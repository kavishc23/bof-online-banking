<?php

namespace App\Console\Commands;

use App\Services\AccountFees\MonthlyFeeDeductionService;
use Illuminate\Console\Command;

class ChargeMonthlyAccountFees extends Command
{
    protected $signature = 'accounts:charge-monthly-fees';

    protected $description = 'Deduct due monthly account fees from Strapi accounts.';

    public function handle(MonthlyFeeDeductionService $deductions): int
    {
        $this->info('Starting monthly account fee deduction...');

        $summary = $deductions->chargeDueFees();

        $this->table(['Metric', 'Count'], [
            ['Accounts checked', $summary['accounts_checked']],
            ['Fees charged', $summary['fees_charged']],
            ['Skipped', $summary['skipped']],
            ['Duplicates prevented', $summary['duplicates_prevented']],
            ['Insufficient balance', $summary['insufficient_balance']],
            ['Errors', $summary['errors']],
        ]);

        $this->info('Monthly account fee deduction completed.');

        return self::SUCCESS;
    }
}
