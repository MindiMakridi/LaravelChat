<?php

namespace Frameworkteam\Chat;

use Frameworkteam\Chat\Models\Chat;
use Frameworkteam\Chat\Models\Message;

use Illuminate\Http\Request;

class ChatHelper
{
	public static function getHashOrCreateChat($userId, $secondUserId)
    {
        $chat_hash = Chat::getByUser($userId, $secondUserId);
        if ($chat_hash) {
            $count_user = Chat::countParticipant($chat_hash);
        } else {
            $count_user = 0;
        }
        if ($count_user > 2 or $count_user == 0) {
            $chat_hash = Chat::create_($userId, $secondUserId);
        }

        return $chat_hash;
    }

    public static function getLastMessageId()
    {
    	return Message::all()->max('id');
    }

    public static function getAllChats()
    {
        return Chat::allChats();
    }
}