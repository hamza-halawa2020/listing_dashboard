<?php

namespace App\Support\Chat;

use Illuminate\Support\Facades\DB;

class ChatUnreadCounter
{
    public function summarizeForUser(?int $userId): array
    {
        if (! $userId) {
            return [
                'unread_messages_count' => 0,
                'unread_conversations_count' => 0,
            ];
        }

        $baseQuery = DB::table('chat_conversation_participants as participants')
            ->join('chat_messages as messages', 'messages.chat_conversation_id', '=', 'participants.chat_conversation_id')
            ->where('participants.user_id', $userId)
            ->where('messages.sender_id', '!=', $userId)
            ->where(function ($query) {
                $query
                    ->whereNull('participants.last_read_at')
                    ->orWhereColumn('messages.created_at', '>', 'participants.last_read_at');
            });

        $unreadMessagesCount = (clone $baseQuery)->count();

        $unreadConversationsCount = (clone $baseQuery)
            ->distinct()
            ->count('participants.chat_conversation_id');

        return [
            'unread_messages_count' => $unreadMessagesCount,
            'unread_conversations_count' => $unreadConversationsCount,
        ];
    }

    public function totalUnreadMessagesForUser(?int $userId): int
    {
        return $this->summarizeForUser($userId)['unread_messages_count'];
    }
}
