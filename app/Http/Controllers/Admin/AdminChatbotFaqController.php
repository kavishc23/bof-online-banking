<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminChatbotFaqRequest;
use App\Services\Admin\AdminChatbotFaqService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminChatbotFaqController extends Controller
{
    public function __construct(private readonly AdminChatbotFaqService $faqs) {}

    public function index(Request $request): View
    {
        return view('admin.chatbot-faqs.index', [
            'faqs' => $this->faqs->filteredFaqs($request->query()),
            'filters' => $request->query(),
        ]);
    }

    public function create(): View
    {
        return view('admin.chatbot-faqs.create');
    }

    public function store(AdminChatbotFaqRequest $request): RedirectResponse
    {
        $response = $this->faqs->create($request->validated());

        if (! $response->successful()) {
            return back()->withInput()->with('error', 'FAQ could not be created.');
        }

        return redirect()->route('admin.chatbot-faqs.index')->with('success', 'FAQ created successfully.');
    }

    public function edit(string $id): View|RedirectResponse
    {
        $faq = $this->faqs->find($id);

        if (! $faq) {
            return redirect()->route('admin.chatbot-faqs.index')->with('error', 'FAQ not found.');
        }

        return view('admin.chatbot-faqs.edit', ['faq' => $faq]);
    }

    public function update(AdminChatbotFaqRequest $request, string $id): RedirectResponse
    {
        $response = $this->faqs->update($id, $request->validated());

        if (! $response->successful()) {
            return back()->withInput()->with('error', 'FAQ could not be updated.');
        }

        return redirect()->route('admin.chatbot-faqs.index')->with('success', 'FAQ updated successfully.');
    }
}
