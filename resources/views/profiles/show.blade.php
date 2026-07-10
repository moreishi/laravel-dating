<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900 leading-tight">
            {{ $profile->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900">{{ $profile->name }}</h3>
                        <p class="text-gray-600 mt-1">
                            {{ $profile->age ?? 'N/A' }} years old
                            @if ($profile->gender)
                                &middot; {{ ucfirst($profile->gender) }}
                            @endif
                        </p>
                    </div>
                </div>

                @if ($profile->bio)
                    <div class="mt-6">
                        <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wide">{{ __('About') }}</h4>
                        <p class="mt-2 text-gray-700 whitespace-pre-wrap">{{ $profile->bio }}</p>
                    </div>
                @endif

                <div class="mt-8 flex gap-4">
                    <form action="{{ route('conversations.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="recipient_id" value="{{ $profile->id }}">
                        <button type="submit" class="inline-flex items-center px-6 py-3 bg-rose-500 text-white rounded-md hover:bg-rose-600 font-medium">
                            {{ __('Send Message') }}
                        </button>
                    </form>

                    <a href="{{ route('profiles.index') }}" class="inline-flex items-center px-6 py-3 bg-white text-gray-700 rounded-md border border-gray-300 hover:bg-gray-50 font-medium">
                        {{ __('Back to Profiles') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
