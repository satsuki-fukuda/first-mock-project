<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('categories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $params = [
            ['content' => 'ファッション'],
            ['content' => '家電'],
            ['content' => 'インテリア'],
            ['content' => 'レディース'],
            ['content' => 'メンズ'],
            ['content' => 'コスメ'],
            ['content' => '本'],
            ['content' => 'ゲーム'],
            ['content' => 'スポーツ'],
            ['content' => 'キッチン'],
            ['content' => 'ハンドメイド'],
            ['content' => 'アクセサリー'],
            ['content' => 'おもちゃ'],
            ['content' => 'ベビー・キッズ'],
        ];

        foreach ($params as $param) {
            DB::table('categories')->insert($param);
        }
    }
}
