<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyAccount extends Model
{
    protected $table = 'company_accounts';
    protected $fillable = ['account_name', 'account_number', 'bank_name', 'is_active'];
}