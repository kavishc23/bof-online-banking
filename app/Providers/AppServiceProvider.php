<?php

namespace App\Providers;

use App\Contracts\AuditLogger;
use App\Contracts\FeeCalculator;
use App\Events\BankingActivityOccurred;
use App\Listeners\LogBankingActivity;
use App\Listeners\SendBankingNotification;
use App\Models\User;
use App\Observers\UserObserver;
use App\Services\AuditService;
use App\Services\FeeCalculationService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AuditLogger::class, AuditService::class);
        $this->app->bind(FeeCalculator::class, FeeCalculationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(BankingActivityOccurred::class, LogBankingActivity::class);
        Event::listen(BankingActivityOccurred::class, SendBankingNotification::class);

        User::observe(UserObserver::class);
    }
}
