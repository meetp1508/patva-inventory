@props(['title', 'back' => null])

<div class="flex justify-between items-center">
    <div class="flex items-center">
        @if ($back)
            <a href="{{ $back }}" class="text-gray-500 hover:text-gray-700 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
        @endif
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $title }}</h2>
    </div>

    @isset($action)
        <div>{{ $action }}</div>
    @endisset
</div>
