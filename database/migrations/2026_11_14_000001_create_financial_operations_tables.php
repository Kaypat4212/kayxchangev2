<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crypto_deposits', function (Blueprint $table) {
            $table->timestamp('last_provider_check_at')->nullable()->index();
        });
        Schema::table('crypto_withdrawals', function (Blueprint $table) {
            $table->decimal('usd_value_at_request', 18, 2)->nullable();
            $table->json('risk_flags')->nullable();
        });
        Schema::create('wallet_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_id')->nullable()->constrained()->nullOnDelete();
            $table->string('currency', 12);
            $table->string('entry_type', 24);
            $table->decimal('amount', 24, 8);
            $table->decimal('balance_after', 24, 8);
            $table->string('reference', 100)->nullable();
            $table->string('source', 64);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'currency', 'created_at']);
            $table->index(['source', 'reference']);
        });

        Schema::create('admin_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 100);
            $table->string('subject_type')->nullable();
            $table->string('subject_id', 100)->nullable();
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->index(['admin_id', 'created_at']);
            $table->index(['subject_type', 'subject_id']);
        });

        Schema::create('support_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('crypto_deposit_id')->nullable()->constrained('crypto_deposits')->nullOnDelete();
            $table->string('category', 40)->default('general');
            $table->string('subject', 160);
            $table->text('message');
            $table->string('status', 24)->default('open');
            $table->text('admin_response')->nullable();
            $table->foreignId('assigned_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });

        $now = now();
        foreach (\Illuminate\Support\Facades\DB::table('users')->where('balance', '>', 0)->get(['id', 'balance']) as $user) {
            \Illuminate\Support\Facades\DB::table('wallet_ledger_entries')->insert([
                'user_id' => $user->id, 'wallet_id' => null, 'currency' => 'NGN',
                'entry_type' => 'credit', 'amount' => $user->balance, 'balance_after' => $user->balance,
                'source' => 'opening_balance', 'metadata' => json_encode(['note' => 'Opening ledger balance at migration time']),
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }
        foreach (\Illuminate\Support\Facades\DB::table('wallets')->where('currency', '<>', 'NGN')->where('balance', '>', 0)->get(['id', 'user_id', 'currency', 'balance']) as $wallet) {
            \Illuminate\Support\Facades\DB::table('wallet_ledger_entries')->insert([
                'user_id' => $wallet->user_id, 'wallet_id' => $wallet->id, 'currency' => strtoupper($wallet->currency),
                'entry_type' => 'credit', 'amount' => $wallet->balance, 'balance_after' => $wallet->balance,
                'source' => 'opening_balance', 'metadata' => json_encode(['note' => 'Opening ledger balance at migration time']),
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('crypto_deposits', function (Blueprint $table) {
            $table->dropIndex(['last_provider_check_at']);
            $table->dropColumn('last_provider_check_at');
        });
        Schema::table('crypto_withdrawals', function (Blueprint $table) {
            $table->dropColumn(['usd_value_at_request', 'risk_flags']);
        });
        Schema::dropIfExists('support_cases');
        Schema::dropIfExists('admin_audit_logs');
        Schema::dropIfExists('wallet_ledger_entries');
    }
};
