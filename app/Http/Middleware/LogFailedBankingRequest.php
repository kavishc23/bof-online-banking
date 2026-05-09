<?php

namespace App\Http\Middleware;

use App\Services\Logging\BankingLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogFailedBankingRequest
{
    public function __construct(private readonly BankingLogger $logger) {}

    /**
     * Logging aspect: records failed web/API responses outside controllers.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->getStatusCode() >= 400) {
            $this->logger->failedRequest($request, $response->getStatusCode());
        }

        return $response;
    }
}
