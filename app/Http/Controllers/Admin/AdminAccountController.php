<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminAccountRequest;
use App\Services\Admin\AdminAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminAccountController extends Controller
{
    public function __construct(private readonly AdminAccountService $accounts) {}

    public function index(): View
    {
        return view('admin.accounts.index', [
            'accounts' => $this->accounts->accounts(),
        ]);
    }

    public function create(): View
    {
        return view('admin.accounts.create');
    }

    public function store(AdminAccountRequest $request): RedirectResponse
    {
        $response = $this->accounts->create($request->validated());

        if (! $response->successful()) {
            return back()->withInput()->with('error', 'Account could not be created.');
        }

        return redirect()->route('admin.accounts.index')->with('success', 'Account created successfully.');
    }

    public function edit(string $id): View|RedirectResponse
    {
        $account = $this->accounts->find($id);

        if (! $account) {
            return redirect()->route('admin.accounts.index')->with('error', 'Account not found.');
        }

        return view('admin.accounts.edit', [
            'account' => $account,
        ]);
    }

    public function update(AdminAccountRequest $request, string $id): RedirectResponse
    {
        $response = $this->accounts->update($id, $request->validated());

        if (! $response->successful()) {
            return back()->withInput()->with('error', 'Account could not be updated.');
        }

        return redirect()->route('admin.accounts.index')->with('success', 'Account updated successfully.');
    }
}
