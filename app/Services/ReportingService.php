<?php

namespace App\Services;

use App\Events\BankingActivityOccurred;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ReportingService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function accountStatements(): array
    {
        return config('banking.statements', []);
    }

    public function findAccountStatement(string $id): ?array
    {
        return collect($this->accountStatements())
            ->flatMap(fn (array $group): array => $group['items'])
            ->firstWhere('id', $id);
    }

    public function downloadAccountStatement(string $id): Response
    {
        $statement = $this->statementOrFail($id);

        event(new BankingActivityOccurred('report.generated', 'Account statement downloaded.', [
            'statement_id' => $id,
            'report_type' => 'account_statement',
        ]));

        return Pdf::loadView('pdf.account-statement-pdf', [
            'statement' => $statement,
        ])->setPaper('a4', 'portrait')->download($statement['name'].'.pdf');
    }

    public function previewAccountStatement(string $id): Response
    {
        $statement = $this->statementOrFail($id);

        event(new BankingActivityOccurred('report.generated', 'Account statement previewed.', [
            'statement_id' => $id,
            'report_type' => 'account_statement',
        ]));

        return Pdf::loadView('pdf.account-statement-pdf', [
            'statement' => $statement,
        ])->setPaper('a4', 'portrait')->stream($statement['name'].'.pdf');
    }

    private function statementOrFail(string $id): array
    {
        $statement = $this->findAccountStatement($id);

        if (! $statement) {
            abort(404);
        }

        return $statement;
    }
}
