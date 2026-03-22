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

    //メール認証機能--会員登録後の認証メール送信
    public function test_registration_sends_verification_email()
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => '新規ユーザー',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(302);
        $user = User::where('email', 'newuser@example.com')->first();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    //メール認証機能--ボタン押下後認証サイトへの遷移、認証完了後のプロフィール設定画面への遷移
    public function test_email_verification_flow_and_redirect_to_profile()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $verificationUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $response->assertRedirect('/?verified=1');

        $this->assertNotNull($user->fresh()->email_verified_at);

        $finalResponse = $this->actingAs($user->fresh())->get('/mypage/profile');
        $finalResponse->assertStatus(200);
        $finalResponse->assertSee('プロフィール設定');
    }
}
