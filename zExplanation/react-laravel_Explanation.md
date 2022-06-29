 # 新版のLaravelとなるため、バージョンを合わせたい場合は
 create-project --prefer-dist laravel/laravel
 ↓
 create-project --prefer-dist "laravel/laravel=8.4"

# UIパッケージ導入
   composer require laravel/ui

# React.js導入
   php artisan ui react --auth
# 下記の様の出たらOK
React scaffolding installed successfully.
Please run "npm install && npm run dev" to compile your fresh scaffolding.
Authentication scaffolding generated successfully.

# ここで確認のためビルド
 npm run dev

# resources/viewsの下はシングルページとするためapp.blade.phpのみとする
下記4項目を削除する
/auth以下
layouts - (app.blade.phpをviews直下に移動させlayoutsディレクトリ自体を削除)
home.blade.php
welcome.blade.php
# 削除後にresources/viewsディレクトリ下は下記のようになっていればOK
resources
  ┗ views
    ┗ app.blade.php (layouts下に出来ていたものをviews下に移動する)

# views/app.blade.phpを修正
既存のコードに対してid="app"の中身を空っぽにする
    <div id="app">
      # id="app"の中身を空する変更
    </div>

# resources/js/components/Example.jsを修正
id="app"としたため、getElementByIdを下記のように変更
example→app
Example.js
// 20行目以下を修正
export default Example;

if (document.getElementById('app')) {
    ReactDOM.render(<Example />, document.getElementById('app'));
}

# routes/web.phpを修正
シングルページアプリケーションなのでどんなURLだったとしても/views/app.blade.phpを表示するように設定します。
# 画面をリロードしてExample Componentという文字列が表示されたらReactの導入はOK

// 追記
Route::get('{any}', function () {
    return view('app');
})->where('any','.*');

# typescriptを使う場合は

 npm install ts-loader typescript react-router-dom @types/react @types/react-dom @types/react-router-dom  @types/bootstrap  --save-dev
# webpack.mixにて
mix.ts('resources/ts/index.tsx', 'public/js')
   .sass('resources/sass/app.scss', 'public/css')
   .version(); 
 と書き換え

# tsconfig.json作成
./node_modules/.bin/tsc --init
# tsconfig.json 書き換え
 {
    "compilerOptions": {
        "outDir": "./built/",
        "sourceMap": true,
        "strict": true,
        "noImplicitReturns": true,
        "noImplicitAny": true,
        "module": "es2015",
        "jsx": "react",
        "experimentalDecorators": true,
        "emitDecoratorMetadata": true,
        "moduleResolution": "node",
        "target": "es6",
        "lib": [
            "es2019",
            "dom"
        ],
        "allowSyntheticDefaultImports": true
    },
    "include": [
        "resources/ts/**/*"
    ]
}
# app.blade.phpを編集する
    - <script src="{{ asset('js/app.js') }}" defer></script>
    + <link rel="stylesheet" href="{{ mix('css/app.css') }}">
     <!-- Scripts -->
    + <script src="{{ mix('js/app.js') }}" defer></script>
# resourcesにtsフォルダを作成しする
ex. 
app.tsx
    import React from "react";
    import ReactDOM from "react-dom";
    import 'bootstrap';

    import Index from "./Index";

    ReactDOM.render(
        <Index />,
        document.getElementById("app")
    );
Index.tsx
    import React from 'react';

    const Index: React.FC = () => {
        return (
            <div>
                <p>こんにちは</p>
            </div>
        )
    }
export default Index;

# chakra ui を導入
npm i @chakra-ui/react @emotion/react@^11 @emotion/styled@^11 framer-motion@^6
 ダウンロードできない場合は強制的に --save --legacy-peer-deps
# 注意！！！！！
webpack mix の場合？？ framer-motionはv3.10.6 にしておいた方が無難。コンパイルエラー？webpackでのエラーが出る。

# sanctum導入
composer require laravel/sanctum

# 2.sanctum設定ファイルと移行ファイルを公開
以下コマンドを実行し、sanctumで利用するconfig設定ファイルと、sanctum用のテーブルを作成するマイグレーションファイルを作成します。

config/sanctum.php
database/migrations/2019_12_14_000001_create_personal_access_tokens_table.php
こちらは、トークンでの認証のみ必要なテーブルなので削除してもOK

php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# 3.ミドルウェアの追加
Sanctumのミドルウェアをアプリケーションのapp/Http/Kernel.phpファイル内の、apiミドルウェアグループに追加する

    app/Http/Kernel.php
    use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

    'api' => [
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class, // here
    'throttle:api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ],

EnsureFrontendRequestsAreStatefulクラスは、ざっくりと以下のような処理を行う

セッションクッキーのセキュア設定を強制
cookie のスコープ(参照・操作の権限)を HTTP リクエストに制限し、javascriptなどから直接参照・操作されないようにする
クッキーの暗号化
レスポンスにクッキー付与(\Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class)
セッションの開始
laravel_sessionをレスポンスに追加
CSRFトークンを検証する

# 4.ドメイン設定
sanctum.phpに、クッキーを使用した「ステートフル」な認証を維持する必要があるドメイン(フロントエンドサーバーのドメインでOK)を指定
デフォルトでlocalhostが設定されているので、特殊なことをしていない限り特にローカル環境で開発するだけなら追加する必要はなし
本番にリリースする際は必ず本番のサイトドメインを指定する必要あり
※.envのSANCTUM_STATEFUL_DOMAINSに設定するか、このファイルに直接ドメインを記載する必要があります。(”の中に複数指定可)

config/sanctum.php

'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
  '%s%s',
  'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
  env('APP_URL') ? ','.parse_url(env('APP_URL'), PHP_URL_HOST) : ''
))),

# 5.CORS(クロスオリジンリソースシェアリング)
CORS(Cross-Origin Resource Sharing)とは、WebブラウザがHTMLを読み込んだ以外のサーバからデータを取得する仕組みです。

※異なるオリジン間で通信する場合に必要(例えば、ローカルのDockerでフロントエンドはlocalhost:3000でNext.jsを起動し、バックエンドはlocalhost:9000で起動している等)

config/cors.phpのsupports_credentialsオプションをtrueに
    'supports_credentials' => true,
‘paths’ に ‘sanctum/csrf-cookie’を追加(login,logoutなど随時追加あり)

# 6.サブドメインでセッションを共有したい場合???
アプリケーションのセッションクッキードメイン設定で、ルートドメインのサブドメインを指定する
(.envのSESSION_DOMAINでもOK)

SESSION_DOMAINには、ドメインの異なるサイトでセッションを共有したい場合にそのドメインを記述する(片方でログインしたらもう一方でもログイン状態を維持したいみたいな内容)

sample01.hoge.com
sample02.hoge.com
例えば上記のようなサイトがあった場合、以下のように指定すると、双方のドメインで認証のセッションを共有することができる

config/session.php


'domain' => '.hoge.com',

# 7.セッション管理
.envファイルのSESSION_DRIVER=の値はデフォルトだと「file」ですが、これを「cookie」にすることでsanctum認証用のトークンをcookieで管理することができますし、「database」にすることでdatabaes管理にすることができます。(他にも管理方法はあるので、詳細は公式を確認ください。)

.env

SESSION_DRIVER=file 
# SESSION_DRIVER=cookie 
# SESSION_DRIVER=database
管理をdatabaseにした場合は以下コマンドで専用のテーブルを作成する必要があります。


php artisan session:table && php artisan migrate