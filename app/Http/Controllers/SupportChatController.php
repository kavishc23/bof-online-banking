<?php

namespace App\Http\Controllers;

use App\Http\Requests\RateSupportChatRequest;
use App\Http\Requests\StoreSupportChatRequest;
use App\Services\Support\CustomerSupportChatService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SupportChatController extends Controller
{
    public function __construct(private readonly CustomerSupportChatService $supportChat) {}

    public function index(): View
    {
        return view('support-chat.index', [
            'chats' => $this->supportChat->chats(),
        ]);
    }

    public function create(): View
    {
        return view('support-chat.create');
    }

    public function store(StoreSupportChatRequest $request): RedirectResponse
    {
        $response = $this->supportChat->create($request->validated());

        if (! $response->successful()) {
            return back()->withInput()->with('error', 'Support chat could not be created. Please try again.');
        }

        $ticket = $response->json('data');
        $ticketId = $ticket['documentId'] ?? $ticket['id'] ?? null;

        if ($ticketId) {
            return redirect()->route('support-chat.show', $ticketId)->with('success', 'Support chat started successfully.');
        }

        return redirect()->route('support-chat.index')->with('success', 'Support chat started successfully.');
    }

    public function show(string $id): View|RedirectResponse
    {
        $chat = $this->supportChat->findForCustomer($id);

        if (! $chat) {
            return redirect()->route('support-chat.index')->with('error', 'Support chat not found.');
        }

        return view('support-chat.show', [
            'chat' => $chat,
        ]);
    }

    public function rate(RateSupportChatRequest $request, string $id): RedirectResponse
    {
        $result = $this->supportChat->rate($id, $request->validated());

        return back()->with($result['successful'] ? 'success' : 'error', $result['message']);
    }

    public function resolved(string $id): RedirectResponse
    {
        $result = $this->supportChat->markResolved($id);

        return back()->with($result['successful'] ? 'success' : 'error', $result['message']);
    }

    public function needsConsultant(string $id): RedirectResponse
    {
        $result = $this->supportChat->needsConsultant($id);

        return back()->with($result['successful'] ? 'success' : 'error', $result['message']);
    }
}
