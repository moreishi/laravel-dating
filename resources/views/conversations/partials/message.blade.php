<div class="flex {{ $message->user_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
    <div class="max-w-lg {{ $message->user_id === auth()->id() ? 'bg-rose-50 text-gray-900' : 'bg-gray-50 text-gray-900' }} rounded-lg px-4 py-2">
        <p class="text-sm">{{ $message->content }}</p>
        <p class="text-xs text-gray-500 mt-1">{{ $message->created_at->diffForHumans() }}</p>
    </div>
</div>
