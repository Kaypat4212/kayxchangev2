<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $defaults = [
            ['onboarding_welcome_title', 'Welcome heading', 'Welcome to KayXchange'],
            ['onboarding_welcome_subtitle', 'Welcome introduction', "You're a few steps away from using your account. Let's get it ready."],
            ['onboarding_feature_security', 'Security feature', 'Set up a security PIN to protect sensitive actions'],
            ['onboarding_feature_bank', 'Bank details feature', 'Add bank details for convenient NGN payouts'],
            ['onboarding_feature_trading', 'Trading feature', 'Buy and sell supported assets at current platform rates'],
            ['onboarding_pin_title', 'PIN step heading', 'Set Your Security PIN'],
            ['onboarding_pin_description', 'PIN step description', 'Your 4-digit PIN protects withdrawals and sensitive actions. Keep it private.'],
            ['onboarding_bank_title', 'Bank step heading', 'Add Bank Details'],
            ['onboarding_bank_description', 'Bank step description', 'Add your bank account for NGN withdrawals. You can skip this and add it later in Settings.'],
            ['onboarding_complete_title', 'Completion heading', "You're all set!"],
            ['onboarding_complete_description', 'Completion description', 'Your account is ready. Open your dashboard to get started.'],
        ];

        foreach ($defaults as [$key, $label, $value]) {
            DB::table('site_contents')->updateOrInsert(
                ['key' => $key],
                ['group' => 'onboarding', 'label' => $label, 'value' => $value, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function down(): void
    {
        DB::table('site_contents')->where('group', 'onboarding')->delete();
    }
};
