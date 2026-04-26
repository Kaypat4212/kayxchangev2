<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $id_document_path
 * @property string|null $document_type
 * @property \Carbon\Carbon|null $expiry_date
 * @property string|null $selfie_path
 * @property string $status
 * @property string|null $rejection_reason
 * @property \Carbon\Carbon|null $reviewed_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class Kyc extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'id_document_path',
        'document_type',
        'expiry_date',
        'selfie_path',
        'status',
        'rejection_reason',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'expiry_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}