<?php

namespace App\Http\Controllers;

use App\Actions\StartConversationAction;
use App\Data\StartConversationData;
use App\Models\Conversation;
use App\Services\ConversationService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConversationController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly ConversationService $conversationService,
        private readonly StartConversationAction $startConversationAction,
    ) {}

    public function index(): View
    {
        return view('conversations.index', [
            'conversations' => $this->conversationService->getUserConversations(auth()->id()),
        ]);
    }

    public function show(int $id): View
    {
        $conversation = $this->conversationService->getConversation($id);

        $this->authorize('view', $conversation);

        $this->conversationService->markAsRead($conversation, auth()->id());

        return view('conversations.show', [
            'conversation' => $conversation,
            'messages' => $conversation->messages()->with('user')->oldest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'recipient_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        if ((int) $validated['recipient_id'] === auth()->id()) {
            return redirect()->back()->withErrors(['recipient_id' => 'You cannot start a conversation with yourself.']);
        }

        $data = StartConversationData::from($validated);

        $conversation = $this->startConversationAction->execute(
            auth()->id(),
            $data->recipientId,
        );

        return redirect()->route('conversations.show', $conversation);
    }
}
