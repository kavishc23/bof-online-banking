<?php

namespace App\Services\Notifications;

use App\Services\Audit\AdminAuditLogger;
use App\Services\Logging\BankingLogger;
use App\Services\Strapi\StrapiApiService;

class NotificationSettingsService
{
    public const ALLOWED_EVENT_KEYS = [
        'loan_payment_due',
        'credit_card_transactions',
        'bill_payments',
        'bill_paid',
        'money_sent',
        'money_received',
    ];

    public const EVENT_KEY_ALIASES = [
        'bill_payments' => ['bill_paid'],
    ];

    public function __construct(
        private readonly StrapiApiService $strapi,
        private readonly AdminAuditLogger $audit,
        private readonly BankingLogger $logger,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function settings(): array
    {
        return $this->strapi->data($this->strapi->get('/api/notification-settings', [
            'sort' => 'eventLabel:asc',
        ]));
    }

    public function isEnabled(string $eventKey): bool
    {
        if (! in_array($eventKey, self::ALLOWED_EVENT_KEYS, true)) {
            $this->logger->activity('notification.setting_denied', 'Notification message suppressed because event key is not allowed.', [
                'event_key' => $eventKey,
            ]);

            return false;
        }

        $setting = $this->findSettingByEventKey($eventKey);

        $enabled = (bool) ($setting['enabled'] ?? false);

        $this->logger->activity(
            $enabled ? 'notification.message_allowed' : 'notification.message_suppressed',
            $enabled
                ? 'Notification confirmation message allowed by admin settings.'
                : 'Notification confirmation message suppressed by admin settings.',
            ['event_key' => $eventKey]
        );

        return $enabled;
    }

    /**
     * @param  array<string, bool|int|string>  $settings
     */
    public function update(array $settings): void
    {
        foreach ($this->settings() as $setting) {
            $eventKey = $setting['eventKey'] ?? null;

            if (! in_array($eventKey, self::ALLOWED_EVENT_KEYS, true) || ! array_key_exists($eventKey, $settings)) {
                continue;
            }

            $enabled = filter_var($settings[$eventKey], FILTER_VALIDATE_BOOLEAN);
            $id = (string) ($setting['documentId'] ?? $setting['id']);
            $payload = ['data' => ['enabled' => $enabled]];
            $response = $this->strapi->put('/api/notification-settings/'.$id, $payload);

            if ($response->successful()) {
                $this->audit->log('notification_setting_updated', [
                    'event_key' => $eventKey,
                    'enabled' => $enabled,
                ]);
            }
        }
    }

    /**
     * Supports newer Strapi records such as bill_paid while keeping older code
     * that asks for bill_payments working.
     *
     * @return array<string, mixed>|null
     */
    private function findSettingByEventKey(string $eventKey): ?array
    {
        $keys = [$eventKey, ...(self::EVENT_KEY_ALIASES[$eventKey] ?? [])];

        foreach ($keys as $key) {
            $setting = $this->strapi->data($this->strapi->get('/api/notification-settings', [
                'filters[eventKey][$eq]' => $key,
            ]))[0] ?? null;

            if ($setting) {
                return $setting;
            }
        }

        return null;
    }
}
