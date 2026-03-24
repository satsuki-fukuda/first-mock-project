<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Item;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    //ユーザ情報取得
    public function test_get_profile()
    {
        $user = User::find(1);
        $user->email_verified_at = now();
        $user->postal_code = '123-4567';
        $user->address = '東京都渋谷区';
        $user->save();

        $response = $this->actingAs($user)->get('/mypage');
        $response->assertStatus(200);

        $response->assertSee($user->profile_image);
        $response->assertSee($user->name);
        foreach ($user->items as $item) {
            $response->assertSee($item->name);
        }

        $response = $this->actingAs($user)->get('/mypage?tab=buy');
        foreach ($user->purchases as $purchase) {
            $response->assertSee($purchase->item->name);
        }
    }

    //ユーザ情報変更
    public function test_change_profile()
    {
        $user = User::find(1);
        $user->postal_code = '123-4567';
        $user->address = '東京都渋谷区';
        $user->building = 'テストビル';
        $user->email_verified_at = now();
        $user->save();

        $response = $this->actingAs($user)->get('/mypage/profile');
        $response->assertStatus(200);

        $response->assertSee('value="' . $user->name . '"', false);
        $response->assertSee('value="' . $user->postal_code . '"', false);
        $response->assertSee('value="' . $user->address . '"', false);
        $response->assertSee('value="' . $user->building . '"', false);

        $response->assertSee('http://localhost/storage');
    }

    //出品情報登録
    public function test_listing_item(){
        $user = User::find(1);
        $user->email_verified_at = now();
        $user->postal_code = '123-4567';
        $user->address = '東京都渋谷区';
        $user->save();

        Storage::fake('public');
        $image = UploadedFile::fake()->create('test_item.png', 100);

        $response = $this->actingAs($user)->post('/sell',[
            'image' => $image,
            'name' => "テストアイテム",
            'price' => 5000,
            'brand' => 'テストブランド',
            'description' => "テストテストテストテスト",
            'categories' => [1,2],
            'condition' => 1,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $this->assertDatabaseHas('items', [
            'name' => "テストアイテム",
            'price' => 5000,
            'user_id'      => $user->id,
            'condition_id' => 1,
        ]);

        $item = Item::where('name', "テストアイテム")->first();
        Storage::disk('public')->assertExists($item->image);
    }
}
