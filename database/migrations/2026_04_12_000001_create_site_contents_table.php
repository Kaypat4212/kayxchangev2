<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_contents', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('group')->default('general');
            $table->string('label');
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Seed defaults
        $defaults = [
            // About
            ['key' => 'about_heading',       'group' => 'about',      'label' => 'About Heading',          'value' => 'About KayXchange'],
            ['key' => 'about_subheading',     'group' => 'about',      'label' => 'About Sub-Heading',      'value' => 'Nigeria\'s most trusted platform for buying & selling digital assets.'],
            ['key' => 'about_description',    'group' => 'about',      'label' => 'About Description',      'value' => 'Our platform provides a seamless, secure experience for clients exchanging digital assets. We make the process simple — our experts are always on hand to guide you.'],
            // Why / Values
            ['key' => 'why_heading',          'group' => 'why',        'label' => 'Why Section Heading',    'value' => 'Why People Choose Crypto'],
            ['key' => 'why_card1_title',      'group' => 'why',        'label' => 'Why Card 1 Title',       'value' => 'Easy Mode of Payment'],
            ['key' => 'why_card1_desc',       'group' => 'why',        'label' => 'Why Card 1 Description', 'value' => 'Send and receive money globally to purchase goods and pay for services with ease.'],
            ['key' => 'why_card2_title',      'group' => 'why',        'label' => 'Why Card 2 Title',       'value' => 'Financial Freedom'],
            ['key' => 'why_card2_desc',       'group' => 'why',        'label' => 'Why Card 2 Description', 'value' => 'No single entity controls the crypto network — full transparency, privacy, and control over your money.'],
            ['key' => 'why_card3_title',      'group' => 'why',        'label' => 'Why Card 3 Title',       'value' => 'Investment'],
            ['key' => 'why_card3_desc',       'group' => 'why',        'label' => 'Why Card 3 Description', 'value' => 'Cryptocurrencies act as Digital Gold — a popular alternative store of wealth for long-term investors.'],
            // Stats
            ['key' => 'stat_clients',         'group' => 'stats',      'label' => 'Happy Clients Count',    'value' => '3000'],
            ['key' => 'stat_trades',          'group' => 'stats',      'label' => 'Total Trades Count',     'value' => '90000'],
            ['key' => 'stat_support',         'group' => 'stats',      'label' => 'Support Hours',          'value' => '24'],
            ['key' => 'stat_workers',         'group' => 'stats',      'label' => 'Team Members',           'value' => '15'],
            // Newsletter
            ['key' => 'newsletter_title',     'group' => 'newsletter', 'label' => 'Newsletter Title',       'value' => 'Stay Ahead of the Market'],
            ['key' => 'newsletter_subtitle',  'group' => 'newsletter', 'label' => 'Newsletter Subtitle',    'value' => 'Get weekly market insights, trading tips, and exclusive rate notifications delivered to your inbox.'],
            // Footer
            ['key' => 'footer_tagline',       'group' => 'footer',     'label' => 'Footer Tagline',         'value' => 'Your trusted platform for seamless cryptocurrency trading. Fast, secure, and competitive NGN rates.'],
            ['key' => 'contact_email',        'group' => 'footer',     'label' => 'Contact Email',          'value' => 'support@kayxchange.net'],
            ['key' => 'contact_phone',        'group' => 'footer',     'label' => 'Contact Phone',          'value' => '+234 901 674 0523'],
            ['key' => 'contact_location',     'group' => 'footer',     'label' => 'Contact Location',       'value' => 'Nigeria'],
        ];

        foreach ($defaults as $row) {
            DB::table('site_contents')->insert(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('site_contents');
    }
};
