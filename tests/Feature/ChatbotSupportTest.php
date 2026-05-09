<?php

use App\Services\Support\ChatbotFaqService;
use Illuminate\Support\Facades\Http;

function chatbotAdminSession(): array
{
    return [
        'jwt' => 'admin-token',
        'user_role' => 'Admin',
        'user' => ['email' => 'admin@example.com'],
        'customer' => ['email' => 'admin@example.com', 'userRole' => 'Admin'],
    ];
}

function chatbotCustomerSession(): array
{
    return [
        'jwt' => 'customer-token',
        'user_role' => 'Customer',
        'user' => ['email' => 'customer@example.com', 'username' => 'jane'],
        'customer' => ['firstName' => 'Jane', 'lastName' => 'Customer', 'email' => 'customer@example.com'],
    ];
}

function chatbotFaq(array $overrides = []): array
{
    return $overrides + [
        'id' => 51,
        'documentId' => 'faq-51',
        'question' => 'How do I check my balance?',
        'keywords' => ['balance', 'account balance', 'check balance'],
        'answer' => 'You can check your balance from Dashboard > Accounts Overview.',
        'category' => 'Accounts',
        'isActive' => true,
    ];
}

function chatbotTicket(array $overrides = []): array
{
    return $overrides + [
        'id' => 61,
        'documentId' => 'ticket-61',
        'ticketNumber' => 'SUP-2026-0061',
        'customerName' => 'Jane Customer',
        'customerEmail' => 'customer@example.com',
        'subject' => 'Balance help',
        'message' => 'How do I check account balance?',
        'consultantReply' => 'You can check your balance from Dashboard > Accounts Overview.',
        'ticketStatus' => 'InProgress',
        'satisfactionRating' => null,
    ];
}

test('active FAQ keyword match returns answer', function () {
    Http::fake([
        '*localhost:1337/api/chatbot-faqs*' => Http::response(['data' => [chatbotFaq()]]),
    ]);

    $match = app(ChatbotFaqService::class)->match('Balance', 'How do I check account balance?');

    expect($match)->not->toBeNull()
        ->and($match['answer'])->toContain('Dashboard');
});

test('FAQ keywords JSON is handled correctly', function () {
    Http::fake([
        '*localhost:1337/api/chatbot-faqs*' => Http::response([
            'data' => [chatbotFaq(['keywords' => '["card transaction","credit card"]'])],
        ]),
    ]);

    $match = app(ChatbotFaqService::class)->match('Credit card', 'I need help with a card transaction');

    expect($match)->not->toBeNull()
        ->and($match['keyword'])->toBe('card transaction');
});

test('creating chat with FAQ match stores automated reply', function () {
    Http::fake([
        '*localhost:1337/api/chatbot-faqs*' => Http::response(['data' => [chatbotFaq()]]),
        '*localhost:1337/api/support-tickets' => Http::response(['data' => chatbotTicket()]),
    ]);

    $response = $this->withSession(chatbotCustomerSession())->post('/support-chat', [
        'subject' => 'Balance help',
        'message' => 'How do I check my account balance?',
    ]);

    $response->assertRedirect('/support-chat/ticket-61');
    Http::assertSent(fn ($request): bool => $request->method() === 'POST'
        && str_contains($request->url(), '/api/support-tickets')
        && $request['data']['consultantReply'] === 'You can check your balance from Dashboard > Accounts Overview.'
        && $request['data']['ticketStatus'] === 'InProgress');
});

test('customer can mark bot answer resolved', function () {
    Http::fake([
        '*localhost:1337/api/support-tickets/ticket-61' => Http::sequence()
            ->push(['data' => chatbotTicket()])
            ->push(['data' => chatbotTicket(['ticketStatus' => 'Resolved'])]),
    ]);

    $response = $this->withSession(chatbotCustomerSession())->patch('/support-chat/ticket-61/resolved');

    $response->assertSessionHas('success');
    Http::assertSent(fn ($request): bool => $request->method() === 'PUT'
        && str_contains($request->url(), '/api/support-tickets/ticket-61')
        && $request['data']['ticketStatus'] === 'Resolved'
        && array_key_exists('resolvedAt', $request['data']));
});

test('customer can request consultant', function () {
    Http::fake([
        '*localhost:1337/api/support-tickets/ticket-61' => Http::sequence()
            ->push(['data' => chatbotTicket()])
            ->push(['data' => chatbotTicket(['ticketStatus' => 'Open'])]),
    ]);

    $response = $this->withSession(chatbotCustomerSession())->patch('/support-chat/ticket-61/needs-consultant');

    $response->assertSessionHas('success', 'A consultant will review your query.');
    Http::assertSent(fn ($request): bool => $request->method() === 'PUT'
        && str_contains($request->url(), '/api/support-tickets/ticket-61')
        && $request['data']['ticketStatus'] === 'Open');
});

test('admin can view chatbot FAQ page', function () {
    Http::fake([
        '*localhost:1337/api/chatbot-faqs*' => Http::response(['data' => [chatbotFaq()]]),
    ]);

    $response = $this->withSession(chatbotAdminSession())->get('/admin/chatbot-faqs');

    $response->assertOk();
    $response->assertSee('How do I check my balance?');
});

test('admin can create FAQ', function () {
    Http::fake([
        '*localhost:1337/api/chatbot-faqs' => Http::response(['data' => chatbotFaq()]),
    ]);

    $response = $this->withSession(chatbotAdminSession())->post('/admin/chatbot-faqs', [
        'question' => 'How do I check my balance?',
        'keywords' => 'balance, account balance, check balance',
        'answer' => 'You can check your balance from Dashboard > Accounts Overview.',
        'category' => 'Accounts',
        'isActive' => '1',
    ]);

    $response->assertRedirect('/admin/chatbot-faqs');
    Http::assertSent(fn ($request): bool => $request->method() === 'POST'
        && str_contains($request->url(), '/api/chatbot-faqs')
        && $request['data']['keywords'] === ['balance', 'account balance', 'check balance']);
});

test('admin can update FAQ', function () {
    Http::fake([
        '*localhost:1337/api/chatbot-faqs/faq-51' => Http::response(['data' => chatbotFaq()]),
    ]);

    $response = $this->withSession(chatbotAdminSession())->patch('/admin/chatbot-faqs/faq-51', [
        'question' => 'How do I check my balance?',
        'keywords' => 'balance, available balance',
        'answer' => 'Open Dashboard > Accounts Overview.',
        'category' => 'Accounts',
        'isActive' => '0',
    ]);

    $response->assertRedirect('/admin/chatbot-faqs');
    Http::assertSent(fn ($request): bool => $request->method() === 'PUT'
        && str_contains($request->url(), '/api/chatbot-faqs/faq-51')
        && $request['data']['isActive'] === false);
});
