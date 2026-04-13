<x-filament-panels::page class="chat-messenger-page" full-height>
    @php
        $isArabic = app()->getLocale() === 'ar';
        $sidebarCount = count($this->contacts) + count($this->groupConversations);
    @endphp

    @once
        <style>
            .chat-messenger-page .fi-page-header-main-ctn {
                padding-top: 0 !important;
                padding-bottom: 0 !important;
                gap: 0 !important;
            }

            .chat-messenger-page .fi-page-content {
                display: block !important;
                gap: 0 !important;
            }

            .chat-messenger-page .chat-messenger-shell {
                min-height: calc(100vh - 5.5rem);
            }

            .chat-messenger-page .chat-messages-area {
                background-image:
                    radial-gradient(circle at top, rgba(59, 130, 246, 0.08), transparent 28rem),
                    linear-gradient(180deg, rgba(248, 250, 252, 0.92), rgba(255, 255, 255, 1));
            }

            .dark .chat-messenger-page .chat-messages-area {
                background-image:
                    radial-gradient(circle at top, rgba(59, 130, 246, 0.12), transparent 28rem),
                    linear-gradient(180deg, rgba(10, 15, 25, 0.96), rgba(17, 24, 39, 1));
            }

            @media (max-width: 1023px) {
                .chat-messenger-page .chat-messenger-shell {
                    min-height: calc(100vh - 5rem);
                }
            }
        </style>
    @endonce

    <div
        class="chat-messenger-shell overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900"
        dir="{{ $isArabic ? 'rtl' : 'ltr' }}"
    >
        <div class="grid h-full min-h-full grid-cols-12">
            <aside
                class="col-span-12 flex min-h-full flex-col border-b border-gray-200 bg-[#f7f9fc] dark:border-gray-800 dark:bg-[#0f1724] lg:col-span-4 lg:border-b-0 {{ $isArabic ? 'lg:order-last lg:border-l' : 'lg:border-r' }}"
            >
                <div class="border-b border-gray-200 bg-white/90 px-5 py-5 backdrop-blur dark:border-gray-800 dark:bg-gray-900/90">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white">
                                {{ __('chat.messages_heading') }}
                            </div>
                            <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ __('chat.messenger_intro') }}
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <div class="flex h-10 min-w-10 items-center justify-center rounded-2xl bg-primary-50 px-3 text-sm font-semibold text-primary-700 dark:bg-primary-900/40 dark:text-primary-200">
                                {{ $sidebarCount }}
                            </div>

                            <button
                                type="button"
                                class="rounded-2xl border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-600 transition hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800"
                                wire:click="refreshSidebar"
                            >
                                {{ __('Refresh') }}
                            </button>
                        </div>
                    </div>

                    <div class="mt-4">
                        <input
                            type="text"
                            class="fi-input w-full rounded-2xl border-none bg-gray-100 px-4 py-3 shadow-none ring-0 focus:bg-white dark:bg-gray-800 dark:focus:bg-gray-900"
                            placeholder="{{ __('chat.search_placeholder') }}"
                            wire:model.live.debounce.300ms="search"
                        />
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto px-3 py-3" style="max-height: calc(100vh - 14rem);">
                    <div class="mb-3 px-2 text-xs font-semibold uppercase tracking-wider text-gray-400">
                        {{ __('chat.people_section') }}
                    </div>

                    <div class="space-y-1.5">
                        @forelse ($this->contacts as $contact)
                            @php
                                $isActive = (int) ($contact['conversation_id'] ?? 0) === (int) ($this->activeConversationId ?? 0);
                            @endphp

                            <button
                                type="button"
                                wire:click="openConversationWithUser({{ $contact['id'] }})"
                                wire:key="contact-{{ $contact['id'] }}-{{ $contact['conversation_id'] ?? 'new' }}"
                                class="flex w-full items-center gap-3 rounded-2xl border px-3 py-3 transition {{ $isArabic ? 'text-right' : 'text-left' }} {{ $isActive ? 'border-primary-200 bg-white shadow-sm dark:border-primary-800 dark:bg-gray-900' : 'border-transparent hover:border-gray-200 hover:bg-white dark:hover:border-gray-800 dark:hover:bg-gray-900/70' }}"
                            >
                                <div class="relative shrink-0">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-primary-100 to-sky-100 text-sm font-bold text-primary-700 dark:from-primary-900/50 dark:to-sky-900/40 dark:text-primary-200">
                                        {{ $contact['initials'] }}
                                    </div>

                                    <span class="absolute bottom-0 {{ $isArabic ? '-left-0.5' : '-right-0.5' }} h-3.5 w-3.5 rounded-full border-2 border-[#f7f9fc] bg-emerald-500 dark:border-[#0f1724]"></span>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="truncate text-sm font-semibold text-gray-950 dark:text-white">
                                                {{ $contact['name'] }}
                                            </div>
                                            <div class="mt-1 truncate text-xs text-gray-500 dark:text-gray-400">
                                                {{ $contact['preview'] }}
                                            </div>
                                        </div>

                                        <div class="shrink-0 text-xs text-gray-400">
                                            {{ $contact['activity_label'] ?: $contact['role_label'] }}
                                        </div>
                                    </div>
                                </div>

                                @if ($contact['has_unread'])
                                    <div class="h-2.5 w-2.5 shrink-0 rounded-full bg-primary-600"></div>
                                @endif
                            </button>
                        @empty
                            <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-4 py-6 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
                                {{ __('chat.no_matching_contacts') }}
                            </div>
                        @endforelse
                    </div>

                    @if (count($this->groupConversations))
                        <div class="mb-3 mt-6 px-2 text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ __('chat.other_conversations') }}
                        </div>

                        <div class="space-y-1.5">
                            @foreach ($this->groupConversations as $conversation)
                                @php
                                    $isActiveGroup = (int) ($conversation['id'] ?? 0) === (int) ($this->activeConversationId ?? 0);
                                @endphp

                                <button
                                    type="button"
                                    wire:click="selectConversation({{ $conversation['id'] }})"
                                    wire:key="conversation-{{ $conversation['id'] }}"
                                    class="flex w-full items-center gap-3 rounded-2xl border px-3 py-3 transition {{ $isArabic ? 'text-right' : 'text-left' }} {{ $isActiveGroup ? 'border-primary-200 bg-white shadow-sm dark:border-primary-800 dark:bg-gray-900' : 'border-transparent hover:border-gray-200 hover:bg-white dark:hover:border-gray-800 dark:hover:bg-gray-900/70' }}"
                                >
                                    <div class="relative shrink-0">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-200 text-sm font-bold text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                            {{ $conversation['initials'] }}
                                        </div>
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <div class="truncate text-sm font-semibold text-gray-950 dark:text-white">
                                                    {{ $conversation['title'] }}
                                                </div>
                                                <div class="mt-1 truncate text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $conversation['last_message_preview'] ?: $conversation['subtitle'] }}
                                                </div>
                                            </div>

                                            <div class="shrink-0 text-xs text-gray-400">
                                                {{ $conversation['activity_label'] }}
                                            </div>
                                        </div>
                                    </div>

                                    @if ($conversation['has_unread'])
                                        <div class="h-2.5 w-2.5 shrink-0 rounded-full bg-primary-600"></div>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </aside>

            <section
                class="chat-messages-area col-span-12 flex min-h-full flex-col lg:col-span-8 {{ $isArabic ? 'lg:order-first' : '' }}"
                x-data="{
                    pendingMessages: [],
                    queuePendingMessage() {
                        const body = ($wire.draft ?? '').trim();

                        if (! body || ! $wire.activeConversationId) {
                            return;
                        }

                        this.pendingMessages.push({
                            id: `pending-${Date.now()}-${Math.random().toString(16).slice(2)}`,
                            body,
                            timeLabel: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                        });

                        this.scrollToBottom();
                    },
                    clearPendingMessages() {
                        this.pendingMessages = [];
                    },
                    scrollToBottom() {
                        if (! this.$refs.messagesPanel) {
                            return;
                        }

                        requestAnimationFrame(() => {
                            requestAnimationFrame(() => {
                                this.$refs.messagesPanel.scrollTop = this.$refs.messagesPanel.scrollHeight;
                            });
                        });
                    },
                }"
                x-init="$nextTick(() => scrollToBottom())"
                x-on:chat-scroll-to-bottom.window="clearPendingMessages(); scrollToBottom()"
            >
                @if ($this->activeConversationId && !empty($this->activeConversation))
                    <div class="border-b border-gray-200 bg-white/85 px-5 py-4 backdrop-blur dark:border-gray-800 dark:bg-gray-900/85">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex min-w-0 items-center gap-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-primary-100 to-sky-100 text-sm font-bold text-primary-700 dark:from-primary-900/50 dark:to-sky-900/40 dark:text-primary-200">
                                    {{ $this->activeConversation['initials'] }}
                                </div>

                                <div class="min-w-0">
                                    <div class="truncate text-lg font-bold text-gray-950 dark:text-white">
                                        {{ $this->activeConversation['title'] }}
                                    </div>
                                    <div class="mt-0.5 truncate text-sm text-gray-500 dark:text-gray-400">
                                        {{ $this->activeConversation['subtitle'] ?: __('chat.direct_conversation') }}
                                    </div>
                                </div>
                            </div>

                            <div class="hidden rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-300 md:block">
                                {{ __('chat.available_now') }}
                            </div>
                        </div>
                    </div>
                @else
                    <div class="border-b border-gray-200 bg-white/85 px-5 py-4 backdrop-blur dark:border-gray-800 dark:bg-gray-900/85">
                        <div class="text-lg font-bold text-gray-950 dark:text-white">
                            {{ __('chat.choose_from_list') }}
                        </div>
                        <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ __('chat.conversation_preview_hint') }}
                        </div>
                    </div>
                @endif

                <div
                    class="flex-1"
                    wire:key="messages-panel-{{ $this->activeConversationId ?? 'none' }}-{{ count($this->messages) }}"
                >
                    <div
                        class="h-full overflow-y-auto px-4 py-6 md:px-6"
                        x-ref="messagesPanel"
                        style="max-height: calc(100vh - 17rem); min-height: 26rem;"
                    >
                        @if (!$this->activeConversationId)
                            <div class="flex h-full min-h-96 items-center justify-center">
                                <div class="max-w-md text-center">
                                    <div class="mx-auto flex h-24 w-24 items-center justify-center rounded-3xl bg-white text-3xl font-bold text-primary-700 shadow-sm dark:bg-gray-900 dark:text-primary-200">
                                        {{ $isArabic ? 'ش' : 'C' }}
                                    </div>
                                    <div class="mt-6 text-2xl font-bold tracking-tight text-gray-950 dark:text-white">
                                        {{ __('chat.start_from_sidebar') }}
                                    </div>
                                    <div class="mt-3 text-sm leading-7 text-gray-500 dark:text-gray-400">
                                        {{ __('chat.start_from_sidebar_description') }}
                                    </div>
                                </div>
                            </div>
                        @elseif (count($this->messages))
                            <div class="space-y-3">
                                @foreach ($this->messages as $message)
                                    <div class="flex {{ $message['is_mine'] ? 'justify-end' : 'justify-start' }}" wire:key="message-{{ $message['id'] }}">
                                        <div class="max-w-xl rounded-3xl px-4 py-3 shadow-sm {{ $message['is_mine'] ? 'rounded-br-md bg-primary-600 text-white' : 'rounded-bl-md border border-white/60 bg-white text-gray-900 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-100' }}">
                                            @if (!$message['is_mine'])
                                                <div class="mb-1 text-xs font-semibold uppercase tracking-wide opacity-70">
                                                    {{ $message['sender_name'] ?? __('chat.unknown_sender') }}
                                                </div>
                                            @endif

                                            <div class="whitespace-pre-line text-sm leading-7" dir="auto">
                                                {{ $message['body'] }}
                                            </div>

                                            <div class="mt-2 text-xs {{ $message['is_mine'] ? 'text-white/70' : 'text-gray-500 dark:text-gray-400' }}">
                                                {{ $message['time_label'] }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                <template x-for="message in pendingMessages" :key="message.id">
                                    <div class="flex justify-end">
                                        <div class="max-w-xl rounded-3xl rounded-br-md bg-primary-600 px-4 py-3 text-white opacity-75 shadow-sm">
                                            <div class="whitespace-pre-line text-sm leading-7" dir="auto" x-text="message.body"></div>

                                            <div class="mt-2 text-xs text-white/70" x-text="message.timeLabel"></div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        @else
                            <div class="flex h-full min-h-96 items-center justify-center" x-show="pendingMessages.length === 0">
                                <div class="max-w-sm rounded-3xl border border-dashed border-gray-300 bg-white/80 px-6 py-8 text-center shadow-sm dark:border-gray-700 dark:bg-gray-900/70">
                                    <div class="text-lg font-bold text-gray-950 dark:text-white">
                                        {{ __('chat.no_messages_yet') }}
                                    </div>
                                    <div class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">
                                        {{ __('chat.no_messages_description') }}
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-3" x-show="pendingMessages.length > 0" x-cloak>
                                <template x-for="message in pendingMessages" :key="message.id">
                                    <div class="flex justify-end">
                                        <div class="max-w-xl rounded-3xl rounded-br-md bg-primary-600 px-4 py-3 text-white opacity-75 shadow-sm">
                                            <div class="whitespace-pre-line text-sm leading-7" dir="auto" x-text="message.body"></div>

                                            <div class="mt-2 text-xs text-white/70" x-text="message.timeLabel"></div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="border-t border-gray-200 bg-white/90 px-4 py-4 backdrop-blur dark:border-gray-800 dark:bg-gray-900/90 md:px-6">
                    <form class="flex items-center gap-3" wire:submit.prevent="send" x-on:submit="queuePendingMessage()">
                        <div class="flex flex-1 items-center rounded-3xl border border-gray-200 bg-white px-3 py-2 shadow-sm dark:border-gray-700 dark:bg-gray-950">
                            <input
                                type="text"
                                class="fi-input w-full border-none bg-transparent px-2 shadow-none ring-0 focus:ring-0"
                                placeholder="{{ __('chat.message_placeholder') }}"
                                wire:model.defer="draft"
                                @disabled(!$this->activeConversationId)
                            />
                        </div>

                        <button
                            type="submit"
                            class="inline-flex h-12 shrink-0 items-center justify-center rounded-2xl bg-primary-600 px-5 text-sm font-bold text-white transition hover:bg-primary-500 disabled:cursor-not-allowed disabled:opacity-60"
                            @disabled(!$this->activeConversationId)
                        >
                            {{ __('Send') }}
                        </button>
                    </form>

                    <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        {{ __('chat.enter_to_send_hint') }}
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-filament-panels::page>
