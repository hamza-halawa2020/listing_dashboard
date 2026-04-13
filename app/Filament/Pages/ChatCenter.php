<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\AuthorizesPageAccess;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use App\Support\Chat\ChatUnreadCounter;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;

class ChatCenter extends Page
{
    use AuthorizesPageAccess;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = null;

    protected string $view = 'filament.pages.chat-center';

    protected Width|string|null $maxContentWidth = Width::Full;

    public ?int $activeConversationId = null;

    public string $draft = '';

    public string $search = '';

    public function mount(): void
    {
        if ($this->activeConversationId !== null) {
            $this->ensureActiveConversationStillExists();

            return;
        }

        $firstConversationId = collect($this->contacts)
            ->pluck('conversation_id')
            ->filter()
            ->first()
            ?? collect($this->groupConversations)->pluck('id')->first();

        if ($firstConversationId) {
            $this->selectConversation((int) $firstConversationId);
        }
    }

    protected static function getAccessPermissionName(): ?string
    {
        return 'chat_conversations.view_any';
    }

    public static function getNavigationLabel(): string
    {
        return __('chat.navigation_label');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getUnreadMessagesCount();

        if ($count < 1) {
            return null;
        }

        return $count > 99 ? '99+' : (string) $count;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getUnreadMessagesCount() > 0 ? 'danger' : null;
    }

    public static function getUnreadMessagesCount(): int
    {
        return app(ChatUnreadCounter::class)->totalUnreadMessagesForUser(auth()->id());
    }

    public function getHeading(): string | Htmlable | null
    {
        return null;
    }

    public function refreshData(): void
    {
        $this->refreshSidebar();
        $this->refreshCurrentConversation();
    }

    public function refreshSidebar(): void
    {
        $this->ensureActiveConversationStillExists();
    }

    public function refreshCurrentConversation(): void
    {
        if (! $this->activeConversationId) {
            return;
        }

        if (! $this->userParticipatesInConversation($this->activeConversationId)) {
            $this->activeConversationId = null;
            $this->draft = '';
            $this->invalidateChatComputedState();

            return;
        }

        if (($this->activeConversation['has_unread'] ?? false) === true) {
            $this->markActiveConversationRead();
            $this->invalidateSidebarComputedState();
        }
    }

    public function selectConversation(int $conversationId): void
    {
        if ($conversationId === $this->activeConversationId) {
            return;
        }

        if (! $this->userParticipatesInConversation($conversationId)) {
            return;
        }

        $this->activeConversationId = $conversationId;
        $this->invalidateChatComputedState();
        $this->dispatchScrollToBottom();

        if (($this->activeConversation['has_unread'] ?? false) === true) {
            $this->markActiveConversationRead();
            $this->invalidateSidebarComputedState();
        }
    }

    public function openConversationWithUser(int $userId): void
    {
        $contact = collect($this->contacts)->firstWhere('id', $userId);

        if (! $contact) {
            return;
        }

        if (! empty($contact['conversation_id'])) {
            $this->selectConversation((int) $contact['conversation_id']);

            return;
        }

        $this->startDirectConversation($userId);
    }

    public function send(): void
    {
        $user = auth()->user();
        $body = trim($this->draft);

        if (! $user || ! $this->activeConversationId || $body === '') {
            return;
        }

        if (! $this->userParticipatesInConversation($this->activeConversationId, $user->id)) {
            return;
        }

        ChatMessage::create([
            'chat_conversation_id' => $this->activeConversationId,
            'sender_id' => $user->id,
            'body' => $body,
        ]);

        ChatConversation::query()->whereKey($this->activeConversationId)->update([
            'last_message_at' => now(),
        ]);

        $this->updateParticipantLastReadAt($this->activeConversationId, $user->id);
        $this->draft = '';
        $this->invalidateChatComputedState();
        $this->dispatchScrollToBottom();
    }

    public function startDirectConversation(int $userId): void
    {
        $authUser = auth()->user();

        if (! $authUser) {
            return;
        }

        $other = $this->availableContactsQuery($authUser)
            ->whereKey($userId)
            ->first(['id', 'name', 'role', 'phone']);

        if (! $other) {
            return;
        }

        $participantIds = collect([$authUser->id, $other->id])->unique()->values();

        $conversationQuery = ChatConversation::query()
            ->where('type', 'direct')
            ->withCount('participants')
            ->having('participants_count', 2);

        foreach ($participantIds as $participantId) {
            $conversationQuery->whereHas('participants', fn ($query) => $query->where('users.id', $participantId));
        }

        $conversation = $conversationQuery->first();

        if (! $conversation) {
            $conversation = ChatConversation::create([
                'created_by' => $authUser->id,
                'type' => 'direct',
            ]);

            $conversation->participants()->sync([
                $authUser->id => ['is_admin' => true, 'joined_at' => now()],
                $other->id => ['is_admin' => $other->role === 'admin', 'joined_at' => now()],
            ]);
        }

        $this->activeConversationId = $conversation->id;
        $this->invalidateChatComputedState();
        $this->dispatchScrollToBottom();

        if (($this->activeConversation['has_unread'] ?? false) === true) {
            $this->markActiveConversationRead();
            $this->invalidateSidebarComputedState();
        }
    }

    private function markActiveConversationRead(): void
    {
        $user = auth()->user();

        if (! $user || ! $this->activeConversationId) {
            return;
        }

        $this->updateParticipantLastReadAt($this->activeConversationId, $user->id);
    }

    /**
     * @param  array<int, array<string, mixed>>  $directConversations
     * @return array<int, array<string, mixed>>
     */
    private function buildContacts(User $user, array $directConversations): array
    {
        $availableContacts = $this->availableContactsQuery($user)
            ->orderBy('name')
            ->get(['id', 'name', 'role', 'phone']);

        $contacts = $availableContacts
            ->map(function (User $contact) use ($directConversations) {
                $conversation = $directConversations[$contact->id] ?? null;

                return $this->mapContactItem(
                    id: $contact->id,
                    name: $contact->name,
                    role: $contact->role,
                    phone: $contact->phone,
                    conversation: $conversation,
                );
            })
            ->keyBy('id');

        foreach ($directConversations as $participantId => $conversation) {
            if ($contacts->has($participantId) || empty($conversation['other_user'])) {
                continue;
            }

            $otherUser = $conversation['other_user'];

            $contacts->put($participantId, $this->mapContactItem(
                id: $otherUser['id'],
                name: $otherUser['name'],
                role: $otherUser['role'],
                phone: $otherUser['phone'],
                conversation: $conversation,
            ));
        }

        return $contacts
            ->sort(function (array $left, array $right): int {
                $leftHasConversation = filled($left['last_message_at']);
                $rightHasConversation = filled($right['last_message_at']);

                if ($leftHasConversation !== $rightHasConversation) {
                    return $leftHasConversation ? -1 : 1;
                }

                if ($leftHasConversation && $rightHasConversation && $left['last_message_at'] !== $right['last_message_at']) {
                    return strcmp($right['last_message_at'], $left['last_message_at']);
                }

                return strcasecmp($left['name'], $right['name']);
            })
            ->pipe(fn (Collection $items) => $this->filterItemsBySearch($items, function (array $item): string {
                return implode(' ', array_filter([
                    $item['name'] ?? null,
                    $item['role_label'] ?? null,
                    $item['phone'] ?? null,
                    $item['preview'] ?? null,
                ]));
            }))
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>|null  $conversation
     * @return array<string, mixed>
     */
    private function mapContactItem(int $id, string $name, string $role, ?string $phone, ?array $conversation = null): array
    {
        return [
            'id' => $id,
            'name' => $name,
            'role' => $role,
            'role_label' => $this->formatRoleLabel($role),
            'phone' => $phone,
            'initials' => $this->makeInitials($name),
            'conversation_id' => $conversation['id'] ?? null,
            'has_conversation' => filled($conversation['id'] ?? null),
            'last_message_at' => $conversation['last_message_at'] ?? null,
            'activity_label' => $conversation['activity_label'] ?? null,
            'preview' => $conversation['last_message_preview']
                ?? $phone
                ?? $this->formatRoleLabel($role),
            'has_unread' => (bool) ($conversation['has_unread'] ?? false),
        ];
    }

    private function availableContactsQuery(User $user): Builder
    {
        $query = User::query()->whereKeyNot($user->id);

        if ($user->role === 'member') {
            $query->where('role', 'admin');
        } elseif ($user->role === 'service_provider') {
            $query->whereIn('role', ['admin', 'member']);
        }

        return $query;
    }

    private function makeConversationTitle(ChatConversation $conversation, Collection $others): string
    {
        if ($conversation->type === 'direct' && $others->count() === 1) {
            return (string) $others->first()?->name;
        }

        $otherNames = $others
            ->pluck('name')
            ->filter()
            ->values();

        return $conversation->subject
            ?: ($otherNames->isNotEmpty() ? $otherNames->implode(', ') : __('chat.conversation_number', ['id' => $conversation->id]));
    }

    private function makeConversationSubtitle(ChatConversation $conversation, Collection $others, ?User $otherUser): string
    {
        if ($conversation->type === 'direct' && $otherUser) {
            return collect([
                $this->formatRoleLabel($otherUser->role),
                $otherUser->phone,
            ])->filter()->implode(' / ');
        }

        $names = $others
            ->pluck('name')
            ->filter()
            ->take(3)
            ->implode(', ');

        return collect([
            $conversation->subject ? __('chat.group_chat') : null,
            $names ?: null,
        ])->filter()->implode(' / ');
    }

    private function makeMessagePreview(?ChatMessage $message, int $authUserId): ?string
    {
        if (! $message || blank($message->body)) {
            return null;
        }

        $body = preg_replace('/\s+/u', ' ', trim($message->body));
        $preview = Str::limit((string) $body, 90);

        if ($message->sender_id === $authUserId) {
            return __('chat.you_message_prefix', ['message' => $preview]);
        }

        return $preview;
    }

    private function makeInitials(string $value): string
    {
        $parts = collect(preg_split('/\s+/u', trim($value)) ?: [])
            ->filter()
            ->take(2)
            ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)));

        return $parts->isNotEmpty() ? $parts->implode('') : 'C';
    }

    private function formatRoleLabel(?string $role): string
    {
        return match ($role) {
            'admin' => __('Admin'),
            'member' => __('Member'),
            'service_provider' => __('Service provider'),
            default => __('User'),
        };
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $items
     * @param  callable(array<string, mixed>): string  $resolver
     * @return Collection<int, array<string, mixed>>
     */
    private function filterItemsBySearch(Collection $items, callable $resolver): Collection
    {
        $search = Str::lower(trim($this->search));

        if ($search === '') {
            return $items;
        }

        return $items->filter(function (array $item) use ($resolver, $search): bool {
            $haystack = Str::lower($resolver($item));

            return Str::contains($haystack, $search);
        })->values();
    }

    #[Computed]
    public function sidebarData(): array
    {
        $user = auth()->user();

        if (! $user) {
            return $this->emptySidebarData();
        }

        $items = ChatConversation::query()
            ->whereHas('participants', fn ($query) => $query->where('users.id', $user->id))
            ->with([
                'participants:id,name,role,phone',
                'latestMessage' => fn ($query) => $query->with('sender:id,name,role'),
            ])
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at')
            ->get();

        $directConversations = [];

        $mappedItems = $items->map(function (ChatConversation $conversation) use ($user, &$directConversations) {
            $participants = $conversation->participants->values();
            $others = $participants->where('id', '!=', $user->id)->values();
            $otherUser = $conversation->type === 'direct' && $others->count() === 1 ? $others->first() : null;
            $lastMessage = $conversation->latestMessage;
            $lastReadAt = $participants->firstWhere('id', $user->id)?->pivot?->last_read_at;
            $title = $this->makeConversationTitle($conversation, $others);

            $item = [
                'id' => $conversation->id,
                'title' => $title,
                'subtitle' => $this->makeConversationSubtitle($conversation, $others, $otherUser),
                'type' => $conversation->type,
                'participants_count' => $participants->count(),
                'other_user' => $otherUser ? [
                    'id' => $otherUser->id,
                    'name' => $otherUser->name,
                    'role' => $otherUser->role,
                    'role_label' => $this->formatRoleLabel($otherUser->role),
                    'phone' => $otherUser->phone,
                ] : null,
                'last_message_at' => optional($conversation->last_message_at)->toDateTimeString(),
                'activity_label' => $conversation->last_message_at?->locale(app()->getLocale())->diffForHumans(),
                'last_message_preview' => $this->makeMessagePreview($lastMessage, $user->id),
                'has_unread' => $lastMessage !== null
                    && $lastMessage->sender_id !== $user->id
                    && (blank($lastReadAt) || $lastMessage->created_at?->gt($lastReadAt)),
                'initials' => $this->makeInitials($title),
            ];

            if ($otherUser) {
                $directConversations[$otherUser->id] = $item;
            }

            return $item;
        })->values();

        $filteredConversationItems = $this->filterItemsBySearch($mappedItems, function (array $item): string {
            return implode(' ', array_filter([
                $item['title'] ?? null,
                $item['subtitle'] ?? null,
                $item['last_message_preview'] ?? null,
            ]));
        });

        return [
            'conversations' => $mappedItems->all(),
            'contacts' => $this->buildContacts($user, $directConversations),
            'group_conversations' => $filteredConversationItems
                ->where('type', '!=', 'direct')
                ->values()
                ->all(),
            'active_conversation' => $mappedItems->firstWhere('id', $this->activeConversationId) ?? [],
        ];
    }

    #[Computed]
    public function contacts(): array
    {
        return $this->sidebarData['contacts'];
    }

    #[Computed]
    public function groupConversations(): array
    {
        return $this->sidebarData['group_conversations'];
    }

    #[Computed]
    public function activeConversation(): array
    {
        return $this->sidebarData['active_conversation'];
    }

    #[Computed]
    public function messages(): array
    {
        $user = auth()->user();

        if (! $user || ! $this->activeConversationId) {
            return [];
        }

        if (! $this->userParticipatesInConversation($this->activeConversationId, $user->id)) {
            return [];
        }

        return ChatMessage::query()
            ->where('chat_conversation_id', $this->activeConversationId)
            ->with('sender:id,name,role')
            ->latest()
            ->limit(50)
            ->get()
            ->reverse()
            ->values()
            ->map(fn (ChatMessage $message) => [
                'id' => $message->id,
                'body' => $message->body,
                'sender_id' => $message->sender_id,
                'sender_name' => $message->sender?->name,
                'created_at' => optional($message->created_at)->toDateTimeString(),
                'time_label' => $message->created_at?->locale(app()->getLocale())->translatedFormat('H:i'),
                'is_mine' => $message->sender_id === $user->id,
            ])->all();
    }

    private function updateParticipantLastReadAt(int $conversationId, int $userId): void
    {
        DB::table('chat_conversation_participants')
            ->where('chat_conversation_id', $conversationId)
            ->where('user_id', $userId)
            ->update([
                'last_read_at' => now(),
                'updated_at' => now(),
            ]);
    }

    private function userParticipatesInConversation(int $conversationId, ?int $userId = null): bool
    {
        $resolvedUserId = $userId ?? auth()->id();

        if (! $resolvedUserId) {
            return false;
        }

        return DB::table('chat_conversation_participants')
            ->where('chat_conversation_id', $conversationId)
            ->where('user_id', $resolvedUserId)
            ->exists();
    }

    private function ensureActiveConversationStillExists(): void
    {
        if (! $this->activeConversationId) {
            return;
        }

        if ($this->activeConversation === []) {
            $this->activeConversationId = null;
            $this->draft = '';
            $this->invalidateChatComputedState();
        }
    }

    /**
     * @return array<string, array<int, array<string, mixed>>|array<string, mixed>>
     */
    private function emptySidebarData(): array
    {
        return [
            'conversations' => [],
            'contacts' => [],
            'group_conversations' => [],
            'active_conversation' => [],
        ];
    }

    private function invalidateSidebarComputedState(): void
    {
        unset($this->sidebarData);
        unset($this->contacts);
        unset($this->groupConversations);
        unset($this->activeConversation);
    }

    private function invalidateChatComputedState(): void
    {
        $this->invalidateSidebarComputedState();
        unset($this->messages);
    }

    private function dispatchScrollToBottom(): void
    {
        $this->dispatch('chat-scroll-to-bottom');
    }
}
