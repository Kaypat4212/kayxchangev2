<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

// use Illuminate\Foundation\Auth\User as Authenticatable;
// use Illuminate\Notifications\Notifiable;
// use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $telegram_chat_id
 * @property string|null $telegram_username
 * @property bool $telegram_notifications
 * @property bool $telegram_verified
 * @property float $balance
 * @property bool $kyc_verified
 * @property bool $is_admin
 * @property string|null $bank_name
 * @property string|null $bank_code
 * @property string|null $account_number
 * @property string|null $account_name
 * @property string|null $transaction_pin
 * @property int $pin_attempts
 * @property \Carbon\Carbon|null $pin_locked_until
 * @property string|null $referral_code
 * @property int|null $referred_by
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'is_admin',
        'balance',
        'bank_name',
        'bank_code',
        'account_number',
        'account_name',
        'kyc_verified',
        'role',
        'telegram_username',
        'telegram_notifications',
        'telegram_ai_enabled',
        'telegram_chat_id',
        'telegram_verified',
        'onboarding_completed',
        'transaction_pin',
        'pin_attempts',
        'pin_locked_until',
        'referral_code',
        'referred_by',
        'paystack_auth_code',
        'paystack_auth_email',
        'paystack_auth_card_last4',
        'paystack_auth_card_type',
        'whatsapp_phone',
        'whatsapp_verified',
        'whatsapp_notifications',
        'registration_ip',
        'two_factor_secret',
        'two_factor_enabled',
        'two_factor_confirmed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'transaction_pin',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'kyc_verified' => 'boolean',
        'onboarding_completed' => 'boolean',
        'pin_locked_until' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
        'telegram_notifications' => 'boolean',
        'telegram_verified' => 'boolean',
        'two_factor_enabled' => 'boolean',
        'two_factor_confirmed_at' => 'datetime',
    ];


    public function referralsMade()
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    public function referralsReceived()
    {
        return $this->hasMany(Referral::class, 'referred_id');
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referred_by', 'referral_code');
    }

    public function sentMessages()
    {
        return $this->hasMany(\App\Models\ChatMessage::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(\App\Models\ChatMessage::class, 'receiver_id');
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function specialReferralCodes()
    {
        return $this->hasMany(SpecialReferralCode::class, 'owner_user_id');
    }

    public static function generateReferralCode()
    {
        do {
            $code = Str::upper(Str::random(8));
        } while (self::where('referral_code', $code)->exists());

        return $code;
    }
}
