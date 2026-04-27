<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('kx_tag', 20)->nullable()->unique()->after('email');
        });

        // Back-fill existing users with a unique KX tag
        DB::table('users')->whereNull('kx_tag')->orderBy('id')->each(function ($user) {
            $tag = null;
            do {
                $tag = 'KX' . strtoupper(Str::random(6));
            } while (DB::table('users')->where('kx_tag', $tag)->exists());

            DB::table('users')->where('id', $user->id)->update(['kx_tag' => $tag]);
        });

        // Now make non-nullable
        Schema::table('users', function (Blueprint $table) {
            $table->string('kx_tag', 20)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('kx_tag');
        });
    }
};
