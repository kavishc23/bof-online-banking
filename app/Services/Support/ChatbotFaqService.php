<?php

namespace App\Services\Support;

use App\Services\Strapi\StrapiApiService;
use App\Services\SupportService;

class ChatbotFaqService
{
    public function __construct(
        private readonly StrapiApiService $strapi,
        private readonly SupportService $logger,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function activeFaqs(): array
    {
        return $this->strapi->data($this->strapi->get('/api/chatbot-faqs', [
            'filters[isActive][$eq]' => true,
        ]));
    }

    /**
     * @return array{question: string, answer: string, category: string|null, keyword: string}|null
     */
    public function match(string $subject, string $message): ?array
    {
        $text = strtolower($subject.' '.$message);
        $matches = [];

        foreach ($this->activeFaqs() as $faq) {
            foreach ($this->keywords($faq['keywords'] ?? []) as $keyword) {
                $normalizedKeyword = strtolower(trim($keyword));

                if ($normalizedKeyword !== '' && str_contains($text, $normalizedKeyword)) {
                    $matches[] = [
                        'question' => (string) ($faq['question'] ?? ''),
                        'answer' => (string) ($faq['answer'] ?? ''),
                        'category' => $faq['category'] ?? null,
                        'keyword' => $normalizedKeyword,
                        'length' => strlen($normalizedKeyword),
                    ];
                }
            }
        }

        if ($matches === []) {
            return null;
        }

        usort($matches, fn (array $left, array $right): int => $right['length'] <=> $left['length']);
        $match = $matches[0];
        unset($match['length']);

        $this->logger->logChatbotFaqMatch($match['question'], [
            'keyword' => $match['keyword'],
            'category' => $match['category'],
        ]);

        return $match;
    }

    /**
     * @return array<int, string>
     */
    private function keywords(mixed $keywords): array
    {
        if (is_string($keywords)) {
            $decoded = json_decode($keywords, true);

            return is_array($decoded) ? array_values($decoded) : [$keywords];
        }

        return is_array($keywords) ? array_values($keywords) : [];
    }
}
