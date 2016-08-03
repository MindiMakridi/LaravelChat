<?php
namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App;

class ChatController extends Controller {

    public function index($userId)
    {
       $user = Auth::user();
       $userModel = config('Chat')['User'];
       $secondUser = new $userModel;
       $secondUser = $secondUser::where('id', $userId)->first();
       abort_if((!$user || !$secondUser || $user->id == $secondUser->id), '404');
       $chat = App::make('chat');
       $chatId = $chat::getHashOrCreateChat($user->id, $userId);

        return view('frameworkteam.chat.chat', [
            'user' => $user,
            'chat_id' => $chatId,
        ]);
    }


    

}