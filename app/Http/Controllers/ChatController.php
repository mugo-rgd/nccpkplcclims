<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Events\UserTyping;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $conversations = Conversation::whereHas('participants', function ($q) {
                $q->where('user_id', auth()->id());
            })
            ->with(['latestMessage', 'participants.user'])
            ->orderByDesc('last_message_at')
            ->get();

        $users = User::where('id', '!=', auth()->id())
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return view('chat.index', compact('conversations', 'users'));
    }

    public function getConversations()
    {
        $conversations = Conversation::whereHas('participants', function ($q) {
                $q->where('user_id', auth()->id());
            })
            ->with(['latestMessage', 'participants.user'])
            ->orderByDesc('last_message_at')
            ->get()
            ->map(function ($conversation) {
                $conversation->unread_count = $conversation->getUnreadCount(auth()->id());
                return $conversation;
            });

        return response()->json($conversations);
    }

    public function show(Conversation $conversation)
    {
        $this->authorizeParticipant($conversation);

        $conversation->load(['participants.user']);

        $conversation->markAsRead(auth()->id());

       $messages = $conversation->messages()
    ->with('sender')
    ->oldest()
    ->take(50)
    ->get()
    ->map(function ($message) {
        if ($message->type === 'file' && $message->file_path) {
            $message->file_url = asset('storage/' . $message->file_path);
        }

        return $message;
    });

        return response()->json([
            'conversation' => $conversation,
            'messages' => $messages,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'recipient_id' => ['required', 'exists:users,id', 'different:' . auth()->id()],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $conversation = Conversation::createPrivateConversation(
            auth()->id(),
            $request->recipient_id
        );

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => auth()->id(),
            'message' => $request->message,
            'type' => 'text',
            'is_read' => false,
        ]);

        $conversation->update([
            'last_message_at' => now(),
        ]);

        broadcast(new MessageSent($message, $conversation))->toOthers();

        return response()->json([
            'success' => true,
            'message' => $message->load('sender'),
            'conversation_id' => $conversation->id,
        ]);
    }

    public function sendMessage(Request $request, Conversation $conversation)
    {
        $this->authorizeParticipant($conversation);

        $validator = Validator::make($request->all(), [
            'message' => ['required', 'string', 'max:5000'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => auth()->id(),
            'message' => $request->message,
            'type' => 'text',
            'is_read' => false,
        ]);

        $conversation->update([
            'last_message_at' => now(),
        ]);

        broadcast(new MessageSent($message, $conversation))->toOthers();

        return response()->json([
            'success' => true,
            'message' => $message->load('sender'),
        ]);
    }

    public function markAsRead(Conversation $conversation)
    {
        $this->authorizeParticipant($conversation);

        $conversation->markAsRead(auth()->id());

        return response()->json([
            'success' => true,
        ]);
    }

    public function typing(Request $request, Conversation $conversation)
    {
        $this->authorizeParticipant($conversation);

        $isTyping = $request->boolean('is_typing', true);

        broadcast(new UserTyping(auth()->id(), $conversation->id, $isTyping))->toOthers();

        return response()->json([
            'success' => true,
        ]);
    }

    public function getUnreadCount()
    {
        $conversations = Conversation::whereHas('participants', function ($q) {
                $q->where('user_id', auth()->id());
            })
            ->get();

        $count = $conversations->sum(function ($conversation) {
            return $conversation->getUnreadCount(auth()->id());
        });

        return response()->json([
            'unread_count' => $count,
        ]);
    }

    public function createGroup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'members' => ['required', 'array', 'min:2'],
            'members.*' => ['exists:users,id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $conversation = Conversation::create([
            'name' => $request->name,
            'type' => 'group',
            'slug' => Str::slug($request->name . '-' . time()),
            'created_by' => auth()->id(),
            'is_active' => true,
            'last_message_at' => now(),
        ]);

        $members = collect($request->members)
            ->push(auth()->id())
            ->unique()
            ->values();

        foreach ($members as $userId) {
            ConversationParticipant::create([
                'conversation_id' => $conversation->id,
                'user_id' => $userId,
                'is_admin' => (int) $userId === auth()->id(),
                'joined_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'conversation' => $conversation->load('participants.user'),
        ]);
    }

    private function authorizeParticipant(Conversation $conversation): void
    {
        $isParticipant = $conversation->participants()
            ->where('user_id', auth()->id())
            ->exists();

        abort_unless($isParticipant, 403, 'You are not a participant in this conversation.');
    }
    public function uploadFile(Request $request, Conversation $conversation)
{
    $this->authorizeParticipant($conversation);

    $request->validate([
        'file' => [
            'required',
            'file',
            'max:10240',
            'mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,txt,csv,zip'
        ],
    ]);

    $file = $request->file('file');

    $path = $file->store('chat_uploads', 'public');

    $message = Message::create([
    'conversation_id' => $conversation->id,
    'sender_id' => auth()->id(),
    'message' => $file->getClientOriginalName(),
    'type' => 'file',
    'file_path' => $path,
    'file_name' => $file->getClientOriginalName(),
    'file_type' => $file->getClientMimeType(),
    'file_size' => $file->getSize(),
    'is_read' => false,
]);

$conversation->update([
    'last_message_at' => now(),
]);

broadcast(new MessageSent($message, $conversation))->toOthers();

$message = $message->load('sender');
$message->file_url = asset('storage/' . $path);

return response()->json([
    'success' => true,
    'message' => $message,
]);
}
}
