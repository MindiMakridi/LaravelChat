<?php

namespace Frameworkteam\Chat\Models;

use Auth;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = 'im__message';

    public function from()
    {
        return $this->hasOne('App\Model\User\User', 'id', 'from_id');
    }

    public static function getByID($chat)
    {
        $messsage = Message::where('chat_id', '=', $chat)->get();

        return $messsage;
    }

    public static function getLongPull($chat, $lastId = 0)
    {
        $qb = Message::where('chat_id', '=', $chat)->with('from');

        if ($lastId) {
            $qb->where('id', '>', $lastId);
        }

        return $qb->get();
    }

    public static function read($id_current, $chat)
    {
        $messsage = Message::where('is_read', '=', 0)
            ->where('from_id', '<>', $id_current)
            ->where('chat_id', '=', $chat)
            ->update(array('is_read' => 1));
    }

    public static function send($chat, $id_user, $text)
    {
        $msg = new Message;
        $msg->chat_id = $chat;
        $msg->from_id = $id_user;
        $msg->text = $text;
        $msg->is_read = 0;
        $msg->save();

        return $msg;
    }

    public static function delete_($chat, $id){
        $msg = Message::find($id);
        if($msg->chat_id == $chat && $msg->from_id == Auth::id()){
            $msg->delete();
            return true;
        }
        return false;
    }
}
