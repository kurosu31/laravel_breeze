
laravelのconfigなどの変更や設定が反映されない場合は、
  applicationキャッシュクリアコマンドは、Laravelのアプリで動いている基本的なキャッシュを削除するコマンドです。
    php artisan cache:clear
  routeキャッシュクリアコマンドは、Laravelルート部分のキャッシュをクリアしてくれるコマンドです。
    php artisan route:clear
  configキャッシュクリアコマンドは、Laravelコンフィグ部分のキャッシュをクリアしてくれるコマンドです
    php artisan config:clear
  configキャッシュクリアコマンドは、Laravelビュー部分(bladeファイルなど)のキャッシュをクリアしてくれるコマンドです。 
    php artisan view:clear



Laravel6 Authのメールアドレスの認証を実装してみる(ローカル開発時)
（VerifyEmail）https://poppotennis.com/posts/laravel_verify
 モデルの編集 下記のように変更(implementsを追記)
 User.php
    class User extends Authenticatable implements MustVerifyEmail
 ルートを編集
    web.php
    //ユーザー認証を作成した時に自動的に作成されるルートを下記のように変更
    Auth::routes(['verify' => true]);
    middleware の verified を追加する
    ①HomeController.phpを編集するやり方
      HomeController.php
    public function __construct()
    {
        //verifiedを追加
        $this->middleware(['auth','verified']);
    }
    ②web.phpを編集するやり方
    verifiedを追加
    Route::group(['middleware' => ['auth','verified']], function () {
      ~~~
    });
 メールドライバーの設定
  開発中は mailtrap が簡単だと思います。
  実際には AWS の SES や Sendgrid を使うことになると思いますが、.env を書き換えるだけで使えるみたいです
  mailtrap は、開発中、メール送信をテストするもので、登録されているメールアドレスに本当に送信しないでくれるものです。
  送信されたメールは mailtrap の管理画面で見れます。
  このページでログインをして、 https://mailtrap.io/inboxes/1530220/messages/2468796862/advanced_html_analysis

  MAIL_DRIVER=smtp
  MAIL_HOST=smtp.mailtrap.io
  MAIL_PORT=2525
  MAIL_USERNAME=???
  MAIL_PASSWORD=??
  MAIL_FROM_ADDRESS=from@example.com
  MAIL_FROM_NAME=Example
  この部分をコピーして.env に貼ります。初期が Rails なので Laravel で探してください。
  php artisan config:cache
done

seederの使い方
  ログイン認証などのダミープログラムを起動させてテストするための機能
  seederファイル作成コマンド
   php artisan make:seeder {適当な名前}
  /database/seeds配下にUsersTableSeeder.phpが出来上がるので、下記のように修正
  <?php
  namespace Database\Seeders;

  use Carbon\Carbon;
  use Illuminate\Database\Seeder;
  use Illuminate\Support\Facades\DB;

  class UsersTableSeeder extends Seeder
  {
      /**
      * Run the database seeds.
      *
      * @return void
      */
      public function run()
      {
          DB::table('users')->insert([
              'name' => 'test taro',
              'email' => 'test@example.com',
              'password' => bcrypt('password'),
              'created_at' => Carbon::now(),
              'updated_at' => Carbon::now(),
          ]);
      }
  }
  下記を実行すれば、usersテーブルにseederで定義したダミーデータが作られます。
    php artisan db:seed --class=UsersTableSeeder
  もしpath系のエラーの場合はターミナルで
    composer dump-autoload
  で整理する

★ウェブカツ参考資料 https://hackmd.io/@72rg5ZuITwqI78OrJnVIOg/SJGofaJ3V
コントローラの作成方法
    php artisan make:controller コントローラー名
  ※コントローラー名ははアッパーキャメルケースで○○Controllerという命名規則（複数形にするのが慣習）
  今回は
    php artisan make:controller DrillsController
  app/Http/Controllersの中に自動で下記のようなファイルが作られます。
    <?php

    namespace App\Http\Controllers;
    use Illuminate\Http\Request;
    class DrillsController extends Controller
    {
        //表示させるアクションを作る
         public function new()
          {
            //ビューを表示するには return view('ビュー名');
              return view('drills.new');
          }
    }
  とすることで、resources/views配下のビューファイルが読み込まれます。
  ディレクトリを切っているなら、.で繋いで指定をします。
  コマンドで特に重要なのは最後に付与している--resourceというオプションです。
  このオプションを付与することによって、コントローラー内でCRUD特性の機能を一式セットにして登録して、扱えるようにしてくれます。

ビューの作成方法
  今回、コントローラーではdrills.newと指定しているので、それと同じ場所に同じ名前のビューファイルを作れば読み込まれます。
  今回は resources/views/drills/new.blade.php
  formのname属性はレコード名と同じにしておくと余計な処理を書かずに楽にDBへ保存できます。
    <form method="POST" action="{{ route('ビュー名') }}">
    <form method="POST" action="{{ route('drills.new') }}">
ルーターの処理
  作成したページ飛べるようにをroutingしておく
  Laravelではルーティングはroutes/web.phpで設定します。
    //get exp データ表示など
    Route::get('/drills/new', 'DrillsController@new')->name('ビュー名');
    Route::get('/drills/new', 'DrillsController@new')->name('drills.new');
    //post exp データ流し込みなど
    Route::post('/drills', 'DrillsController@create')->name('drills.create');
    // 対象ファイルの記述に注意<form method="POST" ✨action="{{ route('drills.create') }}">
  http://localhost:8888/drills/new で表示される

多言語化しよう！
 多言語化させるためにはhtml上で文字を出力する際に__()囲う必要があります。
    {{ __('Title') }} こんな感じ
  公式リファレンス↓
  https://readouble.com/laravel/5.4/ja/localization.html
  多言語対応させる設定ファイルはresources/lang配下にファイルを設置します。phpファイルでやる方法とjsonファイルで設定する方法がありますが、jsonファイルを使ってみましょう。
  resources/lang/ja.jsonを作り、下記のような設定
    {
        "Drill Register": "練習登録",
        "Title": "タイトル",
        "Category": "カテゴリー",
        "Problem": "問題"
    }
  アプリケーションのデフォルト言語はconfig/app.php設定ファイルで指定します。
  fallback_localeでは翻訳できるものが翻訳ファイルになかった時にデフォルトで表示させる言語を指定できます。

バリデーションチェック
 app/controller/ にてmigrationで登録した物をチェックできる
     public function create(Request $request)
    {
        $request->validate([
      //exp "<カラム名>" => '<必須項目>|<文字列など>|<最大値:225>'
            "title" => 'required|string|max:225',
      //exp "<カラム名>" => '<文字列 etc>|<最大値:225 etc>'
            'problem9' => 'string|max:255',
        ]);
    }
    requiredは必須項目
    stringは文字列
    integerは整数値
    betweenは範囲指定
    emailはe-mail形式のチェック
    が可能
  inputの入力保持
  入力画面（今回は new.blade.php）inputのにてvalueに old() を指定する
    exp <input id="title" type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title') }}" required autocomplete="title" autofocus>
  
データベース操作方法
  LaravelではORMには、「Eloquent ORM」
  モデルはappディレクトリ配下に置きましょう。（composer.jsonで設定すれば、好きな場所に置くことができます）
  モデルの命名規則は単数形でアッパーキャメルケースです。（単数系でつけるのが慣わし）
    php artisan make:model モデル名
    php artisan make:model Drill 
  マイグレーションファイルと一緒にモデルも作成したい場合は、--migrationオプションをつけましょう。
    php artisan make:model Drill --migration
  省略して-mと書く事もできます。
    php artisan make:model Drill -m
  コマンドを叩くと下記のようなモデルのファイルが生成
    namespace App;

    use Illuminate\Database\Eloquent\Model;

    class Drill extends Model
    {
      //  自動的にモデル名の複数形のテーブル名と対応します（それでよければ無記入でOK）
          もし、テーブル名がモデル名（複数形）ではない場合は下記のように$tableプロパティで設定できます。
            protected $table = 'my_drills';

      // DBで間違っても変更させたくないカラム（ユーザーID、管理者権限 など）にはロックをかけておく事ができる
      // ロックの掛け方にはfillableかguardedの２種類がある。
      // どちらかしか指定できない
      // $fillable 変更したいカラムを指定。モデルがその属性以外を持たなくなる（fillメソッドに対応しやすいが、カラムが増えるほど足していく必要あり）
        protected $fillable = ['title', 'category_name', 'problem0', 'problem1', 'problem2', 'problem3', 'problem4', 'problem5', 'problem6', 'problem7', 'problem8', 'problem9'];
      // $guarded 変更したくないカラムを指定。モデルからその属性が取り除かれる（カラムが増えてもほとんど変更しなくて良い）
         protected $guarded = ['id'];

    }
  カラム名の変更
    １度既に作ったカラムの名前や各種制限を変更するのもマイグレーションを作成します。
    ただし、カラムを変更する前にcomposer.jsonファイルでdoctrine/dbalを追加する必要があります。 公式リファレンス https://readouble.com/laravel/5.6/ja/migrations.html
      composer require doctrine/dbal
    カラム変更用のマイグレーションファイルを作成する
      php artisan make:migration change_problem_not_null_to_null_on_drills_table --table=drills
    次にマイグレーションファイルを下記のように修正します。 src/migtation/ファイル名
          public function up()
      {
          Schema::table('drills', function (Blueprint $table) {
            //  nullを許容する場合 ->nullable()を追加
            $table->text('problem1')->nullable()->change();
            ~~~~~
          });
      }
    
          public function down()
      {
          Schema::table('drills', function (Blueprint $table) {
            // nullableに false を忘れないこと 元に戻すため
            $table->text('problem1')->nullable(false)->change();
            ~~~~~
          });
      }
    migrateの実行
      php artisan migrate
    done
  既存のテーブルにカラムを追加する
      php artisan make:migration <伝わりやすい命名をする> --table=<追加したいテーブル名>
    今回はdrillsテーブルにuserカラムを追加
      php artisan make:migration add_user_id_to_drills --table=drills
    作成したフィルに記述
      public function up()
      {
          Schema::table('drills', function (Blueprint $table) {
              // 一旦データを削除する。
              DB::statement('DELETE FROM drills');
              // ユーザーテーブルの作成
              $table->unsignedBigInteger('user_id');
              // 外部キーをつける
              //$table->foreign('外部キー')->references('参照キー')->on('参照テーブル');
              $table->foreign('user_id')->references('id')->on('users');
          });
      }

      public function down()
      {
          Schema::table('drills', function (Blueprint $table) {
              // 外部キー付きのカラムを削除するにはまず必ず外部キー制約を外す必要があります
              $table->dropForeign(['user_id']);
              $table->dropColumn('user_id');
          });
      }
    done
    php artisan migrate で新規して確認する。
    mysqlにて indexの一覧を確認
    mysql> show index from drills;
    +--------+------------+------------------------+--------------+-------------+-----------~~~
    | Table  | Non_unique | Key_name               | Seq_in_index | Column_name | Collation ~~~
    +--------+------------+------------------------+--------------+-------------+-----------~~~
    | drills |          0 | PRIMARY                |            1 | id          | A         ~~~
    | drills |          1 | drills_user_id_foreign |            1 | user_id     | A         ~~~
    +--------+------------+------------------------+--------------+-------------+---------  ~~~
    2 rows in set (0.03 sec)
    と出ていればOK!
  テーブルの結合をしてみる。
  「ログイン中のユーザーの登録した練習だけ」を取得したい場合、「テーブルの結合（JOIN）」をしたSQLを実行
    しかし、ORMを使う場合、そういったSQLを書く代わりに「モデル同士のリレーション（関係性）」
    を貼ることで、自動的にテーブル結合したSQLを作って実行できます。
    「１対１」「１対多」「多対多」と呼ばれているもの
    今回のようにユーザーに対して、多くの練習が紐づく場合は「１対多」になりますね。
    src/app/User.phpにて追記
      ~~~~
      public function drills() // 複数あることを暗示させるために複数形で
      {
          // 「○対多」の場合
          //  return $this->hasMany('<App\から紐づく先を指定>');
          return $this->hasMany('App\Drill');
      }
    これで、アクション内で drillsの情報一括取得
      $drills = User::find(<任意のユーザーid>)->drills;
        // 「○対多」なので
      foreach ($drills as $drill) {
      }
      と取得できる
    src/app/User.php
       ~~~
      public function user() // 単体を暗示させるため単数系で 
      {
          //「1対○」の場合
          return $this->hasMany('App\Drill');
      }
    これで、アクション内で user情報取得
      $drill = Drill::find(1);
      echo $drill->user->email;
      と取得できる

  DBへ保存する
    ORMってなに？(laravelでのDB操作であり、SQLを用いずに行う)
    ORMは「SQLを書かなくていいもの」です。
    オブジェクトを扱うようにDB操作が行える機能になります。

    Controllerへ記述
    // 初めに use をすることを忘れないで！（呼び出し）
    use App\Drill;
    ~~~~
    public function create(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category_name' => 'required|string|max:255',
            'problem0' => 'required|string|max:255',
            'problem1' => 'nullable|string|max:255',
            ~~~
        ]);
        // モデルを使って、DBに登録する値をセット ( use App\Drill; を忘れない！！)
        $drill = new Drill;

        // １つ１つ入れるか（カラムが多いと大変）
        // $drill->title = $request->title;
        // $drill->category_name = $request->category_name;
        // $drill->save();

        // fillを使って一気にいれるか
        // $fillableを使っていないと変なデータが入り込んだ場合に勝手にDBが更新されてしまうので注意！
          $drill->fill($request->all())->save();

        // リダイレクトする（登録後に飛ぶリンク）
        // withでsessionを詰めることができる。sessionフラッシュにメッセージを入れる 登録時などに一度だけ使うsession
        return redirect('/drills/new')->with('flash_message', __('Registered.'));
        return redirect('<リダイレクト対象ファイル>')->with('<呼び出し時に使う変数>', __('<表示したい文言>'));
    }     
    requiredは必須項目
    stringは文字列
    integerは整数値
    betweenは範囲指定
    emailはe-mail形式のチェック
    が可能
   sessionフラッシュにメッセージ 今回は app.blade.phpにて呼び出す 
    exp 
    ~~~~
    <!-- フラッシュメッセージ -->
    @if (session('flash_message'))
    <div class='alert alert-primary text-center' role="alert">
        {{ session('flash_message') }}
    </div>
    @endif
  ルーティングを追加する
    routes/web.phpにてRouteを追加
      Route::post('/drills', 'DrillsController@create')->name('drills.create');
    今回は登録なのでPOST送信で記述

モデルからDBへアクセスし、情報を取得
  作成したControllerへ記述し、アクションとして登録する
  今回は DrillsController にて
    ~~~
    class DrillsController extends Controller
    {
        public function index(){
            $drills = Drill::all();
            // return view(<渡し先のview>, [<ビュー先で使いたい変数名> => <取得した情報を入れた変数>]); 
            // もしくはcombine()を使用する
            // return view(<渡し先のview>, combine(<ビュー先で使いたい変数名>)); 
            // で自動的にビュー先で渡したい変数名に取得した情報がはいる
            return view('drills.index', ['drills' => $drills]);
        }
    ~~~
  sqlでいうSelect文を作成できる
    // 全権取得し変数へ
    $drills = Drill::all();
    $drills = <対象データ>::all();
    // １レコードずつ取得する場合
    foreach ( $drills as $drill) {
      $drills->title; 
    }
    // 主キーで指定したモデル取得
    $drills = Drill::find(1);
    // クエリ条件にマッチした最初のレコード取得
    $drills = Drill::where('title', 'aiueo')->first();
    表示のviewを作成する。
    今回は渡す先が、drills.index となっているので index.blade.php とする。
    表示方法
      @foreach ($drills as $drill)
        <p>{{$drills->title}}</p>
      @endforeach
  ルートに登録する。今回は route/web.php 表示のみなので get で
    Route::get('/drills', 'DrillsController@index')->name('drills');
  ルーティングにgetパラメーターをつける場合は{}で囲う。その箇所のURLの値をコントローラー側で取得できるようになります。
    exp
    Route::get('/drills/{id}/edit', 'DrillsController@edit')->name('drills.edit');
  パラメータには必須パラメータと任意パラメータがあります。
  必須の場合 {id}
    Route::get('/drills/{id}/edit', 'DrillsController@edit')->name('drills.edit');
  任意の場合 {id?}
    Route::get('/drills/{id?}/edit', 'DrillsController@edit')->name('drills.edit');
  取得方法
    編集などの場合
     Controllerに記述
      ...
      class DrillsController extends Controller
      {
          public function edit($id){ // idを受け取る
              // GETパラメータが数字かどうかをチェックする
              // 事前にチェックしておくことでDBへの無駄なアクセスが減らせる（WEBサーバーへのアクセスのみで済む）
              if(!ctype_digit($id)){ //GETパラメータのvalid ctype_digit()は「数字かどうかを調べる」関数である。
                  return redirect('/drills/new')->with('flash_message', __('Invalid operation was performed.'));
              }
              
              $drill = Drill::find($id);
              return view('drills.edit', ['drill' => $drill]);
          }
      ...
    ルーティングを作成
      exp
      Route::get('/drills/{id}/edit', 'DrillsController@edit')->name('drills.edit');
    使用方法
    今回は edit.blade.php や update.blade.php に渡す
    データ送信の場合
      <form method="POST" action="{{ route('drills.update',$drill->id ) }}">
      <form method="POST" action="{{ route('drills.edit',$drill->id ) }}">
    データ表示の場合
      <input value="{{ $drill->title}}"> etc

リンクの機能追加
  今回は index.blade.phpのaタグにリンク先
    exp
    <a href="{{ route('drills.edit',$drill->id ) }}" class="btn btn-primary">{{ __('Go Practice')  }}</a>
    route('<リンク先の指定>','<データを渡す場合は記述>')してあげるとリンク先にとび、データを渡せる

ビューコンポーザー
  各画面などて繰り返し使う処理、パーツをまとめてしまう。テスト工数などの削減につながる。
  1. サービスプロバイダクラスの作成
      ServiceProviderクラスはartisanコマンドで簡単に作れます。
      「サービスプロバイダ」というのは「アプリケーションの準備段階で何かしらの処理や設定を行う」ため用のクラスとして用意されているものです。
        exp
        php artisan make:provider ComposerServiceProvider
      app/Providers/ComposerServiceProvider.php に作成される。
      サービスプロバイダクラスは「ServiceProvider」というクラスを継承することで使えるようになります。
      ビューコンポーザを使うには「boot()」内に処理を書いていくことになります。
  2. ビューコンポーザクラスの作成とcomposeメソッドの実装
      ビューコンポーザークラスはapp/Http内のどこに置いても大丈夫です。
      今回はHttp配下のViewComposersというディレクトリを作って、そこにはUserComposerを作りましょう。
      コンポーザーは特に継承もしない普通のクラスです。
        namespace App\Http\ViewComposers;

        use Illuminate\Contracts\View\View;

        class UserComposer {
          // $view->with('user', 'ユーザー情報を第二引数へ');

          // protected この変数のなんかでしか使わないメゾット
          protected $auth;

          //Guard  Auth::ファサードみたいなもの 使用する認証系の情報が詰まっている（呼び出しを忘れない）
          public function __construct(Guard $auth)
          {
            // Guardで取得してきた認証情報を $authに代入
            $this->auth = $auth;
          }
          
          public function compose(View $view)
          {
              // userという変数を使えるようにし、その中に$this->auth->user()という値を詰めてビューに渡す。という定義の仕方になります
              $view->with('user', $this->auth->user());
          }
        }
  3. サービスプロバイダクラスのbootメソッドでビューコンポーザクラスを利用する
    app/Providers/ComposerServiceProvider.php に作成されたサービスプロバイダークラスを編集
      exp
      namespace App\Providers;

      use Illuminate\Support\ServiceProvider;
      use App\Http\ViewComposers\UserComposer;
      use Illuminate\Support\Facades\View;

      class ComposerServiceProvider extends ServiceProvider
      {
          public function register()
          {
              //
          }
          public function boot()
          {
              // 連想配列で渡します
              // キーにコンポーザーを指定し、値にビューを指定します（ワイルドカードも使えます）
              // この場合、layoutsディレクトリ配下のビューテンプレートが読み込まれた場合にUserComposerを読み込む（＝$userが作られる）という設定の仕方になります
              View::composers([
                  UserComposer::class    => 'layouts.*'
              ]);
          }
      }
    ※コンポーザーでは、ビューで常に行われる処理だけを書きましょう
    config/app.phpのprovidersにComposerServiceProviderを追加することで、ComposerServiceProviderをLaravelが自動で読み込んで実行できるようになります
      'providers' => [
          ...
          
          App\Providers\ComposerServiceProvider::class,

      ],
    ビューで表示させる
      <p>{{ Auth::user()->name }}</p>
      ↓
      <p>{{ $user->email }}</p>
      // これだけで表示可能
    ビューは「ただコントローラーから受け取った値を表示するだけ」にし、ビューとコントローラーの役割を分けてあげたほうがコードが推測しやすくなりますね。
    「恐らくコントローラーに書いてあるだろう」と推測して調べまくったけど実はビューにゴリゴリ書いてあった。なんて事になります。

サービスプロバイダーを通して、SQLログを出力さる
  LaravelはデフォルトでSQLのログを出力してくれないので、デバッグ時に困ることがよくあります。そんな時は、
  app/Providers/AppServiceProvider.phpに下記を追記しましょう。
    ~~~~
      public function boot()
    {
        // 商用環境以外だった場合、SQLログを出力させます
        if (config('app.env') !== 'production') {
            \DB::listen(function ($query) {
                \Log::info("Query Time:{$query->time}s] $query->sql");
            });
        }
    }
  storage/logs/laravel.log にsqlの履歴が表示される。

マイページのアクションとビュー、ルーティングの作成
  アクシションでログインしている自分のIDを元にレコードを取得します。
  ログインしているユーザーのレコードを取るのは、Authファサードからuserモデルが取得できるため、そこからリレーションを張っているdrillsモデルを操作できます。
    $drills = Auth::user()->drills();
  DrillsControllerに下記アクションを追加
    use Illuminate\Support\Facades\Auth;
    ...
    public function mypage(){
        $drills = Auth::user()->drills;
        return view('drills.mypage', compact('drills'));
    }
  DrillsControllerを編集する。
    public function create(Request $request)
    {
        ...
        $drill = new Drill;
        // $drill->fill($request->all())->save();
        // Auth::user()でuserを判定し、
        // drills()に渡してsave()で保存する。
        Auth::user()->drills()->save($drill->fill($request->all()));
        ...
    }

ログインしていないと機能を使えないようにしよう！
  現状では、ログインをしていなくてもマイページや登録など各種機能にアクセスできてしまいます
  Laravelでは各コントローラーのaction実行前や実行後に何らかの処理をさせたい場合（ログインしているかを確かめ、ログインしていなければそのactionは実行しないようにするなど）のためにミドルウェアという機能が用意されています。
  ミドルウェアはアクション（エンドポイント）ごとに設定できる、ローカルミドルウェアとアプリ全体で設定できるグローバルミドルウェアがあります。
  ミドルウェアを作成
    php artisan make:middleware CheckLoggedIn
  編集する。今回はアクションを実行する前に処理をする。
  ~~~~~
      public function handle($request, Closure $next)
    {
        // Auth::check()でログインしているか確認できるのでfalseの場合はlogin画面にリダイレクトさせる
        if (!Auth::check()) {
            return redirect('login');
        }

        return $next($request);
    }
      アクションを実行した後の場合は $response = $next($request);をつける
  ~~~~~
      public function handle($request, Closure $next)
    {
          //アクションを実行した後の場合は
         $response = $next($request);
      
        if (!Auth::check()) {
            return redirect('login');
        }

        //アクションを実行した後の場合は
        return $response;
    }
  done
  グローバルミドルウェアの設定の仕方
  あるミドルウェアをアプリケーションの全HTTPリクエストで実行したい場合は、app/Http/Kernel.phpクラスの$middlewareプロパティへ追加します。

  ローカルミドルウェアの設定の仕方
  特定のルートのみに対しミドルウェアを指定したい場合は、先ずapp/Http/Kernel.phpファイルでミドルウェアの短縮キーを登録します。
      ~~~~~~~
      protected $routeMiddleware = [
        ~~~~~~
        'check' => \App\Http\Middleware\CheckLoggedIn::class,
      ]
  あとはweb.phpで、それぞれの設定したいルートに対してミドルウェアを設定します。
    Route::get('/mypage', 'DrillsController@mypage')->name('drills.mypage')->middleware('check');

paginationの実装
  controller(Drillscontroller.php)にて記述（今回はmypage.blade.php）
  exp
  Drillscontroller.php
    public function mypage(){
      $drills - Auth::user()->drills()->pagenate(<表示件数>);
    }
  mypage.blade.php
      {{ $drills->links() }}
    以上

バリデーションの外部化
  フォームリクエストファイルを作る
    php artisan make:request DrillRequest
  