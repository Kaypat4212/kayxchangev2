<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

class AssignReferralCodesToExistingUsers extends Migration
{
    public function up()
    {
        User::whereNull('referral_code')->each(function ($user) {
            $code = Str::random(8);
            while (User::where('referral_code', $code)->exists()) {
                $code = Str::random(8);
            }
            $user->update(['referral_code' => $code]);
        });
    }

    public function down()
    {
        User::whereNotNull('referral_code')->update(['referral_code' => null]);
    }
}