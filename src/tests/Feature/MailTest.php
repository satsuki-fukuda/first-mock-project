<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

class MailTest extends TestCase
{
use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * 1. 会員登録時に認証メールが送信されることの検証
     */
    public function test_registration_sends_verification_email()
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => '新規ユーザー',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // 登録後に未認証画面などへリダイレクトされることを確認
        $response->assertStatus(302);

        $user = User::where('email', 'newuser@example.com')->first();

        // 認証通知（VerifyEmail）が送信されたか確認
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /**
     * 2 & 3. 認証リンク押下で認証が完了し、プロフィール設定画面が表示されることの検証
     */
    public function test_email_verification_flow_and_redirect_to_profile()
    {
        $user = User::factory()->create([
            'email_verified_at' => null, // 未認証状態
        ]);

        // 認証用URLを生成（Laravel標準の通知機能を使用している場合）
        $verificationUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // 2. 認証リンクへアクセス（「認証はこちらから」ボタンの押下をシミュレート）
        $response = $this->actingAs($user)->get($verificationUrl);

        // 3. 認証完了後の検証
        // 通常、認証後は /mypage/profile などの設定画面へリダイレクトされる実装を想定
        $response->assertRedirect('/?verified=1');
        
        // ユーザーが認証済み（email_verified_atが入っている）か確認
        $this->assertNotNull($user->fresh()->email_verified_at);

        // リダイレクト先（プロフィール設定画面）が正常に表示されるか
        $finalResponse = $this->actingAs($user->fresh())->get('/mypage/profile');
        $finalResponse->assertStatus(200);
        $finalResponse->assertSee('プロフィール設定');
    }
}
