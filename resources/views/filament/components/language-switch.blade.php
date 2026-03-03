<div class="px-3" style="margin-bottom: 0.75rem; text-align: end;">
    @if (app()->getLocale() === 'ar')
        <a href="{{ route('language.switch', 'en') }}" class="text-sm font-medium text-gray-700 dark:text-gray-200">
            <span>{{ __('dashboard.languages.english') }}</span>
        </a>
    @else
        <a href="{{ route('language.switch', 'ar') }}" class="text-sm font-medium text-gray-700 dark:text-gray-200">
            <span>{{ __('dashboard.languages.arabic') }}</span>
        </a>
    @endif
</div>
