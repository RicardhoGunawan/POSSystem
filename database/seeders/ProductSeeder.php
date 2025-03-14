<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil kategori dari database
        $categories = [
            'Makanan' => Category::where('name', 'Makanan')->first(),
            'Minuman' => Category::where('name', 'Minuman')->first(),
            'Snack' => Category::where('name', 'Snack')->first(),
            'Bir' => Category::where('name', 'Bir')->first(),
        ];

        // Daftar produk
        $products = [
            // Produk Makanan
            ['name' => 'Nasi Goreng Spesial', 'description' => 'Nasi goreng dengan ayam, udang, dan telur.', 'price' => 30000, 'stock' => 50, 'image_path' => null, 'is_available' => true, 'category_id' => $categories['Makanan']->id],
            ['name' => 'Ayam Bakar Madu', 'description' => 'Ayam bakar dengan saus madu.', 'price' => 35000, 'stock' => 40, 'image_path' => null, 'is_available' => true, 'category_id' => $categories['Makanan']->id],
            ['name' => 'Sate Ayam', 'description' => 'Sate ayam dengan bumbu kacang.', 'price' => 25000, 'stock' => 60, 'image_path' => null, 'is_available' => true, 'category_id' => $categories['Makanan']->id],
            ['name' => 'Mie Goreng Jawa', 'description' => 'Mie goreng khas Jawa dengan ayam dan sayuran.', 'price' => 22000, 'stock' => 55, 'image_path' => null, 'is_available' => true, 'category_id' => $categories['Makanan']->id],
            ['name' => 'Gado-Gado', 'description' => 'Salad sayur dengan bumbu kacang.', 'price' => 20000, 'stock' => 45, 'image_path' => null, 'is_available' => true, 'category_id' => $categories['Makanan']->id],

            // Produk Minuman
            ['name' => 'Es Kopi Susu', 'description' => 'Kopi dengan campuran susu dan gula aren.', 'price' => 18000, 'stock' => 70, 'image_path' => null, 'is_available' => true, 'category_id' => $categories['Minuman']->id],
            ['name' => 'Teh Tarik', 'description' => 'Teh susu khas dengan rasa creamy.', 'price' => 12000, 'stock' => 80, 'image_path' => null, 'is_available' => true, 'category_id' => $categories['Minuman']->id],
            ['name' => 'Jus Mangga', 'description' => 'Jus mangga segar dengan sedikit gula.', 'price' => 16000, 'stock' => 50, 'image_path' => null, 'is_available' => true, 'category_id' => $categories['Minuman']->id],
            ['name' => 'Soda Gembira', 'description' => 'Susu kental manis dicampur dengan soda.', 'price' => 14000, 'stock' => 40, 'image_path' => null, 'is_available' => true, 'category_id' => $categories['Minuman']->id],
            ['name' => 'Air Mineral', 'description' => 'Air mineral dalam kemasan botol.', 'price' => 5000, 'stock' => 100, 'image_path' => null, 'is_available' => true, 'category_id' => $categories['Minuman']->id],

            // Produk Snack
            ['name' => 'Keripik Singkong', 'description' => 'Keripik singkong renyah.', 'price' => 12000, 'stock' => 60, 'image_path' => null, 'is_available' => true, 'category_id' => $categories['Snack']->id],
            ['name' => 'Kacang Mete', 'description' => 'Kacang mete panggang tanpa garam.', 'price' => 30000, 'stock' => 30, 'image_path' => null, 'is_available' => true, 'category_id' => $categories['Snack']->id],
            ['name' => 'Popcorn Caramel', 'description' => 'Popcorn dengan lapisan caramel.', 'price' => 15000, 'stock' => 40, 'image_path' => null, 'is_available' => true, 'category_id' => $categories['Snack']->id],
            ['name' => 'Pisang Goreng', 'description' => 'Pisang goreng crispy dengan topping coklat.', 'price' => 18000, 'stock' => 50, 'image_path' => null, 'is_available' => true, 'category_id' => $categories['Snack']->id],
            ['name' => 'Martabak Mini', 'description' => 'Martabak dengan berbagai rasa.', 'price' => 25000, 'stock' => 35, 'image_path' => null, 'is_available' => true, 'category_id' => $categories['Snack']->id],

            // Produk Bir
            ['name' => 'Bir Heineken', 'description' => 'Bir dengan rasa smooth dan ringan.', 'price' => 45000, 'stock' => 40, 'image_path' => null, 'is_available' => true, 'category_id' => $categories['Bir']->id],
            ['name' => 'Bir Anker', 'description' => 'Bir dengan rasa lebih kuat.', 'price' => 42000, 'stock' => 35, 'image_path' => null, 'is_available' => true, 'category_id' => $categories['Bir']->id],
            ['name' => 'Bali Hai', 'description' => 'Bir dengan rasa tropis.', 'price' => 43000, 'stock' => 30, 'image_path' => null, 'is_available' => true, 'category_id' => $categories['Bir']->id],
            ['name' => 'Corona Extra', 'description' => 'Bir premium dengan rasa ringan.', 'price' => 55000, 'stock' => 25, 'image_path' => null, 'is_available' => true, 'category_id' => $categories['Bir']->id],
            ['name' => 'Bintang Radler', 'description' => 'Bir dengan rasa lemon segar.', 'price' => 39000, 'stock' => 50, 'image_path' => null, 'is_available' => true, 'category_id' => $categories['Bir']->id],
        ];

        // Masukkan data ke dalam database
        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
