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
use ChatHelper;

class MessageController extends Controller
{

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
        elseif ($request->user_id && $request->text)
        {
            $chat_id = ChatHelper::getHashOrCreateChat(Auth::id(), $request->user_id);

            $message = Message::send($chat_id, Auth::id(), $request->text);
            if ($message) {
                $chat = Chat::where('chat_id', $chat_id)->get()->first();
                $chat->setUpdatedAt($chat->freshTimestamp());
                $chat->save();
            }  
        }

        if ($request->redirect) {
            return redirect($request->redirect);
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