<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConditionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 使用しているDBドライバー名を取得
        $driver = DB::getDriverName();

        // 1. 外部キー制約の無効化（DBの種類で分岐）
        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
        }

        // 2. テーブルのクリア
        DB::table('conditions')->truncate();

        // 3. 外部キー制約の有効化
        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        }


        $params = [
            ['content' => '良好'],
            ['content' => '目立った傷や汚れなし'],
            ['content' => 'やや傷や汚れあり'],
            ['content' => '状態が悪い'],
        ];

        foreach ($params as $param) {
            DB::table('conditions')->insert($param);
        }
    }
}
