<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class LoginTest extends TestCase
{
    use RefreshDatabase;
    //ログイン機能--メールバリデーション
    public function test_login_user_validate_email()
    {
        $response = $this->post('/login', [
            'email' => "",
            'password' => "password",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');

        $errors = session('errors');
        $this->assertEquals('メールアドレスを入力してください', $errors->first('email'));
    }

    //ログイン機能--パスワードバリデーション
    public function test_login_user_validate_password()
    {
        $response = $this->post('/login', [
            'email' => "test1@example.com",
            'password' => "",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');

        $errors = session('errors');
        $this->assertEquals('パスワードを入力してください', $errors->first('password'));
    }

    //ログイン機能--入力情報バリデーション
    public function test_login_user_validate_wrong_credentials()
    {
        $response = $this->post('/login', [
            'email' => "notfound@example.com",
            'password' => "wrong-password",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');

        $errors = session('errors');
        $this->assertEquals('ログイン情報が登録されていません', $errors->first('email'));
    }

    //ログイン機能--ログイン処理
    public function test_login_user()
    {
        $user = User::factory()->create([
            'email' => 'test1@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test1@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }
    //ログアウト機能
    public function test_logout_user()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    
}
