<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemCategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $mappings = [
            1  => [1, 5, 12],
            2  => [2],
            3  => [10],
            4  => [1, 5],
            5  => [2],
            6  => [2],
            7  => [1, 4, 12],
            8  => [3, 10],
            9  => [3, 10],
            10 => [4, 6],
        ];

        $data = [];
        foreach ($mappings as $itemId => $categoryIds) {
            foreach ($categoryIds as $categoryId) {
                $data[] = [
                    'item_id'     => $itemId,
                    'category_id' => $categoryId,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }
        }

        DB::table('item_category')->insert($data);
    }
}
