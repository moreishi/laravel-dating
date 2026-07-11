<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900 leading-tight">
            {{ __('Browse Profiles') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse ($profiles as $profile)
                    <a href="{{ route('profiles.show', $profile->id) }}" class="block bg-white rounded-lg shadow p-6 hover:shadow-md hover:bg-gray-50 transition">
                        <h3 class="text-lg font-semibold text-gray-900 hover:text-rose-600">{{ $profile->name }}</h3>
                        <p class="text-gray-600 text-sm mt-1">
                            {{ $profile->age ?? 'N/A' }} years old
                            @if ($profile->gender)
                                &middot; {{ ucfirst($profile->gender) }}
                            @endif
                        </p>
                        @if ($profile->bio)
                            <p class="text-gray-700 mt-3 line-clamp-3">{{ $profile->bio }}</p>
                        @endif
                    </a>
                @empty
                    <div class="col-span-full text-center text-gray-500 py-12">
                        {{ __('No profiles found.') }}
                    </div>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $profiles->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
