@php
    $flowGuide = match ($flow ?? '') {
        'buy' => [
            'title' => 'Buying crypto', 'intro' => 'Review your quote before submitting a buy request.',
            'steps' => ['Choose a supported coin and enter the amount.', 'Check the platform rate and the crypto amount shown in your summary.', 'Complete the listed payment step, then follow order updates in transaction history.'],
            'note' => 'Crypto is sent to the wallet address on your order after payment is verified. Confirm the address and network before submitting.',
        ],
        'sell' => [
            'title' => 'Selling crypto', 'intro' => 'Get a Naira payout estimate before you submit your trade.',
            'steps' => ['Choose the coin and enter the amount you want to sell.', 'Review the sell rate, payout estimate, and your bank details.', 'Send crypto only to the address and network shown for your order, then track verification and payout status.'],
            'note' => 'A quote is an estimate until the platform confirms the correct amount on the selected network. Never send funds to an old order address.',
        ],
        'deposit' => [
            'title' => 'Depositing crypto', 'intro' => 'Create a fresh payment request for the coin and network you plan to use.',
            'steps' => ['Choose the asset, network, and invoice value.', 'Copy the address and exact crypto amount from the deposit status page.', 'Wait for provider confirmation; the wallet is updated after confirmation and any required review.'],
            'note' => 'Your saved settlement preference determines whether a confirmed deposit stays in its crypto wallet or converts to Naira. Always match the network exactly.',
        ],
        default => [
            'title' => 'Withdrawing crypto', 'intro' => 'Check the asset, network, amount, and destination carefully before sending a request.',
            'steps' => ['Select a funded crypto wallet and choose the matching network.', 'Enter an amount up to your available balance and paste the destination address.', 'Submit once, then follow the review and transfer status from wallet history.'],
            'note' => 'Crypto transfers cannot be reversed. Pending requests are reserved from your available balance and are reviewed before payout.',
        ],
    };
@endphp
<section class="kx-flow-resources" aria-labelledby="flowResourcesTitle">
    <div class="kx-flow-resources-head"><span class="kx-flow-resources-icon"><i class="bi bi-journal-check"></i></span><div><div class="kx-flow-eyebrow">Resources</div><h2 id="flowResourcesTitle">{{ $flowGuide['title'] }}</h2><p>{{ $flowGuide['intro'] }}</p></div></div>
    <ol>@foreach($flowGuide['steps'] as $step)<li><span>{{ $loop->iteration }}</span><div>{{ $step }}</div></li>@endforeach</ol>
    <div class="kx-flow-note"><i class="bi bi-shield-check"></i><span>{{ $flowGuide['note'] }}</span></div>
</section>
<style>
.kx-flow-resources{margin:20px 0 5px;padding:19px 20px;border:1px solid rgba(69,190,112,.2);border-radius:17px;background:linear-gradient(135deg,rgba(24,45,34,.78),rgba(18,28,37,.96));color:#e9f1ec}.kx-flow-resources-head{display:flex;gap:12px;align-items:flex-start}.kx-flow-resources-icon{display:grid;place-items:center;flex:0 0 39px;width:39px;height:39px;border-radius:12px;background:rgba(69,190,112,.12);color:#75e699;font-size:1.05rem}.kx-flow-eyebrow{color:#77dfa0;font-size:.65rem;font-weight:850;letter-spacing:.14em;text-transform:uppercase}.kx-flow-resources h2{margin:2px 0 4px;color:#f4f8f5;font-size:1rem;font-weight:820}.kx-flow-resources p{margin:0;color:#a9b8af;font-size:.78rem;line-height:1.5}.kx-flow-resources ol{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;margin:17px 0 13px;padding:0;list-style:none}.kx-flow-resources li{display:flex;gap:9px;align-items:flex-start;padding:11px;border:1px solid rgba(255,255,255,.065);border-radius:11px;background:rgba(255,255,255,.025);color:#d2ddd5;font-size:.74rem;line-height:1.5}.kx-flow-resources li>span{display:grid;place-items:center;flex:0 0 21px;width:21px;height:21px;border-radius:50%;background:rgba(69,190,112,.15);color:#8be9a6;font-size:.68rem;font-weight:850}.kx-flow-note{display:flex;gap:8px;padding-top:12px;border-top:1px solid rgba(255,255,255,.07);color:#99ac9e;font-size:.72rem;line-height:1.5}.kx-flow-note i{color:#77dfa0}@media(max-width:700px){.kx-flow-resources{padding:16px}.kx-flow-resources ol{grid-template-columns:1fr;gap:7px}.kx-flow-resources li{padding:9px}}
body.light-mode .kx-flow-resources{background:linear-gradient(135deg,#f1faf3,#fff);border-color:rgba(28,120,60,.15);color:#22392a}body.light-mode .kx-flow-resources h2{color:#1c3524}body.light-mode .kx-flow-resources p,body.light-mode .kx-flow-note{color:#5f7665}body.light-mode .kx-flow-resources li{background:#f7fbf8;border-color:rgba(28,120,60,.09);color:#314b38}body.light-mode .kx-flow-note{border-color:rgba(28,120,60,.1)}
</style>
