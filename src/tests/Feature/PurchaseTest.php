<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Purchase;
use Database\Seeders\DatabaseSeeder;



class PurchaseTest extends TestCase
{
  use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    //商品購入機能
    public function test_purchase_item(){
        $user = User::find(1);
        $user->email_verified_at = now();
        $user->postal_code = '123-4567';
        $user->address = '東京都';
        $user->save();

        // 修正：他人の商品を確実に作成する
        $otherUser = User::create([
            'name' => '他人',
            'email' => 'other@example.com',
            'password' => bcrypt('password'),
        ]);

        $item = Item::create([
            'user_id' => $otherUser->id,
            'name' => '他人の商品',
            'price' => 1000,
            'description' => 'テスト',
            'image' => 'test.jpg',
            'condition_id' => 1,
        ]);
        // 購入成功処理をシミュレート
        $response = $this->actingAs($user)->get(route('purchase.success', ['item_id' => $item->id]));

        // 3. 検証①: DBに購入情報が保存されているか
        $this->assertDatabaseHas('purchases', [
            'item_id'  => $item->id,
            'buyer_id' => $user->id,
        ]);

        // 4. 検証②: 商品一覧画面で「SOLD」が表示されているか
        $indexResponse = $this->get('/');
        $indexResponse->assertSee('SOLD');

        // 5. 検証③: プロフィール画面の購入した商品一覧に追加されているか
        $profileResponse = $this->actingAs($user)->get('/mypage?tab=buy');
        $profileResponse->assertSee($item->name);
    }

       /**
     * 支払い方法選択が正しく反映されることを確認
     */
    public function test_payment_method_selection_is_reflected()
    {
        // 1. 準備：認証済みユーザーと対象商品
        $user = User::find(1);
        $user->email_verified_at = now();
        $user->postal_code = '123-4567';
        $user->address = '東京都';
        $user->save();

        $item = Item::find(1);
        $paymentMethod = 'コンビニ払い'; // プルダウンで選択する値（value）

        // 2. 実行：支払い方法を選択・保存する（実装のURLに合わせてください）
        // 例として、購入画面内での変更保存を想定
        $response = $this->actingAs($user)->post("/purchase/address/{$item->id}", [
            'payment_method' => $paymentMethod,
        ]);

        // 3. 検証：購入画面を再度開き、選択した支払い方法が反映（表示）されているか
        $response = $this->actingAs($user)->get("/purchase/{$item->id}");
        $response->assertStatus(200);
        
        // 画面内に「コンビニ払い」という文字列が表示されていることを確認
        $response->assertSee($paymentMethod);
        
    }

      /**
     * 1. 送付先住所変更が商品購入画面に正しく反映されることの検証
     */
    public function test_shipping_address_change_is_reflected_on_purchase_page()
    {
        $user = User::find(1);
        $user->email_verified_at = now();
        $user->postal_code = '123-4567';
        $user->address = '東京都';
        $user->save();

        $item = Item::create([
            'user_id' => 2,
            'name' => '住所反映テスト商品',
            'price' => 1000,
            'description' => 'テスト',
            'image' => 'test.jpg',
            'condition_id' => 1,
        ]);

        $newAddress = [
            'postal_code' => '999-0000',
            'address' => '大阪府',
            'building' => 'ビル',
        ];
        // ルート: address.update へPOST
        $this->actingAs($user)->post("/purchase/address/{$item->id}", $newAddress);


        // 購入画面を開き、新しい住所が表示されているか確認
        $response = $this->actingAs($user)->get("/purchase/{$item->id}");
        $response->assertStatus(200);
        $response->assertSee($newAddress['postal_code']);
        $response->assertSee($newAddress['address']);
        $response->assertSee($newAddress['building']);
    }

    /**
     * 2. 登録した住所で購入した際、正しく送付先が紐付いて保存されることの検証
     */
    public function test_item_is_purchased_with_new_shipping_address()
    {
        $user = User::find(1);
        $user->email_verified_at = now();
        $user->postal_code = '123-4567';
        $user->address = '東京都';
        $user->save();

        // 2. 準備：出品者（他人）
        $seller = User::create([
            'name' => '出品者',
            'email' => 'seller@example.com',
            'password' => bcrypt('password'),
        ]);

        // 修正：商品を確実に作成
        $item = Item::create([
            'user_id' => $seller->id,
            'name' => '住所テスト用商品' . uniqid(),
            'price' => 1000,
            'description' => 'テスト',
            'image' => 'test.jpg',
            'condition_id' => 1,
            'is_sold' => false,
        ]);

        $shippingData = [
            'postal_code' => '777-6666',
            'address'     => '北海道',
            'building'    => '雪まつりビル',
        ];
        // 1. 住所変更を実行
        $this->actingAs($user)->post("/purchase/address/{$item->id}", $shippingData);
        // コントローラーの updateOrCreate で user_id が主キー的に扱われているため、
        // 確実にこのユーザーのデータとして POST します
        $response = $this->actingAs($user)->get(route('purchase.success', ['item_id' => $item->id]));
        $response->assertStatus(302);

        // Purchasesテーブル（または関連テーブル）に、送付先住所が保存されているか確認
        $this->assertDatabaseHas('shipping_addresses', [
            'item_id'     => $item->id,
            'user_id'     => $user->id,
            'postal_code' => $shippingData['postal_code'],
            'address'     => $shippingData['address'],
        ]);

        $this->assertDatabaseHas('purchases', [
            'item_id'  => $item->id,
            'buyer_id' => $user->id,
            'seller_id'   => $seller->id,
            'total_price' => $item->price,
        ]);
    }

}
