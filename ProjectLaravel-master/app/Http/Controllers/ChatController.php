<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Models\Chat;
use App\Events\ChatEvent;
use App\Models\Payment;
use App\Events\PaymentEvent;

class ChatController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $chats = DB::table('chats')->where('author', '=', Auth::user()->email)->orWhere('sendto', '=', Auth::user()->email);
        return view('Chat', compact('chats'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
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
        if (!Auth::check()) {
            return redirect('/login');
        }
        if ($request->has('cardnumber')) {
            $data = $request->all();
            $payments = Payment::create($data);
            event(
                $e = new PaymentEvent($payments)
            );
            return;
        }
        //dd($request);
        if ($request->has('starvalue')) {
            $user_id = Auth::id();
            $point = $request->starvalue;
            $movie_id = $request->movie_id;
            $check =  DB::select("select * from nguoi_dung_danh_gias where user_id = '$user_id' and movie_id = '$movie_id'");
            if (count($check) > 0) {
                DB::table('nguoi_dung_danh_gias')->where('user_id', '=', $user_id)->where('movie_id', '=', $movie_id)->update([
                    'point' => $point,
                ]);
            } else {
                DB::table('nguoi_dung_danh_gias')->insert([
                    'user_id' => $user_id,
                    'movie_id' => $movie_id,
                    'point' => $point,
                ]);
            }
        } else if ($request->has('subcribevalue')) {

            $user_id = Auth::id();
            $subcribe = $request->subcribevalue;
            DB::update("update users set isSubcribe = '$subcribe' where id = '$user_id'");
        } else {
            $data = $request->all();
            $data["author"] = Auth::user()->email;
            if (Auth::user()->role_id == 1) {
                $data["sendto"] = $request->get('sendto');
            } else {
                $data["sendto"] = DB::table('users')->where('role_id', '=', 1)->get('email')[0]->email;
            }
            $chats = Chat::create($data);
            if (Auth::user()->role_id == 1) {
                DB::table('chats')->where('author', '=', Auth::user()->email)->update(['isRead' => 1]);
                DB::table('chats')->where('author', '=', $request->get('sendto'))->update(['isRead' => 1]);
            }
            event(
                $e = new ChatEvent($chats)
            );
        }

        //DB::table('chats')->where('id','<',$chats->id)->delete(); 
        //DB::table('chats')->delete();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
    }


    public function get(Request $request)
    {
        $data = $request->all();
        $author = $request->author;
        if (!empty($request->isRead)) {
            DB::table('chats')->where('author', '=', $author)->update(['isRead' => 1]);
        } else {
            $messages = DB::table('chats')->where('author', '=', $author)->orWhere('sendto', '=', $author)->get();
            DB::table('chats')->where('author', '=', $author)->update(['isRead' => 1]);
            return $messages;
        }
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
