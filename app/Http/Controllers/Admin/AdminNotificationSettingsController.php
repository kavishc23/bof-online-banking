<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminNotificationSettingsRequest;
use App\Services\Notifications\NotificationSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminNotificationSettingsController extends Controller
{
    public function __construct(private readonly NotificationSettingsService $settings) {}

    public function index(): View
    {
        return view('admin.notification-settings.index', [
            'settings' => $this->settings->settings(),
            'allowedEventKeys' => NotificationSettingsService::ALLOWED_EVENT_KEYS,
        ]);
    }

    public function update(AdminNotificationSettingsRequest $request): RedirectResponse
    {
        $this->settings->update($request->validated('settings'));

        return redirect()->route('admin.notification-settings.index')->with('success', 'Notification settings updated successfully.');
    }
}
