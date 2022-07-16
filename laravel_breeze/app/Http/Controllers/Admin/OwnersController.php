<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Owner; // Eloquent エロクアント
use Illuminate\Support\Facades\DB; // QueryBuilder クエリービルダー

use Carbon\Carbon;

class OwnersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    // コントローラのコンストラクター内でミドルウェアを指定する
    // 認証(auth)を入れておく。認証の確認ができたら実行できるようになる。
    // 今回は admin で認証できてきるかを確認するため $this->middleware('auth:admin'); とする。
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index()
    {
        $date_now = Carbon::now();
        $date_parse = Carbon::parse(now());
        echo $date_now->year;

        echo $date_parse;
        // Eloquent手法での取得 モデルのインスタンスから取得
        $e_all = Owner::all();
        // queryBuilder手法での取得 get()
        $q_get = DB::table('owners')->select('name', 'created_at')->get();
        // // queryBuilder手法での取得 first()
        // $q_first = DB::table('owners')->select('name')->first();
        // // collection型での取得方法
        // $c_test = collect([
        //     'name ' => 'テスト'
        // ]);
        // var_dump($q_get);
        // var_dump($q_first);

        // dd($e_mail, $q_get, $q_first, $c_test);
        return view('admin.owners.index', compact('e_all', 'q_get'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
