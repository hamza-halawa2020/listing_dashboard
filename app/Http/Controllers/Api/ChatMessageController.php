<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StoreChatMessageRequest;
use App\Http\Resources\Api\ChatMessageResource;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatMessageController extends Controller
{
    public function index(Request $request, ChatConversation $chatConversation)
    {
        $user = $request->user();
        abort_unless($chatConversation->participants()->where('users.id', $user->id)->exists(), 403);

        $limit = min(max($request->integer('limit', 25), 1), 50);
        $beforeMessageId = $request->filled('before_message_id')
            ? max((int) $request->input('before_message_id'), 1)
            : null;

        $messages = ChatMessage::query()
            ->where('chat_conversation_id', $chatConversation->id)
            ->with('sender:id,name,role')
            ->when(
                $beforeMessageId !== null,
                fn ($query) => $query->where('id', '<', $beforeMessageId),
            )
            ->latest('id')
            ->limit($limit + 1)
            ->get();

        $hasMore = $messages->count() > $limit;

        if ($hasMore) {
            $messages = $messages->take($limit);
        }

        $messages = $messages->reverse()->values();

        Log::info('chat.api.messages.index', [
            'user_id' => $user?->id,
            'conversation_id' => $chatConversation->id,
            'limit' => $limit,
            'before_message_id' => $beforeMessageId,
            'returned_count' => $messages->count(),
            'message_ids' => $messages->pluck('id')->values()->all(),
            'has_more' => $hasMore,
            'next_before_message_id' => $hasMore ? $messages->first()?->id : null,
        ]);

        return response()->json([
            'data' => ChatMessageResource::collection($messages)->resolve(),
            'meta' => [
                'limit' => $limit,
                'has_more' => $hasMore,
                'next_before_message_id' => $hasMore ? $messages->first()?->id : null,
            ],
        ]);
    }

    public function store(StoreChatMessageRequest $request, ChatConversation $chatConversation)
    {
        $user = $request->user();
        abort_unless($chatConversation->participants()->where('users.id', $user->id)->exists(), 403);

        $message = ChatMessage::create([
            'chat_conversation_id' => $chatConversation->id,
            'sender_id' => $user->id,
            'body' => $request->validated('body'),
            'meta' => $request->validated('meta'),
        ]);

        $chatConversation->update([
            'last_message_at' => now(),
        ]);

        $chatConversation->participants()->updateExistingPivot($user->id, [
            'last_read_at' => now(),
        ]);

        $message->load('sender:id,name,role');

        Log::info('chat.api.messages.store', [
            'user_id' => $user?->id,
            'conversation_id' => $chatConversation->id,
            'message_id' => $message->id,
        ]);

        return new ChatMessageResource($message);
    }
}
