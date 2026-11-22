<?php

namespace App\Http\Controllers;

use App\Models\AdminSetting;
use Illuminate\View\View;

class ResourcesController extends Controller
{
    public function index(): View
    {
        $guides = [
            'buy' => ['title' => 'Buy crypto', 'icon' => 'bi-cart-check', 'url' => route('buy'), 'steps' => ['Choose an enabled coin and enter your amount.', 'Review the live rate, exact Naira total, and receiving details.', 'Complete payment and follow the order in transaction history.'], 'video' => $this->embedUrl(AdminSetting::getSetting('resource_video_buy_url'))],
            'sell' => ['title' => 'Sell crypto', 'icon' => 'bi-arrow-up-circle', 'url' => route('sell.form'), 'steps' => ['Choose the crypto and network, then review your quote.', 'Send the exact amount to the address on your order and upload proof.', 'Choose a bank payout or Naira wallet credit, then track review status.'], 'video' => $this->embedUrl(AdminSetting::getSetting('resource_video_sell_url'))],
            'deposit' => ['title' => 'Deposit crypto', 'icon' => 'bi-box-arrow-in-down', 'url' => route('wallet.deposit'), 'steps' => ['Select the exact asset and network.', 'Create an invoice and send only the amount shown to its address.', 'Watch provider confirmations; wallet credit follows the displayed review status.'], 'video' => $this->embedUrl(AdminSetting::getSetting('resource_video_deposit_url'))],
            'withdraw' => ['title' => 'Withdraw funds', 'icon' => 'bi-bank', 'url' => route('wallet.withdraw'), 'steps' => ['Select Naira or an enabled crypto withdrawal option.', 'Check your available balance, minimum, fees, and destination carefully.', 'Confirm with your transaction PIN and follow progress in wallet history.'], 'video' => $this->embedUrl(AdminSetting::getSetting('resource_video_withdraw_url'))],
        ];

        return view('resources.index', compact('guides'));
    }

    private function embedUrl(?string $url): ?string
    {
        $url = trim((string) $url);
        if ($url === '') return null;
        $parts = parse_url($url);
        if (!is_array($parts) || strtolower((string) ($parts['scheme'] ?? '')) !== 'https') return null;
        $host = strtolower((string) ($parts['host'] ?? ''));
        $path = trim((string) ($parts['path'] ?? ''), '/');
        $id = null;

        if (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtube-nocookie.com', 'www.youtube-nocookie.com'], true)) {
            parse_str((string) ($parts['query'] ?? ''), $query);
            if (($path === 'watch') && isset($query['v'])) $id = (string) $query['v'];
            elseif (preg_match('#^(?:embed|shorts|live)/([A-Za-z0-9_-]{11})$#', $path, $match)) $id = $match[1];
        } elseif ($host === 'youtu.be' || $host === 'www.youtu.be') {
            if (preg_match('#^([A-Za-z0-9_-]{11})$#', $path, $match)) $id = $match[1];
        } elseif (in_array($host, ['vimeo.com', 'www.vimeo.com', 'player.vimeo.com'], true)) {
            if (preg_match('#^(?:video/)?([0-9]{6,12})$#', $path, $match)) {
                return 'https://player.vimeo.com/video/' . $match[1];
            }
        }

        return $id && preg_match('/^[A-Za-z0-9_-]{11}$/', $id)
            ? 'https://www.youtube-nocookie.com/embed/' . $id
            : null;
    }
}
