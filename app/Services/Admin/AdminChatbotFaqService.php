<?php

namespace App\Services\Admin;

use App\Services\Audit\AdminAuditLogger;
use App\Services\Strapi\StrapiApiService;
use Illuminate\Http\Client\Response;

class AdminChatbotFaqService
{
    public function __construct(
        private readonly StrapiApiService $strapi,
        private readonly AdminAuditLogger $audit,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function faqs(): array
    {
        return $this->strapi->data($this->strapi->get('/api/chatbot-faqs', [
            'sort' => 'category:asc',
        ]));
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    public function filteredFaqs(array $filters): array
    {
        return collect($this->faqs())
            ->filter(fn (array $faq): bool => $this->matchesFilters($faq, $filters))
            ->values()
            ->all();
    }

    public function find(string $id): ?array
    {
        return $this->strapi->get('/api/chatbot-faqs/'.$id)->json('data');
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function create(array $validated): Response
    {
        $payload = ['data' => $this->payload($validated)];
        $response = $this->strapi->post('/api/chatbot-faqs', $payload);

        if ($response->successful()) {
            $this->audit->log('chatbot_faq_created', ['payload' => $payload]);
        }

        return $response;
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function update(string $id, array $validated): Response
    {
        $payload = ['data' => $this->payload($validated)];
        $response = $this->strapi->put('/api/chatbot-faqs/'.$id, $payload);

        if ($response->successful()) {
            $this->audit->log('chatbot_faq_updated', ['faq_id' => $id, 'payload' => $payload]);
        }

        return $response;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function payload(array $validated): array
    {
        return [
            'question' => $validated['question'],
            'keywords' => collect(explode(',', (string) $validated['keywords']))
                ->map(fn (string $keyword): string => trim($keyword))
                ->filter()
                ->values()
                ->all(),
            'answer' => $validated['answer'],
            'category' => $validated['category'],
            'isActive' => (bool) ($validated['isActive'] ?? false),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function matchesFilters(array $faq, array $filters): bool
    {
        $keywords = $this->keywords($faq['keywords'] ?? []);
        $search = strtolower(trim((string) ($filters['search'] ?? '')));

        if ($search !== '') {
            $haystack = strtolower(implode(' ', [
                $faq['question'] ?? '',
                implode(' ', $keywords),
            ]));

            if (! str_contains($haystack, $search)) {
                return false;
            }
        }

        if (! empty($filters['category']) && ($faq['category'] ?? null) !== $filters['category']) {
            return false;
        }

        if (($filters['isActive'] ?? '') !== '') {
            $isActive = filter_var($filters['isActive'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            if ((bool) ($faq['isActive'] ?? false) !== (bool) $isActive) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array<int, string>
     */
    private function keywords(mixed $keywords): array
    {
        if (is_string($keywords)) {
            $decoded = json_decode($keywords, true);

            return is_array($decoded) ? $decoded : [$keywords];
        }

        return is_array($keywords) ? $keywords : [];
    }
}
