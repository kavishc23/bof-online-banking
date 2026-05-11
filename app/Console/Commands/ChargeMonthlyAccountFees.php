<?php

namespace App\Console\Commands;

use App\Services\AccountFees\MonthlyFeeDeductionService;
use App\Services\Logging\BankingLogger;
use Illuminate\Console\Command;

class ChargeMonthlyAccountFees extends Command
{
    protected $signature = 'charge-monthly-fees';

    protected $aliases = ['accounts:charge-monthly-fees'];

    protected $description = 'Deduct due monthly account fees from Strapi accounts.';

    public function handle(MonthlyFeeDeductionService $deductions, BankingLogger $logger): int
    {
        $this->info('Starting monthly account fee deduction...');

        if (! config('services.strapi.api_token')) {
            $message = 'STRAPI_API_TOKEN is missing. Add it to .env and run php artisan config:clear before running scheduled fee deductions.';

            $this->error($message);
            $logger->activity('monthly_fee.command_blocked', $message);

            return self::FAILURE;
        }

        $this->info('Strapi API token configured: yes');

        $summary = $deductions->chargeDueFees();

        $this->table(['Metric', 'Count'], [
            ['Accounts checked', $summary['accounts_checked']],
            ['Fees charged', $summary['fees_charged']],
            ['Skipped', $summary['skipped']],
            ['Duplicates prevented', $summary['duplicates_prevented']],
            ['Insufficient balance', $summary['insufficient_balance']],
            ['Errors', $summary['errors']],
        ]);

        if (! empty($summary['details'])) {
            $this->line('');
            $this->info('Account fee decisions:');
            $this->table([
                'Account',
                'ID',
                'Document ID',
                'Type',
                'Balance',
                'Monthly Fee Field',
                'Opened At',
                'Created At',
                'Last Fee',
                'Result',
                'Reason',
            ], collect($summary['details'])->map(fn (array $detail): array => [
                $detail['accountNumber'] ?? '-',
                $detail['id'] ?? '-',
                $detail['documentId'] ?? '-',
                $detail['accountType'] ?? '-',
                $detail['balance'] ?? '-',
                $detail['monthlyMaintenanceFee'] ?? '-',
                $detail['openedAt'] ?? '-',
                $detail['createdAt'] ?? '-',
                $detail['lastMonthlyFeeChargedAt'] ?? $detail['lastFeeChargedAt'] ?? '-',
                $detail['result'] ?? '-',
                $this->formatReason($detail),
            ])->all());
        }

        $this->info('Monthly account fee deduction completed.');

        return $summary['errors'] > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @param  array<string, mixed>  $detail
     */
    private function formatReason(array $detail): string
    {
        $extra = [];

        foreach ([
            'calculated_fee' => 'fee',
            'new_balance' => 'new balance',
            'monthly_input' => 'monthly input',
            'withdrawal_count' => 'withdrawals',
            'duplicate_reference' => 'duplicate',
            'http_status' => 'HTTP',
        ] as $key => $label) {
            if (array_key_exists($key, $detail) && $detail[$key] !== null && $detail[$key] !== '') {
                $extra[] = $label.': '.$detail[$key];
            }
        }

        if (! empty($detail['response_body'])) {
            $extra[] = 'response: '.$detail['response_body'];
        }

        return trim((string) ($detail['reason'] ?? '').(empty($extra) ? '' : ' ('.implode(', ', $extra).')'));
    }
}
