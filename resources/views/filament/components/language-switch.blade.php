<div class="flex items-center gap-x-3 px-3">
    @if(app()->getLocale() == 'ar')
        <a href="{{ route('language.switch', 'en') }}" class="flex items-center gap-x-2 text-sm font-medium text-gray-700 dark:text-gray-200">
            <x-heroicon-m-globe-alt class="h-5 w-5 text-gray-500" />
            <span>English</span>
        </a>
    @else
        <a href="{{ route('language.switch', 'ar') }}" class="flex items-center gap-x-2 text-sm font-medium text-gray-700 dark:text-gray-200">
            <x-heroicon-m-globe-alt class="h-5 w-5 text-gray-500" />
            <span>العربية</span>
        </a>
    @endif
</div>
