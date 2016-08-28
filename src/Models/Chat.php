<?php

namespace Frameworkteam\Chat\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class Chat extends Model
{
    protected $table = 'im__chat';

    protected $fillable = [
        'user_id',
        'chat_id'
    ];
    
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    public function user()
    {
        return $this->hasOne(config('Chat')['User'], 'id', 'user_id');
    }

    public function message()
    {
        return $this->hasMany('Frameworkteam\Chat\Models\Message', 'chat_id', 'chat_id');
    }

    public static function getByUser($id, $second_id)
    {
        $chat = Chat::where('user_id','=', $id)->get();
        foreach($chat as $c){
            $ch = Chat::where('chat_id','=', $c->chat_id)->where('user_id', '<>', $id)->first();
            if($ch->user_id == $second_id){
                return $ch->chat_id;
            }
        }
        return false;
    }

    public static function countParticipant($chat){
        return  Chat::where('chat_id','=',$chat)->count();
    }

    public static function create_($id_current, $id_second){
        $hash = Str::random(70);
        Chat::create(array('user_id'=>$id_current, 'chat_id'=>$hash));
        Chat::create(array('user_id'=>$id_second, 'chat_id'=>$hash));
        return $hash;
    }

    public static function allChats($filter = 'all', $perPage = 0){
        $chats = Chat::where('user_id', '=', Auth::id())->where('is_ignoring', '=', 0);
        if ($filter == 'all') {
            $chats = $chats;
        } elseif ($filter == 'new') {
            $chats = $chats->whereHas('message', function ($query) {
                $query->where('is_read', '=', 0)->where('from_id', '<>', Auth::id());
            });
        } elseif ($filter == 'ignored') {
            $chats = Chat::where('user_id', '=', Auth::id())->where('is_ignoring', '=', '1');
        } else {
            abort('404');
        }
        
        if ($perPage) {
            $chats = $chats->paginate($perPage);
        } else {
            $chats = $chats->get();
        }

        $result = array();

        foreach($chats as $chat){
            $users = self::getUsers($chat->chat_id, true);
            unset($users[Auth::id()]);
            $chat->new_messages_count = self::countNewMessages($chat->chat_id);
            $chat->all_messages_count = self::countAllMessages($chat->chat_id);
            $chat->users = $users;
            $result[] = (object)array('users'=>$users, 'data'=>$chat);
        }

        return $chats;
    }

    public static function countNewMessages($chat){
        return Message::where('chat_id','=', $chat)->where('is_read', '=', '0')->where('from_id', '<>', Auth::id())->count();
    }

    public static function countAllMessages($chat){
        return Message::where('chat_id','=', $chat)->count();
    }

    public static function getUsers($chat, $exclude = false){
        $user = array();
        $userModel = config('Chat')['User'];
        $chats = Chat::where('chat_id','=',$chat);
        if ($exclude) {
            $chats->where('user_id', '<>', Auth::id());
        }
        foreach( $chats->get() as $chat_line){
            $user[] = $userModel::find($chat_line->user_id);
        }
        return $user;
    }

    public static function addUser($chat, $id){
        return Chat::create(array('user_id'=>$id, 'chat_id'=>$chat));
    }

// function below added by superjarilo
    public static function getChats($userId, $limit = null)
    {
        $chats = null;

        if ($userId) {
            $query = self::select('im__chats.*')
                ->leftJoin(DB::raw('im__chats as ic'), 'ic.chat_id', '=', 'im__chats.chat_id')
                ->where('ic.user_id', $userId)->where('im__chats.user_id', '<>', $userId)
                ->orderBy('im__chats.updated_at', 'desc')
                ->with('user.image');
            if ($limit) {
                $query->limit($limit);
            }
        }

        return $query->get();
    }


    public static function getChatsCount($chats, $userId)
    {
        $chatsCount = null;

        if (count($chats) && $userId) {
            $qb = DB::table('im__messages')->select('chat_id', DB::raw('count(*) as count'))
                ->whereIn('chat_id', $chats->lists('chat_id'))
                ->where('from_id', '<>', $userId)
                ->where('is_read', 0)
                ->whereNull('deleted_at')
                ->groupBy('chat_id');

            $chatsCount = collect($qb->get())->lists('count', 'chat_id');
        }

        return $chatsCount;
    }

    public function config() {
        dd(config());
    }
}
