<a
    href="{{ $chatUrl }}"
    class="fi-topbar-chat-shortcut"
    title="{{ __('chat.navigation_label') }}"
>
    <span class="fi-topbar-chat-shortcut__icon">
        <x-filament::icon
            icon="heroicon-o-chat-bubble-left-right"
            class="h-5 w-5"
        />

        @if ($unreadCount > 0)
            <span class="fi-topbar-chat-shortcut__badge">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </span>

    <span class="fi-topbar-chat-shortcut__label">
        {{ __('chat.navigation_label') }}
    </span>
</a>
