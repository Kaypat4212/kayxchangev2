<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $sender_id
 * @property int $recipient_id
 * @property float $amount
 * @property float $fee
 * @property float $recipient_amount
 * @property string $reference
 * @property string|null $note
 * @property string $status
 * @property string|null $reversed_reason
 * @property \Carbon\Carbon|null $reversed_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read User $sender
 * @property-read User $recipient
 */
class P2pTransfer extends Model
{
    protected $table = 'p2p_transfers';

    protected $fillable = [
        'sender_id',
        'recipient_id',
        'amount',
        'fee',
        'recipient_amount',
        'reference',
        'note',
        'status',
        'reversed_reason',
        'reversed_at',
    ];

    protected $casts = [
        'amount'           => 'decimal:2',
        'fee'              => 'decimal:2',
        'recipient_amount' => 'decimal:2',
        'reversed_at'      => 'datetime',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }
}
