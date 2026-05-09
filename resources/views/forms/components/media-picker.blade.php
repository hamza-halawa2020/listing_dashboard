<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    @php
        $statePath    = $getStatePath();
        $currentValue = $getState();
        $previewUrl   = $getImageUrl($currentValue);
        $saveFormat   = $getSaveFormat();
        $libraryUrl   = route('media.library');
        $uploadUrl    = route('media.upload');
    @endphp

    <div
        x-data="{
            open: false,
            selected: @js($currentValue),
            selectedUrl: @js($previewUrl),
            uploading: false,
            dragOver: false,
            images: [],
            search: '',
            page: 1,
            lastPage: 1,
            loading: false,
            searchTimer: null,

            init() {
                this.$watch('open', v => { if (v) this.loadImages(true); });
                this.$watch('search', () => {
                    clearTimeout(this.searchTimer);
                    this.searchTimer = setTimeout(() => this.loadImages(true), 350);
                });
            },

            async loadImages(reset = false) {
                if (this.loading) return;
                if (!reset && this.page > this.lastPage) return;
                if (reset) { this.images = []; this.page = 1; }
                this.loading = true;
                const params = new URLSearchParams({ page: this.page, format: '{{ $saveFormat }}', search: this.search });
                const res  = await fetch('{{ $libraryUrl }}?' + params);
                const data = await res.json();
                this.images   = reset ? data.data : [...this.images, ...data.data];
                this.lastPage = data.lastPage;
                this.page     = data.page + 1;
                this.loading  = false;
            },

            selectImage(saveValue, url) {
                this.selected    = saveValue;
                this.selectedUrl = url;
                $wire.set('{{ $statePath }}', saveValue, false);
                this.open = false;
            },

            clearSelection() {
                this.selected    = null;
                this.selectedUrl = null;
                $wire.set('{{ $statePath }}', null, false);
            },

            handleDrop(event) {
                this.dragOver = false;
                const file = event.dataTransfer?.files?.[0];
                if (file && file.type.startsWith('image/')) this.uploadFile(file);
            },

            handleFileInput(event) {
                const file = event.target.files[0];
                if (file) this.uploadFile(file);
                event.target.value = '';
            },

            async uploadFile(file) {
                this.uploading = true;
                const formData = new FormData();
                formData.append('file', file);
                formData.append('format', '{{ $saveFormat }}');
                try {
                    const res  = await fetch('{{ $uploadUrl }}', { method: 'POST', body: formData });
                    const data = await res.json();
                    this.uploading = false;
                    if (data.saveValue) {
                        this.images.unshift({ saveValue: data.saveValue, url: data.url, name: data.path });
                        this.selected    = data.saveValue;
                        this.selectedUrl = data.url;
                        // false = don't re-render the component, just update the state
                        $wire.set('{{ $statePath }}', data.saveValue, false);
                    }
                } catch(e) {
                    this.uploading = false;
                    alert('{{ __('Upload failed') }}');
                }
            },

            onScroll(el) {
                if (el.scrollTop + el.clientHeight >= el.scrollHeight - 60) this.loadImages(false);
            }
        }"
        class="space-y-2"
    >
        {{-- Field area --}}
        <div
            @dragover.prevent="dragOver = true"
            @dragleave.prevent="dragOver = false"
            @drop.prevent="handleDrop($event)"
            :class="dragOver ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 scale-[1.01]' : 'border-gray-200 dark:border-gray-700 hover:border-primary-400 dark:hover:border-primary-500'"
            class="rounded-2xl border-2 border-dashed transition-all duration-200"
        >
            {{-- No image selected --}}
            <div x-show="!selected" class="flex flex-col items-center gap-4 py-10 px-6">
                <div x-show="!uploading" class="flex flex-col items-center gap-4 w-full">
                    {{-- Icon --}}
                    <div class="w-14 h-14 rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                        <x-heroicon-o-photo class="w-7 h-7 text-gray-400 dark:text-gray-500" />
                    </div>
                    <div class="text-center">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('Drop your image here') }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ __('PNG, JPG, GIF, WEBP supported') }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" @click.prevent="open = true"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white text-sm font-medium shadow-sm transition-colors">
                            <x-heroicon-o-squares-2x2 class="w-4 h-4" />
                            {{ __('Choose from Library') }}
                        </button>
                        <button type="button" @click.prevent="$refs.fileInput.click()"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium shadow-sm border border-gray-200 dark:border-gray-600 transition-colors">
                            <x-heroicon-o-arrow-up-tray class="w-4 h-4" />
                            {{ __('Upload New') }}
                        </button>
                    </div>
                </div>
                <div x-show="uploading" class="flex flex-col items-center gap-3 py-2">
                    <div class="w-12 h-12 rounded-full bg-primary-50 dark:bg-primary-900/30 flex items-center justify-center">
                        <svg class="animate-spin h-6 w-6 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-primary-600">{{ __('Uploading...') }}</span>
                </div>
            </div>

            {{-- Image selected --}}
            <div x-show="selected && selectedUrl" class="flex items-center gap-4 p-4">
                <div class="relative flex-shrink-0">
                    <img :src="selectedUrl" class="h-24 w-24 rounded-xl object-cover shadow ring-2 ring-primary-500/30" />
                    <div class="absolute -top-1.5 -right-1.5 bg-primary-500 rounded-full p-0.5 shadow-md">
                        <x-heroicon-s-check class="w-3 h-3 text-white" />
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ __('Image selected') }}</p>
                    <p class="text-xs text-gray-400 truncate mt-0.5" x-text="selected"></p>
                    <div class="flex items-center gap-2 mt-3">
                        <button type="button" @click.prevent="open = true"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-medium transition-colors">
                            <x-heroicon-o-arrow-path class="w-3.5 h-3.5" />
                            {{ __('Change') }}
                        </button>
                        <button type="button" @click.prevent="clearSelection()"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/40 text-red-600 dark:text-red-400 text-xs font-medium transition-colors">
                            <x-heroicon-o-trash class="w-3.5 h-3.5" />
                            {{ __('Remove') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <input type="file" x-ref="fileInput" accept="image/*" class="hidden" @change="handleFileInput($event)" />

        {{-- Modal overlay — inside same x-data scope --}}
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @keydown.escape.window="open = false"
            class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
            style="display:none"
        >
            <div
                @click.outside="open = false"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="relative w-full max-w-4xl bg-white dark:bg-gray-900 rounded-2xl shadow-2xl flex flex-col overflow-hidden"
                style="max-height:88vh"
            >
                {{-- Header --}}
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-800">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-primary-100 dark:bg-primary-900/40 flex items-center justify-center">
                            <x-heroicon-o-photo class="w-4 h-4 text-primary-600 dark:text-primary-400" />
                        </div>
                        <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">{{ __('Media Library') }}</h3>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" @click.prevent="$refs.fileInputModal.click()" :disabled="uploading"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-primary-600 hover:bg-primary-700 disabled:opacity-50 text-white text-xs font-medium transition-colors shadow-sm">
                            <x-heroicon-o-arrow-up-tray class="w-3.5 h-3.5" />
                            <span x-text="uploading ? '{{ __('Uploading...') }}' : '{{ __('Upload') }}'"></span>
                        </button>
                        <input type="file" x-ref="fileInputModal" accept="image/*" class="hidden" @change="handleFileInput($event)" />
                        <button type="button" @click.prevent="open = false"
                            class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                            <x-heroicon-o-x-mark class="w-5 h-5" />
                        </button>
                    </div>
                </div>

                {{-- Grid --}}
                <div class="flex-1 overflow-y-auto p-5" @scroll="onScroll($el)">
                    <div x-show="!loading && images.length === 0"
                        class="flex flex-col items-center justify-center gap-3 py-20 text-gray-400">
                        <div class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                            <x-heroicon-o-photo class="w-8 h-8 opacity-40" />
                        </div>
                        <p class="text-sm font-medium">{{ __('No images found') }}</p>
                        <p class="text-xs text-gray-300 dark:text-gray-600">{{ __('Upload a new image to get started') }}</p>
                    </div>

                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
                        <template x-for="img in images" :key="img.saveValue">
                            <button type="button" @click.prevent="selectImage(img.saveValue, img.url)"
                                class="group flex flex-col rounded-xl overflow-hidden focus:outline-none transition-all duration-150 bg-white dark:bg-gray-800 border"
                                :class="selected === img.saveValue
                                    ? 'border-primary-500 ring-2 ring-primary-500/40 shadow-lg'
                                    : 'border-gray-200 dark:border-gray-700 hover:border-primary-400 hover:shadow-md'">
                                {{-- Image --}}
                                <div class="relative w-full aspect-square overflow-hidden bg-gray-100 dark:bg-gray-700">
                                    <img :src="img.url" :alt="img.name" class="w-full h-full object-cover transition-transform duration-200 group-hover:scale-105" loading="lazy" />
                                    {{-- Hover overlay --}}
                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors"></div>
                                    {{-- Selected overlay --}}
                                    <div x-show="selected === img.saveValue"
                                        class="absolute inset-0 bg-primary-500/15 flex items-center justify-center">
                                        <div class="bg-primary-500 rounded-full p-1 shadow-lg">
                                            <x-heroicon-s-check class="w-4 h-4 text-white" />
                                        </div>
                                    </div>
                                </div>
                                {{-- Name --}}
                                <div class="px-2 py-1.5 w-full"
                                    :class="selected === img.saveValue ? 'bg-primary-50 dark:bg-primary-900/30' : 'bg-white dark:bg-gray-800'">
                                    <p class="text-[11px] truncate text-center font-medium"
                                        :class="selected === img.saveValue ? 'text-primary-600 dark:text-primary-400' : 'text-gray-500 dark:text-gray-400'"
                                        x-text="img.name"></p>
                                </div>
                            </button>
                        </template>

                        <template x-if="loading">
                            <template x-for="i in [1,2,3,4,5,6,7,8,9,10,11,12]" :key="i">
                                <div class="rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700">
                                    <div class="aspect-square bg-gray-100 dark:bg-gray-800 animate-pulse"></div>
                                    <div class="px-2 py-1.5 bg-white dark:bg-gray-800">
                                        <div class="h-2.5 bg-gray-100 dark:bg-gray-700 rounded animate-pulse mx-auto w-3/4"></div>
                                    </div>
                                </div>
                            </template>
                        </template>
                    </div>

                    <div x-show="!loading && page <= lastPage" class="flex justify-center pt-5">
                        <button type="button" @click.prevent="loadImages(false)"
                            class="inline-flex items-center gap-2 text-xs text-primary-600 hover:text-primary-700 font-medium px-5 py-2 rounded-lg border border-primary-200 dark:border-primary-800 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors">
                            <x-heroicon-o-arrow-down class="w-3.5 h-3.5" />
                            {{ __('Load more') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dynamic-component>
