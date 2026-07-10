<?php

namespace App\Http\Controllers;

use App\Actions\SendMessageAction;
use App\Models\Conversation;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessageController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly SendMessageAction $sendMessageAction,
    ) {}

    public function store(Request $request, Conversation $conversation): RedirectResponse|View
    {
        $this->authorize('view', $conversation);

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
        ]);

        $message = $this->sendMessageAction->execute(
            $conversation->id,
            auth()->id(),
            $validated['content'],
        );

        if ($request->header('HX-Request')) {
            return view('conversations.partials.message', ['message' => $message->load('user')]);
        }

        return redirect()->route('conversations.show', $conversation);
    }
}
