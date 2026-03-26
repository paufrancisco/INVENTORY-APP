<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Product::create(['name' => 'Laptop',        'category' => 'Electronics', 'price' => 999.99,  'quantity' => 10]);
        Product::create(['name' => 'Office Chair',  'category' => 'Furniture',   'price' => 249.99,  'quantity' => 25]);
        Product::create(['name' => 'Notebook',      'category' => 'Stationery',  'price' => 3.50,    'quantity' => 200]);
        Product::create(['name' => 'Desk Lamp',     'category' => 'Electronics', 'price' => 39.99,   'quantity' => 50]);
        Product::create(['name' => 'Whiteboard',    'category' => 'Furniture',   'price' => 89.99,   'quantity' => 8]);
    }
}