<?php

namespace App\Http\Controllers;

use App\Actions\StartConversationAction;
use App\Data\StartConversationData;
use App\Models\Conversation;
use App\Services\ConversationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConversationController extends Controller
{
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
        $data = StartConversationData::from($request->all());

        $conversation = $this->startConversationAction->execute(
            auth()->id(),
            $data->recipientId,
        );

        return redirect()->route('conversations.show', $conversation);
    }
}
