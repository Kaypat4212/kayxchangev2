<?php

namespace App\Services;

use App\Models\SellTrade;
use App\Models\User;
use App\Mail\TradeNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * CryptoMonitorService
 *
 * Polls public blockchain APIs to detect incoming crypto payments for pending
 * sell trades. When a matching confirmed transaction is found the trade is
 * auto-approved and (if payout = wallet_balance) the user's NGN balance is credited.
 *
 * Supported:
 *  Coin  | Network  | API
 *  BTC   | Bitcoin  | BlockCypher  (BLOCKCYPHER_TOKEN optional)
 *  ETH   | ERC-20   | Etherscan    (ETHERSCAN_API_KEY)
 *  USDT  | ERC-20   | Etherscan    (same key)
 *  USDT  | TRC-20   | TronGrid     (TRONGRID_API_KEY optional)
 *  USDT  | BEP-20   | BSCScan      (BSCSCAN_API_KEY)
 *  SOL   | Solana   | Solana RPC   (public, no key)
 *
 * .env keys:
 *   ETHERSCAN_API_KEY
 *   BSCSCAN_API_KEY
 *   TRONGRID_API_KEY   (optional, free without key but lower rate limit)
 *   BLOCKCYPHER_TOKEN  (optional)
 */
class CryptoMonitorService
{
    private const TOLERANCE   = 0.02;   // ±2% on expected crypto amount
    private const ETH_DEC     = 1e18;
    private const USDT_DEC    = 1e6;
    private const SOL_DEC     = 1e9;    // lamports → SOL

    // Tether ERC-20 contract (mainnet)
    private const USDT_ERC20_CONTRACT = '0xdac17f958d2ee523a2206206994597c13d831ec7';
    // Tether BEP-20 contract (BSC mainnet)
    private const USDT_BEP20_CONTRACT = '0x55d398326f99059ff775485246999027b3197955';

    // ──────────────────────────────────────────────────────────────
    //  Public entry-point
    // ──────────────────────────────────────────────────────────────

    public function checkTrade(SellTrade $trade): bool
    {
        try {
            $coin    = strtoupper($trade->coin    ?? '');
            $network = strtoupper($trade->network ?? '');
            $wallet  = $trade->wallet_address ?? '';

            if (empty($wallet) || $wallet === 'N/A') return false;

            return match(true) {
                $coin === 'BTC'                           => $this->checkBTC($trade, $wallet),
                $coin === 'ETH'                           => $this->checkETH($trade, $wallet),
                $coin === 'USDT' && $network === 'TRC20'  => $this->checkUSDT_TRC20($trade, $wallet),
                $coin === 'USDT' && $network === 'BEP20'  => $this->checkUSDT_BEP20($trade, $wallet),
                $coin === 'USDT'                          => $this->checkUSDT_ERC20($trade, $wallet),
                $coin === 'SOL'                           => $this->checkSOL($trade, $wallet),
                default                                   => false,
            };
        } catch (\Throwable $e) {
            Log::error("[CryptoMonitor] Exception trade #{$trade->id}: " . $e->getMessage());
            return false;
        }
    }

    // ──────────────────────────────────────────────────────────────
    //  BTC — BlockCypher
    // ──────────────────────────────────────────────────────────────

    private function checkBTC(SellTrade $trade, string $address): bool
    {
        $token = config('services.blockcypher.token', '');
        $url   = "https://api.blockcypher.com/v1/btc/main/addrs/{$address}/full?limit=10"
                . ($token ? "&token={$token}" : '');

        $res = Http::timeout(8)->get($url);
        if (!$res->successful()) return false;

        foreach ($res->json('txs', []) as $tx) {
            if (($tx['confirmations'] ?? 0) < 1) continue;

            $satoshi = 0;
            foreach ($tx['outputs'] ?? [] as $out) {
                if (in_array($address, $out['addresses'] ?? [], true)) {
                    $satoshi += $out['value'] ?? 0;
                }
            }

            if ($this->matchesAmount($trade, $satoshi / 1e8)) {
                return $this->approveTrade($trade, $tx['hash']);
            }
        }
        return false;
    }

    // ──────────────────────────────────────────────────────────────
    //  ETH — Etherscan
    // ──────────────────────────────────────────────────────────────

    private function checkETH(SellTrade $trade, string $address): bool
    {
        $key = config('services.etherscan.key', '');
        $res = Http::timeout(8)->get('https://api.etherscan.io/api', [
            'module'  => 'account', 'action' => 'txlist',
            'address' => $address,  'sort'   => 'desc',
            'page'    => 1,         'offset' => 20, 'apikey' => $key,
        ]);

        if (!$res->successful() || $res->json('status') !== '1') return false;

        foreach ($res->json('result', []) as $tx) {
            if (($tx['confirmations'] ?? 0) < 1) continue;
            if (strtolower($tx['to'] ?? '') !== strtolower($address)) continue;
            if ($this->matchesAmount($trade, (float) $tx['value'] / self::ETH_DEC)) {
                return $this->approveTrade($trade, $tx['hash']);
            }
        }
        return false;
    }

    // ──────────────────────────────────────────────────────────────
    //  USDT ERC-20 — Etherscan
    // ──────────────────────────────────────────────────────────────

    private function checkUSDT_ERC20(SellTrade $trade, string $address): bool
    {
        $key = config('services.etherscan.key', '');
        $res = Http::timeout(8)->get('https://api.etherscan.io/api', [
            'module'          => 'account', 'action'  => 'tokentx',
            'contractaddress' => self::USDT_ERC20_CONTRACT,
            'address'         => $address,  'sort'    => 'desc',
            'page'            => 1,         'offset'  => 20, 'apikey' => $key,
        ]);

        if (!$res->successful() || $res->json('status') !== '1') return false;

        foreach ($res->json('result', []) as $tx) {
            if (($tx['confirmations'] ?? 0) < 1) continue;
            if (strtolower($tx['to'] ?? '') !== strtolower($address)) continue;
            $usdt = (float) $tx['value'] / self::USDT_DEC;
            if ($this->withinTolerance((float) $trade->usd_amount, $usdt)) {
                return $this->approveTrade($trade, $tx['hash']);
            }
        }
        return false;
    }

    // ──────────────────────────────────────────────────────────────
    //  USDT TRC-20 — TronGrid
    // ──────────────────────────────────────────────────────────────

    private function checkUSDT_TRC20(SellTrade $trade, string $address): bool
    {
        $key     = config('services.trongrid.key', '');
        $headers = $key ? ['TRON-PRO-API-KEY' => $key] : [];

        $res = Http::timeout(8)->withHeaders($headers)
            ->get("https://api.trongrid.io/v1/accounts/{$address}/transactions/trc20", [
                'limit'            => 20,
                'contract_address' => 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t', // USDT TRC-20 contract
                'only_to'          => 'true',
            ]);

        if (!$res->successful()) return false;

        foreach ($res->json('data', []) as $tx) {
            // TronGrid confirmed = not unconfirmed
            if (($tx['confirmed'] ?? false) === false) continue;
            $to    = $tx['to'] ?? '';
            $value = (float) ($tx['value'] ?? 0);
            if (strtolower($to) !== strtolower($address)) continue;
            $usdt = $value / self::USDT_DEC;
            if ($this->withinTolerance((float) $trade->usd_amount, $usdt)) {
                return $this->approveTrade($trade, $tx['transaction_id'] ?? $tx['txID'] ?? '');
            }
        }
        return false;
    }

    // ──────────────────────────────────────────────────────────────
    //  USDT BEP-20 — BSCScan
    // ──────────────────────────────────────────────────────────────

    private function checkUSDT_BEP20(SellTrade $trade, string $address): bool
    {
        $key = config('services.bscscan.key', '');
        $res = Http::timeout(8)->get('https://api.bscscan.com/api', [
            'module'          => 'account', 'action'  => 'tokentx',
            'contractaddress' => self::USDT_BEP20_CONTRACT,
            'address'         => $address,  'sort'    => 'desc',
            'page'            => 1,         'offset'  => 20, 'apikey' => $key,
        ]);

        if (!$res->successful() || $res->json('status') !== '1') return false;

        foreach ($res->json('result', []) as $tx) {
            if (($tx['confirmations'] ?? 0) < 1) continue;
            if (strtolower($tx['to'] ?? '') !== strtolower($address)) continue;
            $usdt = (float) $tx['value'] / self::USDT_DEC;
            if ($this->withinTolerance((float) $trade->usd_amount, $usdt)) {
                return $this->approveTrade($trade, $tx['hash']);
            }
        }
        return false;
    }

    // ──────────────────────────────────────────────────────────────
    //  SOL — Solana public RPC
    // ──────────────────────────────────────────────────────────────

    private function checkSOL(SellTrade $trade, string $address): bool
    {
        // Get last 20 confirmed signatures for this address
        $sigRes = Http::timeout(8)->post('https://api.mainnet-beta.solana.com', [
            'jsonrpc' => '2.0', 'id' => 1,
            'method'  => 'getSignaturesForAddress',
            'params'  => [$address, ['limit' => 20]],
        ]);

        if (!$sigRes->successful()) return false;

        $sigs = collect($sigRes->json('result', []))
            ->where('err', null)       // only successful txs
            ->pluck('signature');

        foreach ($sigs as $sig) {
            $txRes = Http::timeout(8)->post('https://api.mainnet-beta.solana.com', [
                'jsonrpc' => '2.0', 'id' => 1,
                'method'  => 'getTransaction',
                'params'  => [$sig, ['encoding' => 'json', 'maxSupportedTransactionVersion' => 0]],
            ]);

            if (!$txRes->successful()) continue;
            $txData = $txRes->json('result');
            if (!$txData) continue;

            // Find how much SOL the target address received
            $accounts    = $txData['transaction']['message']['accountKeys'] ?? [];
            $preBalances  = $txData['meta']['preBalances']  ?? [];
            $postBalances = $txData['meta']['postBalances'] ?? [];

            foreach ($accounts as $idx => $acct) {
                $acctAddr = is_array($acct) ? ($acct['pubkey'] ?? '') : $acct;
                if ($acctAddr !== $address) continue;

                $pre  = $preBalances[$idx]  ?? 0;
                $post = $postBalances[$idx] ?? 0;
                $receivedSOL = max(0, ($post - $pre)) / self::SOL_DEC;

                if ($this->matchesAmount($trade, $receivedSOL)) {
                    return $this->approveTrade($trade, $sig);
                }
            }
        }
        return false;
    }

    // ──────────────────────────────────────────────────────────────
    //  Helpers
    // ──────────────────────────────────────────────────────────────

    private function matchesAmount(SellTrade $trade, float $received): bool
    {
        $expected = (float) ($trade->amount ?? 0);
        if ($expected <= 0) return false;
        return $this->withinTolerance($expected, $received);
    }

    private function withinTolerance(float $expected, float $received): bool
    {
        if ($expected <= 0) return false;
        return abs($expected - $received) / $expected <= self::TOLERANCE;
    }

    private function approveTrade(SellTrade $trade, string $txHash): bool
    {
        if ($trade->status !== 'pending') return false;

        $trade->status          = 'completed';
        $trade->transaction_ref = $txHash;
        $trade->save();

        $user = User::find($trade->user_id);

        if ($trade->payment_method === 'wallet_balance') {
            if ($user && $trade->naira_amount > 0) {
                $user->increment('balance', (float) $trade->naira_amount);
                $user->refresh(); // reload after increment
            }
        }

        Log::info("[CryptoMonitor] Auto-approved SellTrade #{$trade->id} ({$trade->coin}/{$trade->network}) tx: {$txHash}");

        // Notify user via email + Telegram
        if ($user) {
            // Calculate crypto equivalent
            $usdVal = $trade->usd_amount ?? 0;
            $cryptoAmount = $usdVal / ($trade->rate_used ?: 1);
            
            try {
                Mail::to($user->email)->send(new TradeNotification(
                    user: $user,
                    templateKey: 'sell_trade_completed',
                    data: [
                        'usd_amount'     => number_format((float)$usdVal, 2),
                        'crypto_amount'  => number_format($cryptoAmount, 8),
                        'currency'       => $trade->coin,
                        'rate_used'      => number_format((float)$trade->rate_used, 2),
                        'naira_amount'   => number_format((float)$trade->naira_amount, 2),
                        'reference'      => $trade->transaction_ref,
                        'payment_method' => $trade->payment_method ?? 'Bank Transfer',
                        'reason'         => '',
                    ],
                    badge: ['text' => 'Payment Sent', 'color' => '#00cc00'],
                    ctaUrl: url('/dashboard'),
                    ctaText: 'Go to Dashboard',
                ));
            } catch (\Throwable $e) {
                Log::warning("[CryptoMonitor] Email notify failed trade #{$trade->id}: " . $e->getMessage());
            }

            if ($user->telegram_chat_id) {
                try {
                    app(\App\Services\TelegramService::class)->sendMessage(
                        (int)$user->telegram_chat_id,
                        "✅ *Sell Trade Auto-Confirmed!*\n\n" .
                        "🔖 Ref: `{$trade->transaction_ref}`\n" .
                        "🪙 {$trade->coin}: \$" . number_format((float)($trade->usd_amount ?? 0), 2) . "\n" .
                        "💴 Amount: ₦" . number_format((float)$trade->naira_amount, 2) . "\n" .
                        ($trade->payment_method === 'wallet_balance'
                            ? "💰 Credited to your wallet balance."
                            : "💳 Payment is being sent to your bank."),
                        'Markdown'
                    );
                } catch (\Throwable $e) {
                    Log::warning("[CryptoMonitor] Telegram notify failed trade #{$trade->id}: " . $e->getMessage());
                }
            }
        }

        return true;
    }
}
