<?php

use Illuminate\Support\Facades\Http;

function supportCustomerSession(): array
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

function supportTicket(array $overrides = []): array
{
    return $overrides + [
        'id' => 10,
        'documentId' => 'ticket-10',
        'ticketNumber' => 'SUP-2026-0010',
        'customerName' => 'Jane Customer',
        'customerEmail' => 'customer@example.com',
        'subject' => 'Transfer issue',
        'message' => 'I cannot transfer money.',
        'consultantReply' => 'We are checking this for you.',
        'ticketStatus' => 'Open',
        'satisfactionRating' => null,
        'satisfactionComment' => null,
        'createdAt' => '2026-05-09T00:00:00.000Z',
        'updatedAt' => '2026-05-09T01:00:00.000Z',
    ];
}

test('logged-in customer can open support chat page', function () {
    Http::fake([
        '*localhost:1337/api/support-tickets*' => Http::response([
            'data' => [supportTicket()],
        ]),
    ]);

    $response = $this->withSession(supportCustomerSession())->get('/support-chat');

    $response->assertOk();
    $response->assertSee('Live Chat Support');
    $response->assertSee('SUP-2026-0010');
});

test('guest cannot access support chat', function () {
    $response = $this->get('/support-chat');

    $response->assertRedirect('/login');
});

test('customer can create support chat', function () {
    Http::fake([
        '*localhost:1337/api/support-tickets' => Http::response([
            'data' => supportTicket(['documentId' => 'ticket-new']),
        ]),
        '*localhost:1337/api/chatbot-faqs*' => Http::response(['data' => []]),
    ]);

    $response = $this->withSession(supportCustomerSession())->post('/support-chat', [
        'subject' => 'Transfer issue',
        'message' => 'I cannot transfer money.',
    ]);

    $response->assertRedirect('/support-chat/ticket-new');

    Http::assertSent(fn ($request): bool => $request->method() === 'POST'
        && str_contains($request->url(), '/api/support-tickets')
        && str_starts_with($request['data']['ticketNumber'], 'SUP-')
        && $request['data']['customerName'] === 'Jane Customer'
        && $request['data']['customerEmail'] === 'customer@example.com'
        && $request['data']['ticketStatus'] === 'Open');
});

test('customer only sees their own chats', function () {
    Http::fake([
        '*localhost:1337/api/support-tickets*' => Http::response([
            'data' => [
                supportTicket(['subject' => 'My transfer issue']),
                supportTicket([
                    'id' => 11,
                    'documentId' => 'ticket-11',
                    'ticketNumber' => 'SUP-2026-0011',
                    'customerEmail' => 'other@example.com',
                    'subject' => 'Other customer issue',
                ]),
            ],
        ]),
    ]);

    $response = $this->withSession(supportCustomerSession())->get('/support-chat');

    $response->assertOk();
    $response->assertSee('My transfer issue');
    $response->assertDontSee('Other customer issue');
});

test('customer can rate resolved ticket', function () {
    Http::fake([
        '*localhost:1337/api/support-tickets/ticket-10' => Http::sequence()
            ->push(['data' => supportTicket(['ticketStatus' => 'Resolved'])])
            ->push(['data' => supportTicket(['ticketStatus' => 'Resolved', 'satisfactionRating' => 5])]),
    ]);

    $response = $this->withSession(supportCustomerSession())->patch('/support-chat/ticket-10/rating', [
        'satisfactionRating' => 5,
        'satisfactionComment' => 'Very fast support.',
    ]);

    $response->assertSessionHas('success', 'Thank you for rating this support chat.');

    Http::assertSent(fn ($request): bool => $request->method() === 'PUT'
        && str_contains($request->url(), '/api/support-tickets/ticket-10')
        && $request['data']['satisfactionRating'] === 5
        && $request['data']['satisfactionComment'] === 'Very fast support.');
});

test('customer cannot rate unresolved ticket', function () {
    Http::fake([
        '*localhost:1337/api/support-tickets/ticket-10' => Http::response([
            'data' => supportTicket(['ticketStatus' => 'Unresolved']),
        ]),
    ]);

    $response = $this->withSession(supportCustomerSession())->patch('/support-chat/ticket-10/rating', [
        'satisfactionRating' => 4,
        'satisfactionComment' => 'Please follow up.',
    ]);

    $response->assertSessionHas('error', 'Only resolved support chats can be rated.');

    Http::assertNotSent(fn ($request): bool => $request->method() === 'PUT');
});
