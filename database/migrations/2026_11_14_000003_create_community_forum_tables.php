<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80)->unique();
            $table->string('slug', 100)->unique();
            $table->string('description', 220)->nullable();
            $table->string('icon', 60)->default('bi-chat-square-text');
            $table->string('color', 20)->default('#00cc00');
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('forum_topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('forum_categories')->restrictOnDelete();
            $table->foreignId('blog_post_id')->nullable()->constrained('blog_posts')->nullOnDelete();
            $table->string('title', 180);
            $table->string('slug', 220)->unique();
            $table->text('body');
            $table->string('status', 20)->default('published');
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('replies_count')->default(0);
            $table->timestamp('last_activity_at')->nullable()->index();
            $table->timestamps();
            $table->index(['status', 'is_pinned', 'last_activity_at']);
            $table->index(['category_id', 'status', 'last_activity_at']);
            $table->index(['blog_post_id', 'status']);
        });

        Schema::create('forum_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->constrained('forum_topics')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->string('status', 20)->default('published');
            $table->timestamps();
            $table->index(['topic_id', 'status', 'created_at']);
        });

        Schema::create('forum_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('topic_id')->nullable()->constrained('forum_topics')->cascadeOnDelete();
            $table->foreignId('reply_id')->nullable()->constrained('forum_replies')->cascadeOnDelete();
            $table->string('reaction', 20)->default('helpful');
            $table->timestamps();
            $table->unique(['user_id', 'topic_id']);
            $table->unique(['user_id', 'reply_id']);
        });

        Schema::create('forum_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('topic_id')->nullable()->constrained('forum_topics')->cascadeOnDelete();
            $table->foreignId('reply_id')->nullable()->constrained('forum_replies')->cascadeOnDelete();
            $table->string('reason', 40);
            $table->text('details')->nullable();
            $table->string('status', 20)->default('open');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_note')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });

        DB::table('forum_categories')->insert([
            ['name' => 'Community Lounge', 'slug' => 'community-lounge', 'description' => 'Introductions, community updates and general crypto conversation.', 'icon' => 'bi-people', 'color' => '#38bdf8', 'is_active' => true, 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Bitcoin', 'slug' => 'bitcoin', 'description' => 'Bitcoin news, learning and network discussions.', 'icon' => 'bi-currency-bitcoin', 'color' => '#f7931a', 'is_active' => true, 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Altcoins & Web3', 'slug' => 'altcoins-web3', 'description' => 'Ethereum, altcoins, stablecoins and Web3.', 'icon' => 'bi-boxes', 'color' => '#8b9cff', 'is_active' => true, 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Markets & Trading', 'slug' => 'markets-trading', 'description' => 'Market observations and trading education.', 'icon' => 'bi-graph-up-arrow', 'color' => '#4ade80', 'is_active' => true, 'sort_order' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Wallets & Security', 'slug' => 'wallets-security', 'description' => 'Wallet practices, self-custody and account safety.', 'icon' => 'bi-shield-lock', 'color' => '#c084fc', 'is_active' => true, 'sort_order' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Beginner Questions', 'slug' => 'beginner-questions', 'description' => 'A welcoming place to ask questions and learn the basics.', 'icon' => 'bi-lightbulb', 'color' => '#fbbf24', 'is_active' => true, 'sort_order' => 6, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_reports');
        Schema::dropIfExists('forum_reactions');
        Schema::dropIfExists('forum_replies');
        Schema::dropIfExists('forum_topics');
        Schema::dropIfExists('forum_categories');
    }
};
