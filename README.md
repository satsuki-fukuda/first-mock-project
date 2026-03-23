# first-mock-project

## 環境構築
**Dockerビルド**
1. `git clone git@github.com:satsuki-fukuda/first-mock-project.git
2. cd first-mock-project
3. DockerDesktopアプリを立ち上げる
4. `docker-compose up -d --build`


**Laravel環境構築**
1. `docker-compose exec php bash`
2. `composer install`
3. 「.env.example」ファイルを 「.env」ファイルに命名を変更。または、新しく.envファイルを作成
4. .envに以下の環境変数を追加
``` text
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass
```
5. アプリケーションキーの作成
``` bash
php artisan key:generate
```

6. マイグレーションの実行
``` bash
php artisan migrate
```

7. シーディングの実行
``` bash
php artisan db:seed
```
8. シンボリックリンク作成
``` bash
php artisan storage:link
```
**メール認証**
<br>ローカル環境でのメール送信テストにMailHogを使用します</br>
1. `docker-compose up -d mailhog`
2. .envに以下の環境変数を追加
``` text
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=hello.example.com
```
3. ブラウザで http://localhost:8025 にアクセスします
4. アプリケーションからメールを送信します。
5. MailHogのUI上にメールが表示されます。

**決済機能について**
<br>Stripeを使用します。公式リンク：https://stripe.com/jp</br>
<br>コンビニ支払い、カード支払いを設定していますがコンビニ支払い選択でレシート印刷画面に遷移するため、カード支払いをテストカードにて行い予定の画面遷移を行います。</br>
<br>.envにAPIキーを設定する</br>
``` text
STRIPE_PUBLIC_KEY=
STRIPE_SECRET_KEY=
STRIPE_WEBHOOK_SECRET=
```
Webhook署名について
<br>ローカル開発時は Stripe CLI でstripe listen --forward-to localhost:8080/webhookを実行して取得</br>
<br>本番環境ではStripeダッシュボードのWebhook設定から取得</br>

**PHPUnitテストについて**
<br>phpコンテナにてvendor/bin/phpunit tests コマンドにて実行</br>

## 使用技術(実行環境)
- PHP8.3.0
- Laravel8.83.27
- MySQL8.0.26

## テーブル設計
<img width="819" height="190" alt="スクリーンショット 2026-03-22 21 33 40" src="https://github.com/user-attachments/assets/c14b0ea5-a407-460d-ae28-392051a7ac00" />
<img width="819" height="274" alt="スクリーンショット 2026-03-22 21 33 56" src="https://github.com/user-attachments/assets/7b9c94f8-130f-4381-83f8-3c7749411cb1" />
<img width="818" height="130" alt="スクリーンショット 2026-03-22 21 34 19" src="https://github.com/user-attachments/assets/1728ee94-3519-490d-8308-b432867bec31" />
<img width="819" height="300" alt="スクリーンショット 2026-03-22 21 34 31" src="https://github.com/user-attachments/assets/a570db2a-81c8-49ae-8f02-6acb7003431d" />
<img width="820" height="159" alt="スクリーンショット 2026-03-22 21 34 46" src="https://github.com/user-attachments/assets/83611754-5610-4e69-b310-1d10045cc0e6" />
<img width="817" height="176" alt="スクリーンショット 2026-03-22 21 35 01" src="https://github.com/user-attachments/assets/cbc32e03-50d4-4c90-91fb-8cffaa4a70f2" />
<img width="818" height="131" alt="スクリーンショット 2026-03-22 21 35 22" src="https://github.com/user-attachments/assets/0f181931-aef8-4460-aae2-6549d4bd72f4" />
<img width="819" height="151" alt="スクリーンショット 2026-03-22 21 35 43" src="https://github.com/user-attachments/assets/f3a5edda-454c-4378-879c-756b514bfd99" />
<img width="818" height="211" alt="スクリーンショット 2026-03-22 21 36 13" src="https://github.com/user-attachments/assets/76831807-5ddb-483a-abc5-0ce736ddc87e" />


## ER図
<img width="354" height="502" alt="スクリーンショット 2026-03-22 17 10 01" src="https://github.com/user-attachments/assets/c1148190-ed46-457b-bcf5-269309144328" />


## URL
- 開発環境：http://localhost/
- phpMyAdmin:：http://localhost:8080/
