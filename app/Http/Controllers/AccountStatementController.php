<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class AccountStatementController extends Controller
{
    private function getStatements()
    {
        return [
            [
                'group' => 'February-2026',
                'items' => [
                    [
                        'id' => 'feb-2026-1',
                        'name' => 'Account Statement-044145600017-Monthly',
                        'date' => '2026-02-27',
                        'account' => '044145600017',
                        'type' => 'Monthly',
                        'issue_date' => '2026-02-27',
                        'period_start' => '2026-02-01',
                        'period_end' => '2026-02-27',
                        'customer_name' => 'Jayshil',
                        'address_1' => 'Raiwaqa, Suva',
                        'address_2' => 'Fiji',
                        'transactions' => [
                            ['date' => '2026-02-01', 'payment_type' => 'Balance', 'detail' => 'Balance Brought Forward', 'paid_in' => '', 'paid_out' => '', 'balance' => '8,313.30'],
                            ['date' => '2026-02-05', 'payment_type' => 'Fast Payment', 'detail' => 'Amazon', 'paid_in' => '', 'paid_out' => '132.30', 'balance' => '8,181.00'],
                            ['date' => '2026-02-09', 'payment_type' => 'BACS', 'detail' => 'eBAY Trading Co.', 'paid_in' => '', 'paid_out' => '515.22', 'balance' => '7,665.78'],
                            ['date' => '2026-02-11', 'payment_type' => 'Fast Payment', 'detail' => 'Morrisons Petrol', 'paid_in' => '', 'paid_out' => '80.00', 'balance' => '7,585.78'],
                            ['date' => '2026-02-14', 'payment_type' => 'Salary', 'detail' => 'Monthly Salary Credit', 'paid_in' => '20,000.00', 'paid_out' => '', 'balance' => '27,585.78'],
                            ['date' => '2026-02-16', 'payment_type' => 'BACS', 'detail' => 'Business Loan', 'paid_in' => '', 'paid_out' => '2,416.85', 'balance' => '25,168.93'],
                            ['date' => '2026-02-18', 'payment_type' => 'Fast Payment', 'detail' => 'ATM High Street', 'paid_in' => '', 'paid_out' => '100.00', 'balance' => '25,068.93'],
                            ['date' => '2026-02-21', 'payment_type' => 'BACS', 'detail' => 'Advertising Studio', 'paid_in' => '', 'paid_out' => '150.00', 'balance' => '24,918.93'],
                            ['date' => '2026-02-27', 'payment_type' => 'Card', 'detail' => 'BOS Mastercard', 'paid_in' => '', 'paid_out' => '4,000.00', 'balance' => '20,918.93'],
                        ],
                    ],
                ],
            ],
            [
                'group' => 'January-2026',
                'items' => [
                    [
                        'id' => 'jan-2026-1',
                        'name' => 'Account Statement-044145600017-Monthly',
                        'date' => '2026-01-30',
                        'account' => '044145600017',
                        'type' => 'Monthly',
                        'issue_date' => '2026-01-30',
                        'period_start' => '2026-01-01',
                        'period_end' => '2026-01-30',
                        'customer_name' => 'Jayshil',
                        'address_1' => 'Raiwaqa, Suva',
                        'address_2' => 'Fiji',
                        'transactions' => [
                            ['date' => '2026-01-01', 'payment_type' => 'Balance', 'detail' => 'Balance Brought Forward', 'paid_in' => '', 'paid_out' => '', 'balance' => '6,313.30'],
                            ['date' => '2026-01-05', 'payment_type' => 'Fast Payment', 'detail' => 'Utilities Payment', 'paid_in' => '', 'paid_out' => '132.30', 'balance' => '6,181.00'],
                            ['date' => '2026-01-10', 'payment_type' => 'BACS', 'detail' => 'Online Purchase', 'paid_in' => '', 'paid_out' => '515.22', 'balance' => '5,665.78'],
                            ['date' => '2026-01-15', 'payment_type' => 'Salary', 'detail' => 'Monthly Salary Credit', 'paid_in' => '10,000.00', 'paid_out' => '', 'balance' => '15,665.78'],
                            ['date' => '2026-01-20', 'payment_type' => 'Card', 'detail' => 'Groceries', 'paid_in' => '', 'paid_out' => '280.00', 'balance' => '15,385.78'],
                            ['date' => '2026-01-30', 'payment_type' => 'Card', 'detail' => 'Bill Payment', 'paid_in' => '', 'paid_out' => '752.00', 'balance' => '14,633.78'],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function index()
    {
        $statements = $this->getStatements();
        return view('account-statement', compact('statements'));
    }

    public function download($id)
    {
        $statements = collect($this->getStatements())
            ->flatMap(fn ($group) => $group['items'])
            ->firstWhere('id', $id);

        if (!$statements) {
            abort(404);
        }

        $pdf = Pdf::loadView('pdf.account-statement-pdf', [
            'statement' => $statements
        ])->setPaper('a4', 'portrait');

        return $pdf->download($statements['name'] . '.pdf');
    }

    public function preview($id)
    {
        $statements = collect($this->getStatements())
            ->flatMap(fn ($group) => $group['items'])
            ->firstWhere('id', $id);

        if (!$statements) {
            abort(404);
        }

        $pdf = Pdf::loadView('pdf.account-statement-pdf', [
            'statement' => $statements
        ])->setPaper('a4', 'portrait');

        return $pdf->stream($statements['name'] . '.pdf');
    }
}