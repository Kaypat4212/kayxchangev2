<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellTrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'coin',
        'amount',
        'proof',
        'status',
        'account_name',
        'account_number',
        'bank_name',
        'payment_method',
    ];
    
}
