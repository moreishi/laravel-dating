<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900 leading-tight">
            {{ __('Messages') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow divide-y divide-gray-200">
                @forelse ($conversations as $conversation)
                    @php $otherUser = $conversation->otherUser(auth()->user()); @endphp
                    <a href="{{ route('conversations.show', $conversation->id) }}" class="flex items-center p-4 hover:bg-gray-50 transition">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    {{ $otherUser?->name ?? 'Unknown User' }}
                                </p>
                                @if ($conversation->hasUnreadMessagesFor(auth()->id()))
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-700">
                                        {{ __('New') }}
                                    </span>
                                @endif
                            </div>
                            @php $lastMessage = $conversation->messages->first(); @endphp
                            @if ($lastMessage)
                                <p class="text-sm text-gray-500 truncate mt-1">
                                    {{ $lastMessage->content }}
                                </p>
                            @endif
                        </div>
                        <div class="ml-4 text-xs text-gray-400">
                            {{ $conversation->updated_at->diffForHumans() }}
                        </div>
                    </a>
                @empty
                    <div class="text-center text-gray-500 py-12">
                        {{ __('No conversations yet.') }}
                        <div class="mt-2">
                            <a href="{{ route('profiles.index') }}" class="text-rose-500 hover:text-rose-600 font-medium">
                                {{ __('Browse profiles to start chatting') }}
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
