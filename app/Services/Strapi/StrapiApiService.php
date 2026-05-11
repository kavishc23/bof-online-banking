<?php

namespace App\Services\Strapi;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class StrapiApiService
{
    public function baseUrl(): string
    {
        return rtrim((string) config('services.strapi.url', 'http://localhost:1337'), '/');
    }

    /**
     * @param  array<string, mixed>  $query
     */
    public function get(string $path, array $query = []): Response
    {
        return $this->request()->get($this->url($path), $query);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function post(string $path, array $payload): Response
    {
        return $this->request()->post($this->url($path), $payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function put(string $path, array $payload): Response
    {
        return $this->request()->put($this->url($path), $payload);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function data(Response $response): array
    {
        return $response->json('data') ?? [];
    }

    private function request(): mixed
    {
        $apiToken = config('services.strapi.api_token');

        if ($apiToken) {
            return Http::withToken((string) $apiToken)->acceptJson();
        }

        $jwt = session('jwt');

        if ($jwt) {
            return Http::withToken($jwt)->acceptJson();
        }

        return Http::acceptJson();
    }

    private function url(string $path): string
    {
        return $this->baseUrl().'/'.ltrim($path, '/');
    }
}
