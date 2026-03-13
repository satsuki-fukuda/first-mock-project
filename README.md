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

## 使用技術(実行環境)
- PHP8.3.0
- Laravel8.83.27
- MySQL8.0.26

## テーブル設計
![alt](table_1.png)
![alt](table_3.png)
<!--変える-->

## ER図
![alt](er.png)
<!--変える-->

## URL
- 開発環境：http://localhost/
- phpMyAdmin:：http://localhost:8080/
