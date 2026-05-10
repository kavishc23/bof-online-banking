<?php

use Illuminate\Support\Facades\Http;

function cs415LiveChatCustomerSession(): array
{
    return [
        'jwt' => 'customer-token',
        'user_role' => 'Customer',
        'user' => [
            'email' => 'customer@example.com',
            'username' => 'customer',
        ],
        'customer' => [
            'firstName' => 'Jane',
            'lastName' => 'Customer',
            'email' => 'customer@example.com',
            'userRole' => 'Customer',
        ],
    ];
}

function cs415LiveChatTicket(array $overrides = []): array
{
    return $overrides + [
        'id' => 81,
        'documentId' => 'ticket-81',
        'ticketNumber' => 'SUP-2026-0081',
        'customerName' => 'Jane Customer',
        'customerEmail' => 'customer@example.com',
        'subject' => 'Card transaction issue',
        'message' => 'I need support with a transaction.',
        'consultantReply' => 'We are reviewing your issue.',
        'ticketStatus' => 'Open',
        'satisfactionRating' => null,
        'satisfactionComment' => null,
        'createdAt' => '2026-05-10T00:00:00.000Z',
    ];
}

test('CS415 live chat: customer can create support issue message', function () {
    Http::fake([
        '*localhost:1337/api/chatbot-faqs*' => Http::response(['data' => []]),
        '*localhost:1337/api/support-tickets' => Http::response([
            'data' => cs415LiveChatTicket(['documentId' => 'ticket-created']),
        ]),
    ]);

    $response = $this->withSession(cs415LiveChatCustomerSession())->post('/support-chat', [
        'subject' => 'Card transaction issue',
        'message' => 'I need support with a transaction.',
    ]);

    $response->assertRedirect('/support-chat/ticket-created');

    Http::assertSent(fn ($request): bool => $request->method() === 'POST'
        && str_contains($request->url(), '/api/support-tickets')
        && str_starts_with((string) $request['data']['ticketNumber'], 'SUP-')
        && $request['data']['customerEmail'] === 'customer@example.com'
        && $request['data']['ticketStatus'] === 'Open');
});

test('CS415 live chat: issue can be marked resolved', function () {
    Http::fake([
        '*localhost:1337/api/support-tickets/ticket-81' => Http::sequence()
            ->push(['data' => cs415LiveChatTicket(['ticketStatus' => 'InProgress'])])
            ->push(['data' => cs415LiveChatTicket(['ticketStatus' => 'Resolved'])]),
    ]);

    $response = $this->withSession(cs415LiveChatCustomerSession())->patch('/support-chat/ticket-81/resolved');

    $response->assertSessionHas('success', 'Glad the virtual assistant helped. You can now rate this support chat.');

    Http::assertSent(fn ($request): bool => $request->method() === 'PUT'
        && str_contains($request->url(), '/api/support-tickets/ticket-81')
        && $request['data']['ticketStatus'] === 'Resolved'
        && array_key_exists('resolvedAt', $request['data']));
});

test('CS415 live chat: issue can remain unresolved by requesting consultant', function () {
    Http::fake([
        '*localhost:1337/api/support-tickets/ticket-81' => Http::sequence()
            ->push(['data' => cs415LiveChatTicket(['ticketStatus' => 'InProgress'])])
            ->push(['data' => cs415LiveChatTicket(['ticketStatus' => 'Open'])]),
    ]);

    $response = $this->withSession(cs415LiveChatCustomerSession())->patch('/support-chat/ticket-81/needs-consultant');

    $response->assertSessionHas('success', 'A consultant will review your query.');

    Http::assertSent(fn ($request): bool => $request->method() === 'PUT'
        && str_contains($request->url(), '/api/support-tickets/ticket-81')
        && $request['data']['ticketStatus'] === 'Open');
});

test('CS415 live chat: customer satisfaction rating can be stored for resolved issue', function () {
    Http::fake([
        '*localhost:1337/api/support-tickets/ticket-81' => Http::sequence()
            ->push(['data' => cs415LiveChatTicket(['ticketStatus' => 'Resolved'])])
            ->push(['data' => cs415LiveChatTicket(['ticketStatus' => 'Resolved', 'satisfactionRating' => 5])]),
    ]);

    $response = $this->withSession(cs415LiveChatCustomerSession())->patch('/support-chat/ticket-81/rating', [
        'satisfactionRating' => 5,
        'satisfactionComment' => 'Resolved quickly.',
    ]);

    $response->assertSessionHas('success', 'Thank you for rating this support chat.');

    Http::assertSent(fn ($request): bool => $request->method() === 'PUT'
        && str_contains($request->url(), '/api/support-tickets/ticket-81')
        && $request['data']['satisfactionRating'] === 5
        && $request['data']['satisfactionComment'] === 'Resolved quickly.');
});
