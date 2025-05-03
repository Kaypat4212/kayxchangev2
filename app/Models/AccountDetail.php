<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountDetail extends Model
{
    use HasFactory;

    // Use $guarded instead of $fillable
    protected $guarded = [];  // This allows all attributes to be mass-assigned
}
