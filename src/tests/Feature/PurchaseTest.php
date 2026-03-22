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
        // 購入
        $response = $this->actingAs($user)->get(route('purchase.success', ['item_id' => $item->id]));

        $this->assertDatabaseHas('purchases', [
            'item_id'  => $item->id,
            'buyer_id' => $user->id,
        ]);

        // 商品一覧画面で「SOLD」
        $indexResponse = $this->get('/');
        $indexResponse->assertSee('SOLD');

        // プロフィール画面購入商品一覧への追加
        $profileResponse = $this->actingAs($user)->get('/mypage?tab=buy');
        $profileResponse->assertSee($item->name);
    }

    // 支払い方法選択機能
    public function test_payment_method_selection_is_reflected()
    {
        $user = User::find(1);
        $user->email_verified_at = now();
        $user->postal_code = '123-4567';
        $user->address = '東京都';
        $user->save();

        $item = Item::find(1);
        $paymentMethod = 'コンビニ払い';

        $response = $this->actingAs($user)->post("/purchase/address/{$item->id}", [
            'payment_method' => $paymentMethod,
        ]);

        $response = $this->actingAs($user)->get("/purchase/{$item->id}");
        $response->assertStatus(200);

        $response->assertSee($paymentMethod);
    }

    // 配送先変更機能
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

        $this->actingAs($user)->post("/purchase/address/{$item->id}", $newAddress);

        $response = $this->actingAs($user)->get("/purchase/{$item->id}");
        $response->assertStatus(200);
        $response->assertSee($newAddress['postal_code']);
        $response->assertSee($newAddress['address']);
        $response->assertSee($newAddress['building']);
    }

    public function test_item_is_purchased_with_new_shipping_address()
    {
        $user = User::find(1);
        $user->email_verified_at = now();
        $user->postal_code = '123-4567';
        $user->address = '東京都';
        $user->save();

        $seller = User::create([
            'name' => '出品者',
            'email' => 'seller@example.com',
            'password' => bcrypt('password'),
        ]);

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

        $this->actingAs($user)->post("/purchase/address/{$item->id}", $shippingData);

        $response = $this->actingAs($user)->get(route('purchase.success', ['item_id' => $item->id]));
        $response->assertStatus(302);

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
