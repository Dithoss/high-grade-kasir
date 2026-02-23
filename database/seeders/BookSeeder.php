<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    public function run(): void
        {
            if (Category::count() === 0) {
                Category::factory()->count(5)->create();
            }

            $categories = Category::all();

            Book::factory()
                ->count(10)
                ->create([
                    'category_id' => fn () => $categories->random()->id,
                ]);
        }
}
