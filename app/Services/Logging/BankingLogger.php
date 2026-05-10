<?php

namespace App\Services\Logging;

use Illuminate\Http\Client\Response as ClientResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class BankingLogger
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function activity(string $type, string $message, array $context = []): void
    {
        Log::channel('banking')->info($message, $this->withActor($context + ['type' => $type]));
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function audit(string $action, array $context = []): void
    {
        Log::channel('audit')->info($action, $this->withActor($context));
    }

    public function apiFailure(string $step, ClientResponse $response): void
    {
        Log::channel('banking')->warning('Strapi API request failed.', $this->withActor([
            'step' => $step,
            'status' => $response->status(),
            'body' => $response->body(),
        ]));
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function apiFailureWithPayload(string $step, ClientResponse $response, array $payload): void
    {
        Log::channel('banking')->warning('Strapi API request failed.', $this->withActor([
            'step' => $step,
            'status' => $response->status(),
            'body' => $response->body(),
            'payload' => $payload,
        ]));
    }

    public function failedRequest(Request $request, int $statusCode): void
    {
        Log::channel('banking')->warning('Failed banking request.', $this->withActor([
            'method' => $request->method(),
            'path' => $request->path(),
            'status' => $statusCode,
            'ip' => $request->ip(),
        ]));
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function exception(Throwable $exception, array $context = []): void
    {
        Log::channel('banking')->error($exception->getMessage(), $this->withActor($context + [
            'exception' => $exception::class,
        ]));
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function withActor(array $context): array
    {
        return $context + [
            'actor' => session('user.email') ?? session('user.username') ?? 'guest',
            'logged_at' => now()->toISOString(),
        ];
    }
}
