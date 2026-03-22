<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\Like;
use Database\Seeders\DatabaseSeeder;

class ItemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    //商品一覧取得
    //商品一覧取得--全商品
    public function test_get_items_all()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewHas('items', function ($items) {
            return $items->count() === Item::count();
        });
    }

    //商品一覧取得--Sold表示
    public function test_sold_label_shown_on_purchased_item()
    {
        $seller = User::find(2);
        $buyer = User::find(1);
        $item = Item::find(1);

        \App\Models\Purchase::create([
            'item_id'      => $item->id,
            'buyer_id'     => $buyer->id,
            'seller_id'    => $item->user_id,
            'total_price'  => $item->price,
        ]);
        $item->update(['is_sold' => true]);
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('SOLD');
    }

    //商品一覧取得--自分の商品が未表示
    public function test_my_items_are_not_shown_on_index()
    {
        $me = User::find(1);
        $me->email_verified_at = now();
        $me->postal_code = '123-4567';
        $me->address = '東京都';
        $me->save();

        $otherUser = User::find(2) ?: User::create([
            'name' => '他人',
            'email' => 'other@example.com',
            'password' => bcrypt('password'),
        ]);

        $myItem = Item::create([
            'user_id' => $me->id,
            'name' => '私が出品した商品A',
            'price'        => 1000,
            'description'  => 'テスト',
            'image'        => 'test.jpg',
            'condition_id' => 1,
        ]);

        $otherItem = Item::create([
            'user_id' => $otherUser->id,
            'name' => '他人が出品した商品B',
            'price'        => 2000,
            'description'  => 'テスト',
            'image'        => 'test2.jpg',
            'condition_id' => 1,
        ]);


        $response = $this->actingAs($me)->get('/');
        $response->assertStatus(200);
        $response->assertDontSee($myItem->name);
        $response->assertSee($otherItem->name);
    }

    //マイリスト一覧取得
    //マイリスト一覧取得--いいねした商品のみ表示
    public function test_get_mylist_contains_only_liked_items()
    {
        $me = User::find(1);
        $me->email_verified_at = now();
        $me->postal_code = '123-4567';
        $me->address = '東京都';
        $me->save();

        $likedItem = Item::find(1);
        $unlikedItem = Item::find(2);

        $me->likedItems()->attach($likedItem->id);

        $response = $this->actingAs($me)->get('/?tab=mylist');

        $response->assertStatus(200);
        $response->assertSee($likedItem->name);

        $response->assertViewHas('items', function ($items) use ($likedItem) {
            return $items->contains($likedItem);
        });
    }

    //マイリスト一覧取得--SOLD表示
    public function test_sold_label_shown_on_mylist()
    {
        $me = User::find(1);
        $me->email_verified_at = now();
        $me->postal_code = '123-4567';
        $me->address = '東京都';
        $me->save();

        $item = Item::find(4);
        $me->likedItems()->attach($item->id);

        $item->update(['is_sold' => true]);

        $response = $this->actingAs($me)->get('/?tab=mylist');

        $response->assertStatus(200);
        $response->assertSee('SOLD');
    }

    //マイリスト一覧取得--未承認で非表示
    public function test_unauthenticated_user_sees_nothing_on_mylist()
    {
        $response = $this->get('/?tab=mylist');

        $response->assertStatus(200);
        $response->assertViewHas('items', function ($items) {
            return $items->isEmpty();
        });
    }

    //商品検索機能
    //商品検索機能--部分一致検索
    public function test_search_item_partial_match()
    {
        $response = $this->get('/?keyword=ノート');

        $response->assertStatus(200);
        $response->assertViewHas('items', function ($items) {
            return str_contains($items->first()->name, 'ノート');
        });
    }

    // 商品検索機能--検索キーワードの保持
    public function test_search_keyword_is_kept_in_mylist()
    {
        $user = User::find(1);
        $user->email_verified_at = now();
        $user->postal_code = '123-4567';
        $user->address = '東京都';
        $user->save();

        $searchKeyword = 'ノート';

        $response = $this->actingAs($user)->get("/?tab=mylist&keyword={$searchKeyword}");

        $response->assertStatus(200);

        $response->assertSee('value="' . $searchKeyword . '"', false);
    }

    //商品詳細情報取得--必要な情報の表示
    public function test_item_detail()
    {
        $item = Item::with(['categories', 'condition', 'comments.user', 'likes'])->find(1);

        $response = $this->get('/item/' . $item->id);
        $response->assertStatus(200);

        $response->assertSee($item->image);
        $response->assertSee($item->name);
        $response->assertSee($item->brand);
        $response->assertSee(number_format($item->price));
        $response->assertSee($item->description);
        $response->assertSee($item->condition->content);

        $response->assertSee($item->likes->count());
        $response->assertSee($item->comments->count());

        //  商品詳細情報取得--複数選択されたカテゴリの表示確認
        foreach ($item->categories as $category) {
            $response->assertSee($category->category);
        }

        foreach ($item->comments as $comment) {
            $response->assertSee($comment->user->name);
            $response->assertSee($comment->comment);
        }
    }


    // いいね機能
    // いいね機能--いいね登録
    public function test_like_item_and_ui_changes()
    {
        $user = User::find(1);
        $user->email_verified_at = now();
        $user->postal_code = '123-4567';
        $user->address = '東京都';
        $user->save();

        $itemId = 4;
        $item = Item::find($itemId);
        $initialLikeCount = $item->likes()->count();

        $response = $this->actingAs($user)->post("/item/{$itemId}/like");

        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'item_id' => $itemId
        ]);

        $response = $this->get("/item/{$itemId}");
        $response->assertStatus(200);

        $response->assertSee($initialLikeCount + 1);

        // いいね機能--アイコン色の変化
        $response->assertSee('ハートロゴ_ピンク.png');
    }

    // いいね機能--いいね解除
    public function test_unlike_item()
    {
        $user = User::find(1);
        $user->email_verified_at = now();
        $user->postal_code = '123-4567';
        $user->address = '東京都';
        $user->save();

        $itemId = 4;

        $user->likes()->create(['item_id' => $itemId]);
        $initialCount = Item::find($itemId)->likes()->count();

        $response = $this->actingAs($user)->post("/item/{$itemId}/like");

        $this->assertDatabaseMissing('likes', [
            'user_id' => $user->id,
            'item_id' => $itemId
        ]);


        $response = $this->get("/item/{$itemId}");
        $response->assertSee($initialCount - 1);

        $response->assertDontSee('is-active');
    }

    //コメント送信機能
    // コメント送信機能--ログインユーザーのコメント送信
    public function test_add_comment_success()
    {
        $user = User::find(1);
        $user->email_verified_at = now();
        $user->postal_code = '123-4567';
        $user->address = '東京都';
        $user->save();

        $itemId = 1;
        $initialCount = Item::find($itemId)->comments()->count();

        $response = $this->actingAs($user)->post("/item/comment/{$itemId}", [
            'comment' => 'テストコメント'
        ]);
        $response->assertStatus(302);

        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'item_id' => $itemId,
            'content' => 'テストコメント'
        ]);

        $response = $this->get("/item/{$itemId}");
        $response->assertSee($initialCount + 1);
    }

    // コメント送信機能--未ログインユーザーは送信不可
    public function test_unauthenticated_user_cannot_comment()
    {
        $itemId = 1;

        $response = $this->post("/item/comment/{$itemId}", [
            'comment' => '未ログインのコメント'
        ]);

        $response->assertRedirect('/login');

        $this->assertDatabaseMissing('comments', [
            'comment' => '未ログインのコメント'
        ]);
    }

    // コメント送信機能--未入力バリデーション
    public function test_comment_validation_required()
    {
        $user = User::find(1);
        $user->email_verified_at = now();
        $user->postal_code = '123-4567';
        $user->address = '東京都';
        $user->save();

        $response = $this->actingAs($user)->post('/item/comment/1', [
            'comment' => ''
        ]);

        $response->assertSessionHasErrors(['comment']);
    }

    // コメント送信機能--255字超えバリデーション
    public function test_comment_validation_max_length()
    {
        $user = User::find(1);
        $user->email_verified_at = now();
        $user->postal_code = '123-4567';
        $user->address = '東京都';
        $user->save();

        $response = $this->actingAs($user)->post('/item/comment/1', [
            'comment' => str_repeat('あ', 256)
        ]);

        $response->assertSessionHasErrors(['comment']);
    }
}
