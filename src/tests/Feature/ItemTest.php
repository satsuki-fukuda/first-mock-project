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

    //商品一覧取得--全表示
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
         // 1. テストに必要なデータの準備
        $seller = User::find(2); // 出品者
        $buyer = User::find(1);  // 購入者
        $item = Item::find(1);   // 対象の商品

        // 2. Purchaseテーブルにレコードを作成（マイグレーションの定義に合わせる）
        \App\Models\Purchase::create([
            'item_id'      => $item->id,
            'buyer_id'     => $buyer->id,
            'seller_id'    => $item->user_id,
            'total_price'  => $item->price, // 商品価格を合計金額とする
        ]);
        $item->update(['is_sold' => true]);
        $response = $this->get('/');

        $response->assertStatus(200);
        // 画面上に「sold」という文字が含まれているか確認
        $response->assertSee('SOLD');
    }

    //商品一覧取得--自分の商品が未表示
    public function test_my_items_are_not_shown_on_index()
    {
        // 1. 自分（ログインユーザー）を準備
        $me = User::find(1);
        $me->email_verified_at = now();
        $me->postal_code = '123-4567';
        $me->address = '東京都';
        $me->save();
        // 2. 他人（別のユーザー）を準備
        $otherUser = User::find(2) ?: User::create([
            'name' => '他人',
            'email' => 'other@example.com',
            'password' => bcrypt('password'),
        ]);
        // 3. 自分が出品した商品を作成（seller_idが自分）
        $myItem = Item::create([
            'user_id' => $me->id,
            'name' => '私が出品した商品A',
            'price'        => 1000,
            'description'  => 'テスト',
            'image'        => 'test.jpg',
            'condition_id' => 1,
        ]);
        // 4. 他人が出品した商品を作成（seller_idが他人）
        $otherItem = Item::create([
            'user_id' => $otherUser->id,
            'name' => '他人が出品した商品B',
            'price'        => 2000,
            'description'  => 'テスト',
            'image'        => 'test2.jpg',
            'condition_id' => 1,
        ]);

        // 5. ログインして一覧ページへアクセス
        $response = $this->actingAs($me)->get('/');

        $response->assertStatus(200);

        // 6. 検証：自分の商品名が「表示されていない」こと
        $response->assertDontSee($myItem->name);

        // 7. 検証：他人の商品名は「表示されている」こと
        $response->assertSee($otherItem->name);
    }

    //マイリスト一覧取得--いいねのみ
     public function test_get_mylist_contains_only_liked_items()
    {
        $me = User::find(1);
        $me->email_verified_at = now();
        $me->postal_code = '123-4567';
        $me->address = '東京都';
        $me->save();

       // Factoryがない場合は既存の商品(Seeder)を利用するか、Item::createで作成
        $likedItem = Item::find(1); 
        $unlikedItem = Item::find(2);

        // 中間テーブルへの登録（Userモデルに likedItems() リレーションがある前提）
        $me->likedItems()->attach($likedItem->id);

        // 修正：URLパラメータを page ではなく tab=mylist に変更
        $response = $this->actingAs($me)->get('/?tab=mylist');

        $response->assertStatus(200);
        
        // 検証
        $response->assertSee($likedItem->name);
        // コントローラーが Auth::user()->likedItems() を返しているか確認
        $response->assertViewHas('items', function ($items) use ($likedItem) {
            return $items->contains($likedItem);
        });
    }

    /**
     * 2. ユーザーにログインしマイリストを開き購入済み商品を確認するとSoldラベルが表示される
     */
    public function test_sold_label_shown_on_mylist()
    {
        $me = User::find(1);
        $me->email_verified_at = now();
        $me->postal_code = '123-4567';
        $me->address = '東京都';
        $me->save();

        $item = Item::find(4);
        $me->likedItems()->attach($item->id);

        // 商品を売却済みに更新
        $item->update(['is_sold' => true]);

        $response = $this->actingAs($me)->get('/?tab=mylist');

        $response->assertStatus(200);
        // マイリスト画面でも「SOLD」が表示されていること
        $response->assertSee('SOLD');
    }

    /**
     * 3. 未認証（未ログイン）の場合はマイリストページを開いても何も表示されない
     */
    public function test_unauthenticated_user_sees_nothing_on_mylist()
    {
        // ログインせずにマイリストページにアクセス
        $response = $this->get('/?tab=mylist');

        $response->assertStatus(200);

        // 検証：itemsが空であること（またはページによってリダイレクトならassertRedirect）
        $response->assertViewHas('items', function ($items) {
            return $items->isEmpty();
        });
    }

    //商品検索
    // 1. 検索機能の修正（部分一致の検証を追加）
    public function test_search_item_partial_match()
    {
        // 「ノート」で検索して「ノートPC」がヒットすることを検証
        $response = $this->get('/?keyword=ノート');

        $response->assertStatus(200);
        $response->assertViewHas('items', function ($items) {
            // 取得した最初の商品の名前に「ノート」が含まれているか
            return str_contains($items->first()->name, 'ノート');
        });
    }

    // 2. 検索キーワードの保持（マイリスト遷移時）
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

    //商品詳細情報取得--複数選択したカテゴリの確認を含む
     public function test_item_detail()
    {
        // ID:1 の商品を取得（関連データもロードしておくと確実です）
        $item = Item::with(['categories', 'condition', 'comments.user', 'likes'])->find(1);
        
        $response = $this->get('/item/' . $item->id);
        $response->assertStatus(200);

        // 1. 基本情報・画像・ブランド・価格などの表示確認
        $response->assertSee($item->image);
        $response->assertSee($item->name);
        $response->assertSee($item->brand);
        $response->assertSee(number_format($item->price));
        $response->assertSee($item->description);
        $response->assertSee($item->condition->content);

        // 2. いいね数・コメント数の表示確認
        $response->assertSee($item->likes->count());
        $response->assertSee($item->comments->count());

        // 3. 複数選択されたカテゴリの表示確認
        foreach ($item->categories as $category) {
            $response->assertSee($category->category);
        }

        // 4. コメントしたユーザーの情報とコメント内容の確認
        foreach ($item->comments as $comment) {
            $response->assertSee($comment->user->name); // もしくはユーザー名が表示される項目
            $response->assertSee($comment->comment);
        }
    }


    //いいね機能
    // 1 & 2. いいね登録、合計数増加、アイコン色の変化
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

        // ログインして商品詳細ページからいいねを実行（非同期通信ならPOSTのみ）
        $response = $this->actingAs($user)->post("/item/{$itemId}/like");

        // DBに登録されているか
        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'item_id' => $itemId
        ]);

        // 商品詳細ページを再取得して表示を確認
        $response = $this->get("/item/{$itemId}");
        $response->assertStatus(200);

        // 合計数が増加しているか（元の数 + 1）
        $response->assertSee($initialLikeCount + 1);

        // アイコンの色が変化しているか（例: activeクラスや特定の色のクラスが付与されているか）
        // 実装に合わせてクラス名（'text-red' や 'is-active' など）を変更してください
        $response->assertSee('ハートロゴ_ピンク.png'); 
    }

    // 3. 再度押下でいいね解除、合計数減少
    public function test_unlike_item()
    {
        $user = User::find(1);
        $user->email_verified_at = now();
        $user->postal_code = '123-4567';
        $user->address = '東京都';
        $user->save();

        $itemId = 4;

        // まず、いいね済みの状態を作る
        $user->likes()->create(['item_id' => $itemId]);
        $initialCount = Item::find($itemId)->likes()->count(); // この時点で1以上

        // 再度POSTして解除を実行
        $response = $this->actingAs($user)->post("/item/{$itemId}/like");

        // DBから削除されているか
        $this->assertDatabaseMissing('likes', [
            'user_id' => $user->id,
            'item_id' => $itemId
        ]);

        // 商品詳細ページで合計数が減少しているか
        $response = $this->get("/item/{$itemId}");
        $response->assertSee($initialCount - 1);
        
        // アイコンの色が元に戻っているか（activeクラスが消えているか）
        $response->assertDontSee('is-active');
    }

    //コメント送信機能
    // 1. ログインユーザーのコメント投稿と数件の増加
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

        // DBに保存されているか
        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'item_id' => $itemId,
            'content' => 'テストコメント'
        ]);

        // 詳細ページでコメント数が増加しているか確認
        $response = $this->get("/item/{$itemId}");
        $response->assertSee($initialCount + 1);
    }

    // 2. 未ログインユーザーは送信不可
    public function test_unauthenticated_user_cannot_comment()
    {
        $itemId = 1;
        
        // ログインせずにPOST送信
        $response = $this->post("/item/comment/{$itemId}", [
            'comment' => '未ログインのコメント'
        ]);

        // ログインページへリダイレクトされるか（実装によります）
        $response->assertRedirect('/login');
        
        // DBにデータが追加されていないか
        $this->assertDatabaseMissing('comments', [
            'comment' => '未ログインのコメント'
        ]);
    }

    // 3. 空入力のバリデーション
    public function test_comment_validation_required()
    {
        $user = User::find(1);
        $user->email_verified_at = now();
        $user->postal_code = '123-4567';
        $user->address = '東京都';
        $user->save();
        
        $response = $this->actingAs($user)->post('/item/comment/1', [
            'comment' => '' // 空入力
        ]);

        // バリデーションエラーがセッションにあるか
        $response->assertSessionHasErrors(['comment']);
    }

    // 4. 255字超えのバリデーション
    public function test_comment_validation_max_length()
    {
        $user = User::find(1);
        $user->email_verified_at = now();
        $user->postal_code = '123-4567';
        $user->address = '東京都';
        $user->save();
        
        $response = $this->actingAs($user)->post('/item/comment/1', [
            'comment' => str_repeat('あ', 256) // 256文字
        ]);

        $response->assertSessionHasErrors(['comment']);
    }
}
