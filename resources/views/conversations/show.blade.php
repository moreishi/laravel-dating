<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-900 leading-tight">
                {{ $conversation->otherUser(auth()->user())?->name ?? 'Conversation' }}
            </h2>
            <a href="{{ route('conversations.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
                {{ __('Back to Messages') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow">
                <div id="messages" class="p-6 space-y-4 max-h-96 overflow-y-auto">
                    @forelse ($messages as $message)
                        @include('conversations.partials.message', ['message' => $message])
                    @empty
                        <p class="text-center text-gray-500 py-8">{{ __('No messages yet. Start the conversation!') }}</p>
                    @endforelse
                </div>

                <div class="border-t border-gray-200 p-6">
                    <form hx-post="{{ route('messages.store', $conversation->id) }}"
                          hx-target="#messages"
                          hx-swap="beforeend"
                          hx-on::after-request="this.reset(); document.getElementById('messages').scrollTop = document.getElementById('messages').scrollHeight;">
                        @csrf
                        <div class="flex gap-3">
                            <textarea name="content" rows="1" class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-rose-500 focus:ring-rose-500" placeholder="{{ __('Type your message...') }}" required maxlength="2000"></textarea>
                            <button type="submit" class="px-4 py-2 bg-rose-600 text-white rounded-md hover:bg-rose-700 font-medium self-end">
                                {{ __('Send') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const messages = document.getElementById('messages');
            if (messages) {
                messages.scrollTop = messages.scrollHeight;
            }
        });
    </script>
</x-app-layout>
