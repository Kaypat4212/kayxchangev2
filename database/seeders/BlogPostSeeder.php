<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use Illuminate\Database\Seeder;

class BlogPostSeeder extends Seeder
{
    public function run(): void
    {
        $posts = [
            [
                'title' => 'Introduction to Blockchain Technology',
                'category' => 'Learn',
                'excerpt' => 'Well-curated guides to get you started with cryptocurrency.',
                'content' => 'Introduction to Blockchain Technology content here...',
                'is_published' => true,
                'published_at' => '2023-09-15 00:00:00',
            ],
            [
                'title' => 'Understanding Cryptocurrency Wallets',
                'category' => 'Learn',
                'excerpt' => 'Well-curated guides to get you started with cryptocurrency.',
                'content' => 'Understanding Cryptocurrency Wallets content here...',
                'is_published' => true,
                'published_at' => '2023-08-28 00:00:00',
            ],
            [
                'title' => 'The Basics of Cryptocurrency',
                'category' => 'Learn',
                'excerpt' => 'Well-curated guides to get you started with cryptocurrency.',
                'content' => 'The Basics of Cryptocurrency content here...',
                'is_published' => true,
                'published_at' => '2023-07-11 00:00:00',
            ],
            [
                'title' => 'Types of Cryptocurrencies',
                'category' => 'Learn',
                'excerpt' => 'Well-curated guides to get you started with cryptocurrency.',
                'content' => 'Types of Cryptocurrencies content here...',
                'is_published' => true,
                'published_at' => '2023-07-11 00:00:00',
            ],
        ];

        foreach ($posts as $post) {
            BlogPost::create($post);
        }
    }
}