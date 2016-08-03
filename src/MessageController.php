<?php
namespace Frameworkteam\Chat;

use App\Http\Controllers\Controller;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;

use DB;
use Auth;
use Input;

use App\Model\User\User;
use Frameworkteam\Chat\Models\Message;
use Frameworkteam\Chat\Models\Chat;

class MessageController extends Controller
{
	public function getHashOrCreateChat()
    {
        $id_current = Auth::id();
        $id_second = Input::get('id');
        $chat_hash = Chat::getByUser($id_current, $id_second);
        if ($chat_hash) {
            $count_user = Chat::countParticipant($chat_hash);
        } else {
            $count_user = 0;
        }
        if ($count_user > 2 or $count_user == 0) {
            $chat_hash = Chat::create_($id_current, $id_second);
        }

        return $chat_hash;
    }

    public function getJson()
    {
        $id_current = Auth::id();
        $chat = Input::get('chat');
        $messages = Message::getLongPull($chat);
        Message::read($id_current, $chat);
        $messages = $this->remoteUnnecessary($messages);

        return response()->json(array('messages' => $messages));
    }

    public function remoteUnnecessary($messages)
    {
        foreach ($messages as $key => $message) {
            $messages[$key]->name_sender = $message->from->name;
            unset(
                $message->updated_at,
                $message->deleted_at
            );
        }

        return $messages;
    }

    public function longpull(Request $request)
    {
        $time = time();
        $userId = Auth::user()->id;
        $lastId = $request->last_id;
        $chatId = $request->chat_id;

        $messages = [];

        if ($chatId) {
            Message::read($userId, $chatId);
            $messages = Message::getLongPull($chatId, $lastId);

            $messages = $this->remoteUnnecessary($messages);

        }

        return response()->json($messages);
    }

    public function send(Request $request)
    {
        $message = [];

        if ($request->chat && $request->text)
        {
            $message = Message::send($request->chat, Auth::id(), $request->text);

            if ($message) {
                $chat = Chat::where('chat_id', $request->chat)->get()->first();
                $chat->setUpdatedAt($chat->freshTimestamp());
                $chat->save();
            }
        }

        return response()->json($message);
    }

    public function deleteMessage(Request $request)
    {
        abort_if(!Auth::user(), '404');
        $id = $request->id;
        $chat = $request->chat;
        return response()->json(Message::delete_($chat, $id));
    }

    public function isRead(Request $request)
    {
        $chatId = $request->chatId;
        $ids = $request->ids ?: [];

        $messages = [];

        if ($chatId && count($ids)) {
            $messages = Message::select('id', 'is_read')
                ->where('chat_id', $chatId)
                ->whereIn('id', $ids)
            ->lists('is_read', 'id');
        }

        return response()->json($messages);
    }

    public function contacts(Request $request)
    {
        $user = Auth::user();

        $userIds = $request->userIds;
        $filter = $request->filter ?: [];

        $users = null;

        if ($user) {
            $query = User::where('id', '<>', $user->id);

            if ($userIds) {
                $query->whereNotIn('id', $userIds);
            }

            $byRoles = $request->byRoles;

            if ($byRoles && count($byRoles)) {
                $query->byRoles($byRoles);
            }

            if (isset($filter['userName'])) {
                $query->where('name', 'LIKE', '%' . $filter['userName'] . '%');
            }

            $users = $query->with('image')->paginate(7);
        }

        $template = $request->ajax() ? 'message.modal.content' : 'message.contacts';

        return view($template)
            ->with('users', $users)
            ->with('userIds', $userIds)
            ->with('filter', $filter);
    }
}