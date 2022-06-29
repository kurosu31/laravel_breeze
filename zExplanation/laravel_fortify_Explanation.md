# 必ず確認
 dockerディレクトリ default.conf 
      root  /var/www/html/l<プロジェクト名>/public; 

# fortifyパッケージのインストール
  fortifyパッケージのインストールをcomposerコマンドで行います。
      cd <プロジェクト名> // プロジェクトに移動
      composer require laravel/fortify
    
  fortifyに関連するファイルの作成をphp artisan vendor:publishコマンドで行います。
     php artisan vendor:publish --provider="Laravel\Fortify\FortifyServiceProvider"
  コマンドを実行するとFortifyに関連するテーブルのマイグレーションファイル(2014_10_12_200000_add_two_factor_columns_to_users_table)やapp/Actionsディレクトリにユーザ登録のバリデーションが含まれるCreateNewUser.phpファイルといったファイルが作成されます。
  Fortifyをインストールすることでどのようなルーティングが追加されたかはphp artisan route:listコマンドを実行して確認することができます。Fortifyに関連する非常にたくさんのルーティングが追加されていることが確認できます。
  # 必ず確認
  dockerの.envファイルとlaravelの.envファイルを合わせること
  DB_CONNECTION=mysql
  DB_HOST=db
  DB_PORT=3306
  DB_DATABASE=<プロジェクト名>
  DB_USERNAME=user
  DB_PASSWORD=root
  DB_ROOT_PASSWORD=root

  # dbの接続を確認しておく 今回はmysql
    docker compose exec db bash
    show databeses;
  php artisan migrateコマンドを実行してください。4つのテーブルが作成されます。

 # Fortifyはapp¥config¥fortify.phpファイルでFortifyに関する設定を変更することができます。 どの機能を利用するかもこのファイルで設定可能です。
  今回は認証部分のみの動作確認を行うため、その他の機能はコメントします。
  'features' => [
    Features::registration(),
    // Features::resetPasswords(),
    // // Features::emailVerification(),
    // Features::updateProfileInformation(),
    // Features::updatePasswords(),
    // Features::twoFactorAuthentication([
    //     'confirmPassword' => true,
    // ]),
    ]
    設定後にphp artisan route:listを実行してみてください。先ほどに比べてルーティングが少なく極端になっていることが確認できます。
    文字が小さくて見えにくいですが、認証に必要なルーティングのlogin, logout, registerを確認することができます。このリストのAction列を確認することで各URLにアクセスした際に実行されるコントローラーとメソッドの情報もわかります。

# ServiceProviderの登録
  先ほど実行したphp artisan vendor:publishコマンドではapp/Providersディレクトリの下にサービスプロバイダーのFortifyServiceProvider.phpファイルが作成されます
  FortifyServiceProvider.phpをconfig¥app.phpファイルに追加します。
    config¥app.phpファイル
    'providers' => [
      //略
      App\Providers\RouteServiceProvider::class,
      App\Providers\FortifyServiceProvider::class,　//追加
      //略
    ]

  # サービスプロバイダーのapp.phpファイルへの登録が完了したらFortifyServiceProvider.phpにログイン画面を表示するviewファイルの設定を行います。
    FortifyServiceProvider.php
    public function boot()
    {
    Fortify::createUsersUsing(CreateNewUser::class);
    Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
    Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
    Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

    //以下を追加
    Fortify::loginView(function () {
        return view('auth.login');
    });
    }

# ログイン画面の作成
Fortifyの公式ドキュメントには/loginに対してemailとpasswordを含んだPOSTリクエストを送信するとFortifyが認証の処理を行ってくれると記載されているので実際にそのような動作になるのか確認してみましょう。

先ほどから説明している通り、Fortifyでは各自でログイン画面を作成する必要があります。下記ではemailとpasswordの入力欄を持つログイン画面を作成しています。バリデーションのエラーが表示されること以外については通常のHTMLの入力フォームと同じだと思いますので各自で簡単に作成することができます。
    <!DOCTYPE html>
    <html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login</title>
    </head>
    <body>
        <h1>ログイン画面</h1>
        @if ($errors->any())
            <div>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form  method="POST"　action="/login">
            @csrf
            <div>
                <label for="email">メールアドレス</label>
                <input name="email" type="email" value="{{old('email')}}"/>
            </div>
            <div>
                <label for="password">パスワード</label>
                <input name="password" type="password" />
            </div>
            <div>
                <button type="submit">送信</button>
            </div>
        </form>
    </body>
    </html>
    ・These credentials do not match our records.
    ログインエラーが表示される
    このようにFortifyでは自分でログイン画面を作成し、/loginのPOSTリクエストを送ることで認証処理が行われることがわかりました。

    つまりバックエンド側の認証処理はFortifyが行ってくれるので、ユーザはフロントエンド側の画面の作成に集中することができます。

    ログイン画面の作成が完了したので次はユーザの登録をどのように行うのかユーザ登録画面の作成を行っていきます。

# ユーザ登録の処理
  ユーザの登録画面の作成はユーザのログイン画面の作成と同様です。FortifyServiceProviderにviewの追加設定を行い、各自でregister.blade.phpファイルを作成します。

  Fortify::registerViewを追加することでブラウザから/registerにアクセスするとauth.registerが存在する場合auth.registerに記述した内容が表示されるようになります。

  authの下にregister.blade.phpファイルを作成します。ログインの時とは異なり、/registerのPOSTリクエストではname, email, password, password_confirmationの4つを追加する必要があります。

  ユーザ登録のバリデーションはFortifyをphp artisan vendro:publishを実行した際に作成されるapp¥Actionsの下のCreateNewUser.phpファイルで確認、変更することが可能です。
# register.blade.phpファイル作成。
  <!DOCTYPE html>
  <html lang="ja">
  <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Login</title>
  </head>
  <body>
      <h1>ユーザ登録画面</h1>
      @if ($errors->any())
          <div>
              <ul>
                  @foreach ($errors->all() as $error)
                      <li>{{ $error }}</li>
                  @endforeach
              </ul>
          </div>
      @endif
      <form  method="POST"　action="{{ route("register") }}">
          @csrf
          <div>
              <label for="name">ユーザ名</label>
          <input name="name" type="text" value="{{ old('name') }}"/>
          </div>
          <div>
              <label for="email">メールアドレス</label>
          <input name="email" type="email" value="{{ old('email') }}"/>
          </div>
          <div>
              <label for="email">パスワード</label>
              <input name="password" type="password"/>
          </div>
          <div>
              <label for="email">パスワード確認</label>
              <input name="password_confirmation" type="password"/>
          </div>
          <div>
              <button type="submit">登録</button>
          </div>
      </form>    
  </body>
  </html>

  404 NOT FOUNDエラー
    なぜ/homeにリダイレクトされるかはfortifyの設定ファイルである¥app¥config¥fortify.phpで確認することができます。
    'home' => RouteServiceProvider::HOME,
    
  app¥Providers¥RouteServiceProvider.phpを見ると/homeに設定されていることがわかります。この値を設定することで別の場所にログイン後にリダイレクトすることができます。
    public const HOME = '/home';
  /homeにリダイレクトされることがわかったのでルーティングをweb.phpファイルに追加します。ログインしたユーザのみアクセスできるようにmiddlewareでauthを設定しています。
    Route::get('/home', function () {
        dd('ログイン成功');
    })->middleware('auth');
  追加後先ほどエラーが発生したブラウザをリロードしてください。画面にログイン成功が表示されることが確認できます。
  
# ログアウトの処理
  ログアウトについては/logoutにアクセスするだけではなくPOSTリクエストを送信する必要があります。

  下記のようにHome.blade.phpファイルを作成します。
    <!DOCTYPE html>
    <html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Home</title>
    </head>
    <body>
        <h1>ホーム画面</h1>
        <p>ようこそLaravelアプリケーションへ</p>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">ログアウト</button>
        </form>
    </body>
    </html>
  ログイン中に/homeにアクセスすると下記の画面が表示されます。ボタンをクリックするとログアウトが行われ”/”にリダイレクトされます。