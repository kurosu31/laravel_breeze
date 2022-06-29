# ○docker関連のファイルを用意
# src
# docker-compose.yml
# Dockerfile

# nginxでのmysqlの設定
  nginx/default.confにて
   root  /var/www/html/<アプリ名>/public; 
  を必ず編集しておくこと

# を用意し,ファイルを記述
 docker compose build
 docker compose up -d

アプリを作成
composer create-project laravel/laravel <アプリ名>  "8.*"

  
  docker exec -it <アプリのDB名>  /usr/bin/mysql -u root -p

Laravelをインストールする
ディレクトリで以下のコマンドを実行し、appコンテナに入ります。
 docker compose exec app bash

 root@81d2cea8efe2:/var/www/html# でOK

 composer -V
  バージョンが表示されたらOKです。
 composer create-project --prefer-dist "laravel/laravel=6.*" .
 composer create-project --prefer-dist "laravel/laravel=8.*" .
コマンドの意味としては『Laravel6系を指定してカレントディレクトリに新規プロジェクトを作成する』です。
（先ほどのコマンドの末尾の.はカレントディレクトリという意味です）

ディレクトリで以下のコマンドを実行し、appコンテナに入ります。
 root@81d2cea8efe2:/var/www/html# npm install -D vue

 npm install -D vue-template-compiler
 これでVue.jsが使えるようになります。

 # typescriptを使う場合は
 npm install

 npm install ts-loader typescript react-router-dom @types/react @types/react-dom @types/react-router-dom --save-dev


 laravelのmigrateがうまくいかない場合は
 dockerとlaravelの .envファイルの記述に差異がないかを確認
  dockerの.env
    WEB_PORT=80
    DB_PORT=3306

    DB_NAME=docker_laravel_sample
    DB_USER=db_user
    DB_PASSWORD=root
    DB_ROOT_PASSWORD=root
  laravelの.env
    DB_CONNECTION=mysql
    DB_HOST=db
    DB_PORT=3306
    DB_DATABASE=docker_laravel_sample
    DB_USERNAME=root {root以外をお勧めする。}
    DB_PASSWORD=root

  キャッシュを再作成します。
    php artisan config:cache && php artisan route:cache

  のちにvolume確認
  ホスト側のターミナル(or コマンドプロンプト)にて、下記コマンドを実行
    docker volume ls
  下記volumeがあることを確認
    DRIVER    VOLUME NAME
    local     docker-practice_mysql-volume
  volume削除（必ずdockerを止めること）
  のちに削除コマンド
    docker volume rm ****_db-volume
  削除後volumeがないことを確認する
    docker volume ls
  再度ビルドする
    docker-compose up -d
  マイグレーション実行
  Laravelでマイグレーションを実行するため、Laravelプロジェクトが入っているphpコンテナへログイン
    docker-compose exec php bash
  以下、コンテナ内での作業となります
    /var/www/html# php artisan migrate

laravel AUTHの設定（VUEの場合）
  phpコンテナへログイン
    docker-compose exec php bash
  laravel/uiライブラリをインストールします。
    composer require laravel/ui 1.*
  実行
  vueの場合
    php artisan ui vue --auth
  reactの場合 
    php artisan ui react --auth
  のちvueのに処理
    npm install
    npm run dev
done