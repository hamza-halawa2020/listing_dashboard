<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StoreChatConversationRequest;
use App\Http\Resources\Api\ChatConversationResource;
use App\Models\ChatConversation;
use App\Models\User;
use App\Support\Chat\ChatUnreadCounter;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ChatConversationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $conversations = ChatConversation::query()
            ->whereHas('participants', fn ($query) => $query->where('users.id', $user->id))
            ->with([
                'participants:id,name,role',
                'messages' => fn ($query) => $query->with('sender:id,name,role')->latest()->limit(20),
            ])
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at')
            ->get()
            ->each(fn (ChatConversation $conversation) => $conversation->setRelation('messages', $conversation->messages->sortBy('created_at')->values()));

        Log::info('chat.api.conversations.index', [
            'user_id' => $user?->id,
            'count' => $conversations->count(),
            'conversation_ids' => $conversations->pluck('id')->values()->all(),
        ]);

        return ChatConversationResource::collection($conversations);
    }

    public function contacts(Request $request)
    {
        $user = $request->user();

        $query = User::query()
            ->whereKeyNot($user->id)
            ->select(['id', 'name', 'role', 'phone']);

        if ($user->role === 'member') {
            $query->where('role', 'admin');
        } elseif ($user->role === 'service_provider') {
            $query->whereIn('role', ['admin', 'member']);
        }

        $contacts = $query->orderBy('name')->get();

        Log::info('chat.api.contacts.index', [
            'user_id' => $user?->id,
            'count' => $contacts->count(),
            'contact_ids' => $contacts->pluck('id')->values()->all(),
        ]);

        return response()->json([
            'data' => $contacts,
        ]);
    }

    public function summary(Request $request, ChatUnreadCounter $chatUnreadCounter)
    {
        $user = $request->user();
        $summary = $chatUnreadCounter->summarizeForUser($user?->id);

        Log::info('chat.api.summary', [
            'user_id' => $user?->id,
            ...$summary,
        ]);

        return response()->json([
            'data' => $summary,
        ]);
    }

    public function store(StoreChatConversationRequest $request)
    {
        $user = $request->user();
        $participantIds = collect($request->validated('participant_ids'))
            ->push($user->id)
            ->unique()
            ->values();

        $participants = User::query()
            ->whereIn('id', $participantIds)
            ->get(['id', 'role']);

        if ($participants->count() !== $participantIds->count()) {
            throw ValidationException::withMessages([
                'participant_ids' => __('Invalid participants selected.'),
            ]);
        }

        $roles = $participants->pluck('role');

        if ($roles->filter(fn ($role) => $role === 'member')->count() > 1) {
            throw ValidationException::withMessages([
                'participant_ids' => __('Members can chat only with admins.'),
            ]);
        }

        if ($roles->contains('member') && ! $roles->contains('admin')) {
            throw ValidationException::withMessages([
                'participant_ids' => __('Any conversation with members must include at least one admin.'),
            ]);
        }

        $conversation = null;

        if ($participantIds->count() === 2) {
            $conversationQuery = ChatConversation::query()
                ->where('type', 'direct')
                ->withCount('participants')
                ->having('participants_count', 2);

            foreach ($participantIds as $participantId) {
                $conversationQuery->whereHas('participants', fn ($query) => $query->where('users.id', $participantId));
            }

            $conversation = $conversationQuery->first();
        }

        if (! $conversation) {
            $conversation = ChatConversation::create([
                'created_by' => $user->id,
                'type' => $participantIds->count() > 2 ? 'group' : 'direct',
                'subject' => $request->validated('subject'),
            ]);

            $syncPayload = $participantIds
                ->mapWithKeys(fn ($participantId) => [
                    $participantId => [
                        'is_admin' => $participants->firstWhere('id', $participantId)?->role === 'admin',
                        'joined_at' => now(),
                    ],
                ])
                ->all();

            $conversation->participants()->sync($syncPayload);
        }

        $conversation->load([
            'participants:id,name,role',
            'messages' => fn ($query) => $query->with('sender:id,name,role')->latest()->limit(20),
        ]);
        $conversation->setRelation('messages', $conversation->messages->sortBy('created_at')->values());

        Log::info('chat.api.conversations.store', [
            'user_id' => $user?->id,
            'conversation_id' => $conversation->id,
            'type' => $conversation->type,
            'participant_ids' => $participantIds->all(),
        ]);

        return new ChatConversationResource($conversation);
    }

    public function markRead(Request $request, ChatConversation $chatConversation)
    {
        $user = $request->user();
        abort_unless($chatConversation->participants()->where('users.id', $user->id)->exists(), 403);

        $chatConversation->participants()->updateExistingPivot($user->id, [
            'last_read_at' => now(),
        ]);

        Log::info('chat.api.conversations.mark_read', [
            'user_id' => $user?->id,
            'conversation_id' => $chatConversation->id,
        ]);

        return response()->json(['message' => __('Conversation marked as read.')]);
    }
}
