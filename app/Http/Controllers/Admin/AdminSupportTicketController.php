<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminSupportTicketRequest;
use App\Services\Support\SupportTicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSupportTicketController extends Controller
{
    public function __construct(private readonly SupportTicketService $tickets) {}

    public function index(Request $request): View
    {
        return view('admin.support-tickets.index', [
            'tickets' => $this->tickets->filteredTickets($request->query()),
            'filters' => $request->query(),
        ]);
    }

    public function show(string $id): View|RedirectResponse
    {
        $ticket = $this->tickets->find($id);

        if (! $ticket) {
            return redirect()->route('admin.support-tickets.index')->with('error', 'Support ticket not found.');
        }

        return view('admin.support-tickets.show', [
            'ticket' => $ticket,
        ]);
    }

    public function update(AdminSupportTicketRequest $request, string $id): RedirectResponse
    {
        $response = $this->tickets->update($id, $request->validated());

        if (! $response->successful()) {
            return back()->withInput()->with('error', 'Support ticket could not be updated.');
        }

        return redirect()->route('admin.support-tickets.show', $id)->with('success', 'Support ticket updated successfully.');
    }
}
