<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rows = [
            // Hero
            ['key' => 'about_page_hero_title',       'group' => 'about_page', 'label' => 'About Page — Hero Title',          'value' => 'About KayXchange'],
            ['key' => 'about_page_hero_subtitle',     'group' => 'about_page', 'label' => 'About Page — Hero Subtitle',        'value' => 'Nigeria\'s most trusted platform for buying & selling digital assets.'],

            // Our Story
            ['key' => 'about_page_story_heading',     'group' => 'about_page', 'label' => 'Our Story — Heading',               'value' => 'Our Story'],
            ['key' => 'about_page_story_text',        'group' => 'about_page', 'label' => 'Our Story — Description',           'value' => 'KayXchange was founded with a single goal: to make cryptocurrency accessible and trustworthy for every Nigerian. We noticed that many people wanted to participate in the digital asset economy but were held back by complicated processes, unreliable platforms, and lack of local support. We built KayXchange to solve that — fast transactions, competitive rates, and a human team that genuinely cares about your experience.'],

            // Mission & Vision
            ['key' => 'about_page_mission_heading',   'group' => 'about_page', 'label' => 'Mission — Heading',                 'value' => 'Our Mission'],
            ['key' => 'about_page_mission_text',      'group' => 'about_page', 'label' => 'Mission — Description',             'value' => 'To provide the fastest, most secure, and most accessible cryptocurrency exchange service in Nigeria — empowering individuals and businesses to participate in the global digital economy with confidence.'],
            ['key' => 'about_page_vision_heading',    'group' => 'about_page', 'label' => 'Vision — Heading',                  'value' => 'Our Vision'],
            ['key' => 'about_page_vision_text',       'group' => 'about_page', 'label' => 'Vision — Description',              'value' => 'To become Africa\'s most trusted digital asset exchange platform, connecting millions of users to the borderless financial system of the future.'],

            // Core Values (4 cards)
            ['key' => 'about_page_val1_title',        'group' => 'about_page', 'label' => 'Value 1 — Title',                   'value' => 'Speed'],
            ['key' => 'about_page_val1_desc',         'group' => 'about_page', 'label' => 'Value 1 — Description',             'value' => 'Payments are confirmed and processed within minutes — because your time matters.'],
            ['key' => 'about_page_val2_title',        'group' => 'about_page', 'label' => 'Value 2 — Title',                   'value' => 'Security'],
            ['key' => 'about_page_val2_desc',         'group' => 'about_page', 'label' => 'Value 2 — Description',             'value' => 'Your funds and personal data are protected by industry-leading encryption and verification protocols.'],
            ['key' => 'about_page_val3_title',        'group' => 'about_page', 'label' => 'Value 3 — Title',                   'value' => 'Transparency'],
            ['key' => 'about_page_val3_desc',         'group' => 'about_page', 'label' => 'Value 3 — Description',             'value' => 'No hidden fees, no surprises. Rates are published openly and transactions are fully traceable.'],
            ['key' => 'about_page_val4_title',        'group' => 'about_page', 'label' => 'Value 4 — Title',                   'value' => 'Support'],
            ['key' => 'about_page_val4_desc',         'group' => 'about_page', 'label' => 'Value 4 — Description',             'value' => 'Our support team is available around the clock to resolve any issue within the hour.'],

            // Stats
            ['key' => 'about_page_stat1_num',         'group' => 'about_page', 'label' => 'Stat 1 — Number',                   'value' => '3,000+'],
            ['key' => 'about_page_stat1_label',       'group' => 'about_page', 'label' => 'Stat 1 — Label',                    'value' => 'Happy Clients'],
            ['key' => 'about_page_stat2_num',         'group' => 'about_page', 'label' => 'Stat 2 — Number',                   'value' => '90,000+'],
            ['key' => 'about_page_stat2_label',       'group' => 'about_page', 'label' => 'Stat 2 — Label',                    'value' => 'Trades Completed'],
            ['key' => 'about_page_stat3_num',         'group' => 'about_page', 'label' => 'Stat 3 — Number',                   'value' => '24/7'],
            ['key' => 'about_page_stat3_label',       'group' => 'about_page', 'label' => 'Stat 3 — Label',                    'value' => 'Customer Support'],
            ['key' => 'about_page_stat4_num',         'group' => 'about_page', 'label' => 'Stat 4 — Number',                   'value' => '15+'],
            ['key' => 'about_page_stat4_label',       'group' => 'about_page', 'label' => 'Stat 4 — Label',                    'value' => 'Team Members'],

            // CTA
            ['key' => 'about_page_cta_heading',       'group' => 'about_page', 'label' => 'CTA — Heading',                     'value' => 'Ready to Start Trading?'],
            ['key' => 'about_page_cta_text',          'group' => 'about_page', 'label' => 'CTA — Description',                 'value' => 'Join thousands of Nigerians who trust KayXchange for their crypto needs.'],
        ];

        foreach ($rows as $row) {
            DB::table('site_contents')->insertOrIgnore(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        DB::table('site_contents')->where('group', 'about_page')->delete();
    }
};
