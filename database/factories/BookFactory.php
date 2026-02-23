<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BookFactory extends Factory
{
    protected $model = Book::class;

    public function definition(): array
    {
        $books = [
            'Laskar Pelangi', 'Bumi Manusia', 'Tenggelamnya Kapal Van Der Wijck',
            'Dilan 1990', 'Perahu Kertas', 'Ayat-Ayat Cinta', 'Negeri 5 Menara',
            'Sang Pemimpi', 'Edensor', 'Maryamah Karpov', 'Saman', 'Pulang',
            'Laut Bercerita', 'Cantik Itu Luka', 'Ronggeng Dukuh Paruk',
            'Di Bawah Lindungan Kabah', 'Atheis', 'Belenggu', 'Salah Asuhan',
            'Sitti Nurbaya', 'Para Priyayi', 'Bukan Pasar Malam', 'Ziarah',
            'Supernova: Ksatria Putri dan Bintang Jatuh', 'Supernova: Akar',
            'Supernova: Petir', 'Rectoverso', 'Filosofi Kopi', 'Madre',
            'Gadis Kretek', 'Orang-Orang Biasa', 'Hujan', 'Matahari', 'Bintang',
            'Rembulan Tenggelam di Wajahmu', 'Hafalan Shalat Delisa',
            'Ketika Cinta Bertasbih', 'Dalam Mihrab Cinta', 'Ranah 3 Warna',
            'Rantau 1 Muara', 'Chairul Tanjung Si Anak Singkong', 'Sepatu Dahlan',
            'Api Tauhid', 'Bidadari-Bidadari Surga', 'Bulan', 'Midnight Sun',
            'Psikologi Kepribadian', 'Sejarah Indonesia Modern',
            'Filsafat Ilmu Pengetahuan', 'Pengantar Ilmu Hukum',
            'Dasar-Dasar Akuntansi', 'Manajemen Sumber Daya Manusia',
            'Pemrograman Web dengan Laravel', 'Algoritma dan Struktur Data',
            'Kecerdasan Buatan', 'Jaringan Komputer', 'Belajar Cepat Python',
            'Clean Code', 'The Pragmatic Programmer', 'Design Patterns',
        ];

        $writers = [
            'Andrea Hirata', 'Pramoedya Ananta Toer', 'Hamka', 'Pidi Baiq',
            'Dee Lestari', 'Habiburrahman El Shirazy', 'Ahmad Fuadi',
            'Ayu Utami', 'Leila S. Chudori', 'Eka Kurniawan',
            'Abdul Muis', 'Marah Rusli', 'Umar Kayam', 'Iwan Simatupang',
            'NH Dini', 'Chairil Anwar', 'Sapardi Djoko Damono',
            'Tere Liye', 'Raditya Dika', 'Boy Candra',
            'Robert C. Martin', 'Martin Fowler', 'Donald Knuth',
        ];

        $name = $this->faker->unique()->randomElement($books);

        $imagePath = $this->generateBookCover();

        return [
            'id'          => (string) Str::uuid(),
            'name'        => $name,
            'slug'        => Str::slug($name) . '-' . Str::random(6),
            'writer'      => $this->faker->randomElement($writers),
            'stock'       => $this->faker->numberBetween(1, 50),
            'image'       => $imagePath,
            'barcode'     => 'BK-' . str_pad($this->faker->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'category_id' => Category::factory(),
        ];
    }

    /**
     * Generate a book cover image and save it to storage
     */
    private function generateBookCover(): ?string
    {
        try {
            if (!Storage::disk('public')->exists('books')) {
                Storage::disk('public')->makeDirectory('books');
            }

            $filename = 'book_' . Str::random(20) . '.jpg';
            $path = 'books/' . $filename;

            $imageContent = @file_get_contents('https://picsum.photos/300/400');

            if ($imageContent !== false) {
                Storage::disk('public')->put($path, $imageContent);
                return $path;
            }

            return null;
        } catch (\Exception $e) {
            \Log::warning('Failed to generate book cover: ' . $e->getMessage());
            return null;
        }
    }
}