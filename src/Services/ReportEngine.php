<?php

namespace ME\Erpaccount\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use ME\Erpaccount\Services\Reports\BuildsBalanceReports;
use ME\Erpaccount\Services\Reports\BuildsBankCashReports;
use ME\Erpaccount\Services\Reports\BuildsCoreReports;
use ME\Erpaccount\Services\Reports\BuildsLcReports;
use ME\Erpaccount\Services\Reports\BuildsManagementReports;
use ME\Erpaccount\Services\Reports\BuildsPayrollReports;
use ME\Erpaccount\Services\Reports\BuildsProductionReports;
use ME\Erpaccount\Services\Reports\BuildsPurchaseExportReports;
use ME\Erpaccount\Services\Reports\BuildsTaxReports;

class ReportEngine
{
    use BuildsCoreReports;
    use BuildsBalanceReports;
    use BuildsTaxReports;
    use BuildsPurchaseExportReports;
    use BuildsLcReports;
    use BuildsProductionReports;
    use BuildsPayrollReports;
    use BuildsBankCashReports;
    use BuildsManagementReports;

    public function __construct(
        protected ReportQueryHelper $query = new ReportQueryHelper()
    ) {
    }

    public function build(string $slug, array $filters = []): array
    {
        $method = $this->reportMethodName($slug);

        if (!method_exists($this, $method)) {
            throw new InvalidArgumentException("Unknown report: {$slug}");
        }

        return $this->{$method}($filters);
    }

    public function isSupported(string $slug): bool
    {
        return method_exists($this, $this->reportMethodName($slug));
    }

    private function reportMethodName(string $slug): string
    {
        return 'report' . str_replace(' ', '', ucwords(str_replace('_', ' ', $slug)));
    }

    protected function table(
        string $title,
        string $subtitle,
        array $columns,
        Collection|array $rows,
        array $summary = [],
        array $notes = []
    ): array {
        $normalizedRows = collect($rows)->map(function ($row) {
            return is_array($row) ? (object) $row : $row;
        })->values();

        return [
            'title' => $title,
            'subtitle' => $subtitle,
            'columns' => $columns,
            'rows' => $normalizedRows,
            'summary' => $summary,
            'notes' => $notes,
        ];
    }

    protected function money(float $amount): string
    {
        return number_format($amount, 2, '.', ',');
    }

    protected function proxyNote(string $module): string
    {
        return 'Ledger-derived view. Connect ' . $module . ' module for document-level detail (invoice/PO/GRN).';
    }
}
