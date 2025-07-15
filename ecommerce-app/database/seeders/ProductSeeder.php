<?php
// File: /Users/ivanpotvorov/Desktop/OnlineStore_Laravel/ecommerce-app/database/seeders/ProductSeeder.php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();

        $products = [
            // Electronics
            [
                'name' => 'iPhone 15 Pro',
                'slug' => 'iphone-15-pro',
                'sku' => 'IPH15PRO001',
                'description' => 'Latest iPhone with advanced features',
                'price' => 999.99,
                'sale_price' => 899.99,
                'stock_quantity' => 50,
                'is_featured' => true,
                'is_active' => true,
                'category_id' => $categories->where('slug', 'electronics')->first()->id,
            ],
            [
                'name' => 'Samsung Galaxy S24',
                'slug' => 'samsung-galaxy-s24',
                'sku' => 'SAM24GAL001',
                'description' => 'Premium Android smartphone',
                'price' => 849.99,
                'stock_quantity' => 30,
                'is_featured' => true,
                'is_active' => true,
                'category_id' => $categories->where('slug', 'electronics')->first()->id,
            ],
            [
                'name' => 'MacBook Pro 16"',
                'slug' => 'macbook-pro-16',
                'sku' => 'MBP16PRO001',
                'description' => 'Professional laptop for developers',
                'price' => 2499.99,
                'stock_quantity' => 15,
                'is_featured' => true,
                'is_active' => true,
                'category_id' => $categories->where('slug', 'electronics')->first()->id,
            ],

            // Clothing
            [
                'name' => 'Nike Air Max 270',
                'slug' => 'nike-air-max-270',
                'sku' => 'NIK270AIR001',
                'description' => 'Comfortable running shoes',
                'price' => 150.00,
                'sale_price' => 120.00,
                'stock_quantity' => 100,
                'is_featured' => false,
                'is_active' => true,
                'category_id' => $categories->where('slug', 'clothing')->first()->id,
            ],
            [
                'name' => 'Levi\'s 501 Jeans',
                'slug' => 'levis-501-jeans',
                'sku' => 'LEV501JEA001',
                'description' => 'Classic straight-fit jeans',
                'price' => 89.99,
                'stock_quantity' => 75,
                'is_featured' => false,
                'is_active' => true,
                'category_id' => $categories->where('slug', 'clothing')->first()->id,
            ],

            // Books
            [
                'name' => 'Clean Code',
                'slug' => 'clean-code',
                'sku' => 'BOO001CLE001',
                'description' => 'A Handbook of Agile Software Craftsmanship',
                'price' => 42.99,
                'stock_quantity' => 200,
                'is_featured' => true,
                'is_active' => true,
                'category_id' => $categories->where('slug', 'books')->first()->id,
            ],
            [
                'name' => 'Laravel: Up & Running',
                'slug' => 'laravel-up-running',
                'sku' => 'BOO002LAR001',
                'description' => 'A Framework for Building Modern PHP Apps',
                'price' => 39.99,
                'stock_quantity' => 150,
                'is_featured' => false,
                'is_active' => true,
                'category_id' => $categories->where('slug', 'books')->first()->id,
            ],

            // Home & Garden
            [
                'name' => 'Dyson V15 Detect',
                'slug' => 'dyson-v15-detect',
                'sku' => 'DYS15DET001',
                'description' => 'Powerful cordless vacuum cleaner',
                'price' => 749.99,
                'stock_quantity' => 25,
                'is_featured' => true,
                'is_active' => true,
                'category_id' => $categories->where('slug', 'home-garden')->first()->id,
            ],

            // Sports
            [
                'name' => 'Wilson Tennis Racket',
                'slug' => 'wilson-tennis-racket',
                'sku' => 'WIL001TEN001',
                'description' => 'Professional tennis racket',
                'price' => 199.99,
                'stock_quantity' => 40,
                'is_featured' => false,
                'is_active' => true,
                'category_id' => $categories->where('slug', 'sports')->first()->id,
            ],
            [
                'name' => 'Yoga Mat Premium',
                'slug' => 'yoga-mat-premium',
                'sku' => 'YOG001MAT001',
                'description' => 'Non-slip yoga mat for all levels',
                'price' => 49.99,
                'sale_price' => 39.99,
                'stock_quantity' => 80,
                'is_featured' => false,
                'is_active' => true,
                'category_id' => $categories->where('slug', 'sports')->first()->id,
            ],
        ];

        foreach ($products as $productData) {
            Product::create($productData);
        }
    }
}