<?php

namespace Tests\Feature;

use App\Models\Deposit;
use App\Models\Wallet;
use App\Models\WalletLedgerEntry;
use App\Services\BalanceSettlementService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WalletLedgerTest extends TestCase
{
    protected int $userId;
    protected string $originalConnection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalConnection = (string) config('database.default');
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::dropIfExists('wallet_ledger_entries');
        Schema::dropIfExists('deposits');
        Schema::dropIfExists('wallets');
        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table) {
            $table->id(); $table->string('name')->nullable(); $table->string('email')->nullable();
            $table->string('password')->nullable(); $table->decimal('balance', 18, 2)->default(0); $table->timestamps();
        });
        Schema::create('wallets', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('user_id'); $table->decimal('balance', 24, 8)->default(0);
            $table->string('currency')->default('NGN'); $table->string('address')->nullable(); $table->string('network')->nullable();
            $table->boolean('is_active')->default(true); $table->timestamps();
        });
        Schema::create('wallet_ledger_entries', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('user_id'); $table->unsignedBigInteger('wallet_id')->nullable();
            $table->string('currency', 12); $table->string('entry_type', 24); $table->decimal('amount', 24, 8);
            $table->decimal('balance_after', 24, 8); $table->string('reference', 100)->nullable(); $table->string('source', 64);
            $table->json('metadata')->nullable(); $table->timestamps();
        });
        Schema::create('deposits', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('user_id'); $table->decimal('amount', 18, 2); $table->decimal('fee_amount', 18, 2)->default(0);
            $table->string('currency')->nullable(); $table->string('status')->default('pending'); $table->string('transaction_ref')->nullable();
            $table->text('admin_note')->nullable(); $table->text('gateway_response')->nullable(); $table->timestamps();
        });
        $this->userId = DB::table('users')->insertGetId(['name' => 'Ledger Test', 'email' => 'ledger@example.test', 'balance' => 0, 'created_at' => now(), 'updated_at' => now()]);
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');
        config(['database.default' => $this->originalConnection]);
        parent::tearDown();
    }

    public function test_wallet_credit_and_debit_write_balanced_ledger_rows(): void
    {
        $wallet = Wallet::create(['user_id' => $this->userId, 'currency' => 'BTC', 'balance' => 0]);
        $this->assertTrue($wallet->credit(0.25, 'test.credit', 'unit-1'));
        $this->assertTrue($wallet->debit(0.1, 'test.debit', 'unit-2'));
        $this->assertFalse($wallet->debit(1.0, 'test.debit', 'unit-3'));

        $this->assertEquals('0.15000000', $wallet->fresh()->balance);
        $this->assertSame(2, WalletLedgerEntry::count());
        $this->assertSame('credit', WalletLedgerEntry::oldest()->first()->entry_type);
        $this->assertEquals('0.15000000', WalletLedgerEntry::orderByDesc('id')->first()->balance_after);
    }

    public function test_replayed_deposit_approval_does_not_credit_twice(): void
    {
        $deposit = Deposit::create(['user_id' => $this->userId, 'amount' => 1250, 'fee_amount' => 0, 'currency' => 'NGN', 'status' => 'pending', 'transaction_ref' => 'DEP-LEDGER-1']);
        $settlement = app(BalanceSettlementService::class);

        $this->assertTrue($settlement->approveDeposit($deposit->id));
        $this->assertFalse($settlement->approveDeposit($deposit->id));
        $this->assertEquals('1250.00', DB::table('users')->where('id', $this->userId)->value('balance'));
        $this->assertEquals(1, WalletLedgerEntry::where('reference', 'DEP-LEDGER-1')->count());
    }
}
