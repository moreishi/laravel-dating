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
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-rose-100 flex items-center justify-center">
                                <span class="text-lg font-semibold text-rose-600">{{ strtoupper(substr($profile->name, 0, 1)) }}</span>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-lg font-semibold text-gray-900 hover:text-rose-600">{{ $profile->name }}</h3>
                                <p class="text-gray-600 text-sm mt-0.5">
                                    {{ $profile->age ?? 'N/A' }} years old
                                    @if ($profile->gender)
                                        &middot; {{ ucfirst($profile->gender) }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        @if ($profile->bio)
                            <p class="text-gray-700 mt-4 line-clamp-3">{{ $profile->bio }}</p>
                        @endif
                        <div class="mt-4">
                            <span class="inline-flex items-center px-4 py-2 bg-rose-600 text-white rounded-md text-sm font-medium">
                                {{ __('View Profile') }}
                            </span>
                        </div>
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
