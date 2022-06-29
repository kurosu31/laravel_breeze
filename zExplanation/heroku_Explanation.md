herokuの設定とgitの設定
  herokuへログイン
    heroku login
  herokuのdockerコンテナーにもログインしておく
    heroku container:login
  gitのアカウントを確認する
    git config --list
      exp
      credential.helper=osxkeychain
      user.name=kurosu31
      user.email=kurosu31@hotmail.com
      ~~~~
  # herokuのアプリを作成
    herokuないでのアップロードする枠を確保する。どのアカウントともカブならない名前をつける。
      heroku create laravel-namake-app
  # herokuないにDBを作成する。（cleardb:ignit は無料枠）
      heroku addons:create cleardb:ignit -a laravel-namake-app
  # データベースの必要情報を取得
      heroku config -a laravel-namake-app
    CLEARDB_DATABASE_URL: mysql://<ユーザー名>:<パスワード>@<ホスト名>.com/データベース名>
    が帰ってくる
   # 環境変数名に合わせてターミナルで登録する。
    heroku config:add APP_DATABASE='データベース名' -a <一意のアプリ名>
    heroku config:add APP_DATABASE_USERNAME='ユーザー名' -a <一意のアプリ名>
    heroku config:add APP_DATABASE_PASSWORD='パスワード' -a <一意のアプリ名>
    heroku config:add APP_DATABASE_HOST='ホスト名' -a <一意のアプリ名>

  https設定
    https にするには app/Http/Middleware/TrustProxies.php の $proxies プロパティを以下のようにする
      namespace App\Http\Middleware;

      use Fideloper\Proxy\TrustProxies as Middleware;
      use Illuminate\Http\Request;

      class TrustProxies extends Middleware
      {
          /**
          * The trusted proxies for this application.
          *
          * @var array|string|null
          */
          protected $proxies = '*'; //ここに' = *'を追加するのみ！

          /**
          * The headers that should be used to detect proxies.
          *
          * @var int
          */
          protected $headers = Request::HEADER_X_FORWARDED_ALL;
      }
    を追記
    $proxiesに信用するプロキシのIPアドレスを追加します。'*'は全プロキシを信用するという設定です