<?php

namespace ME\Erpaccount\Services\Reports;

trait BuildsPurchaseExportReports
{
    protected function purchaseLines(array $filters, array $keywords): array
    {
        [$start, $end] = $this->query->parsePeriod($filters);
        $ids = $this->query->accountIdsByKeywords($keywords, ['Expense', 'Asset', 'Liability']);

        return [$start, $end, $this->query->journalLines($start, $end, $ids, 'Supplier')];
    }

    protected function reportPurchaseRegister(array $filters): array
    {
        [$start, $end, $lines] = $this->purchaseLines($filters, ['purchase', 'supplier', 'yarn', 'fabric', 'trim', 'accessories', 'import']);

        return $this->table(
            'Purchase Register',
            "Period: {$start->toDateString()} to {$end->toDateString()}",
            [
                ['key' => 'journal_date', 'label' => 'Date'],
                ['key' => 'voucher_no', 'label' => 'Voucher'],
                ['key' => 'account_name', 'label' => 'Account'],
                ['key' => 'party_id', 'label' => 'Supplier ID'],
                ['key' => 'debit_amount', 'label' => 'Debit', 'align' => 'right', 'format' => 'money'],
                ['key' => 'credit_amount', 'label' => 'Credit', 'align' => 'right', 'format' => 'money'],
            ],
            $lines
        );
    }

    protected function reportRawMaterialPurchaseReport(array $filters): array
    {
        [$start, $end, $lines] = $this->purchaseLines($filters, ['raw material', 'yarn', 'fabric', 'stock']);

        return $this->table('Raw Material Purchase Report', "Period: {$start->toDateString()} to {$end->toDateString()}", [
            ['key' => 'journal_date', 'label' => 'Date'],
            ['key' => 'voucher_no', 'label' => 'Voucher'],
            ['key' => 'account_name', 'label' => 'Account'],
            ['key' => 'debit_amount', 'label' => 'Amount', 'align' => 'right', 'format' => 'money'],
        ], $lines);
    }

    protected function reportFabricPurchaseReport(array $filters): array
    {
        [$start, $end, $lines] = $this->purchaseLines($filters, ['fabric', 'yarn']);

        return $this->table('Fabric Purchase Report', "Period: {$start->toDateString()} to {$end->toDateString()}", [
            ['key' => 'journal_date', 'label' => 'Date'],
            ['key' => 'voucher_no', 'label' => 'Voucher'],
            ['key' => 'account_name', 'label' => 'Account'],
            ['key' => 'debit_amount', 'label' => 'Debit', 'align' => 'right', 'format' => 'money'],
        ], $lines);
    }

    protected function reportAccessoriesPurchaseReport(array $filters): array
    {
        [$start, $end, $lines] = $this->purchaseLines($filters, ['accessories', 'trim', 'button', 'zipper', 'label', 'packing']);

        return $this->table('Accessories Purchase Report', "Period: {$start->toDateString()} to {$end->toDateString()}", [
            ['key' => 'journal_date', 'label' => 'Date'],
            ['key' => 'voucher_no', 'label' => 'Voucher'],
            ['key' => 'account_name', 'label' => 'Account'],
            ['key' => 'debit_amount', 'label' => 'Debit', 'align' => 'right', 'format' => 'money'],
        ], $lines);
    }

    protected function reportLocalPurchaseReport(array $filters): array
    {
        [$start, $end, $lines] = $this->purchaseLines($filters, ['local', 'bdt']);

        return $this->table('Local Purchase Report', "Period: {$start->toDateString()} to {$end->toDateString()}", [
            ['key' => 'journal_date', 'label' => 'Date'],
            ['key' => 'voucher_no', 'label' => 'Voucher'],
            ['key' => 'account_name', 'label' => 'Account'],
            ['key' => 'debit_amount', 'label' => 'Debit', 'align' => 'right', 'format' => 'money'],
        ], $lines, [], [$this->proxyNote('Procurement')]);
    }

    protected function reportImportPurchaseReport(array $filters): array
    {
        [$start, $end, $lines] = $this->purchaseLines($filters, ['import', 'lc', 'foreign', 'usd', 'btb']);

        return $this->table('Import Purchase Report', "Period: {$start->toDateString()} to {$end->toDateString()}", [
            ['key' => 'journal_date', 'label' => 'Date'],
            ['key' => 'voucher_no', 'label' => 'Voucher'],
            ['key' => 'account_name', 'label' => 'Account'],
            ['key' => 'debit_amount', 'label' => 'Debit', 'align' => 'right', 'format' => 'money'],
        ], $lines);
    }

    protected function reportSupplierWisePurchaseSummary(array $filters): array
    {
        [$start, $end] = $this->query->parsePeriod($filters);
        $ids = $this->query->accountIdsByKeywords(['payable', 'purchase', 'supplier'], ['Liability', 'Expense']);
        $rows = \Illuminate\Support\Facades\DB::table('acc_journal_details as jd')
            ->join('acc_journal_masters as jm', function ($join) {
                \ME\Erpaccount\Support\JournalQueryScopes::activeMasterOnJoin($join);
            })
            ->whereBetween('jm.journal_date', [$start->toDateString(), $end->toDateString()])
            ->where('jd.party_type', 'Supplier')
            ->whereIn('jd.account_id', $ids)
            ->groupBy('jd.party_id')
            ->selectRaw('jd.party_id as supplier_id')
            ->selectRaw('COALESCE(SUM(jd.debit_amount), 0) as purchase_debit')
            ->selectRaw('COALESCE(SUM(jd.credit_amount), 0) as payment_credit')
            ->orderByDesc('purchase_debit')
            ->get();

        return $this->table(
            'Supplier-wise Purchase Summary',
            "Period: {$start->toDateString()} to {$end->toDateString()}",
            [
                ['key' => 'supplier_id', 'label' => 'Supplier ID'],
                ['key' => 'purchase_debit', 'label' => 'Purchases', 'align' => 'right', 'format' => 'money'],
                ['key' => 'payment_credit', 'label' => 'Payments', 'align' => 'right', 'format' => 'money'],
            ],
            $rows
        );
    }

    protected function reportPoStatusReport(array $filters): array
    {
        $report = $this->reportPurchaseRegister($filters);
        $report['title'] = 'Purchase Order (PO) Status Report';
        $report['notes'][] = $this->proxyNote('Procurement');

        return $report;
    }

    protected function reportGrnReport(array $filters): array
    {
        $report = $this->reportRawMaterialPurchaseReport($filters);
        $report['title'] = 'Goods Received Note (GRN) Report';
        $report['notes'][] = $this->proxyNote('Inventory GRN');

        return $report;
    }

    protected function reportExportSalesRegister(array $filters): array
    {
        [$start, $end] = $this->query->parsePeriod($filters);
        $ids = $this->query->accountIdsByKeywords(['export', 'sales', 'revenue', 'fob'], ['Revenue']);
        $lines = $this->query->journalLines($start, $end, $ids);

        return $this->table('Export Sales Register', "Period: {$start->toDateString()} to {$end->toDateString()}", [
            ['key' => 'journal_date', 'label' => 'Date'],
            ['key' => 'voucher_no', 'label' => 'Voucher'],
            ['key' => 'account_name', 'label' => 'Account'],
            ['key' => 'credit_amount', 'label' => 'Sales', 'align' => 'right', 'format' => 'money'],
        ], $lines);
    }

    protected function reportInvoiceWiseExportReport(array $filters): array
    {
        $report = $this->reportExportSalesRegister($filters);
        $report['title'] = 'Invoice-wise Export Report';
        $report['notes'][] = $this->proxyNote('Commercial Export Invoice');

        return $report;
    }

    protected function reportBuyerWiseSalesReport(array $filters): array
    {
        [$start, $end] = $this->query->parsePeriod($filters);
        $ids = $this->query->accountIdsByKeywords(['receivable', 'export', 'sales'], ['Revenue', 'Asset']);
        $rows = \Illuminate\Support\Facades\DB::table('acc_journal_details as jd')
            ->join('acc_journal_masters as jm', function ($join) {
                \ME\Erpaccount\Support\JournalQueryScopes::activeMasterOnJoin($join);
            })
            ->whereBetween('jm.journal_date', [$start->toDateString(), $end->toDateString()])
            ->where('jd.party_type', 'Buyer')
            ->whereIn('jd.account_id', $ids)
            ->groupBy('jd.party_id')
            ->selectRaw('jd.party_id as buyer_id')
            ->selectRaw('COALESCE(SUM(jd.credit_amount - jd.debit_amount), 0) as sales_amount')
            ->orderByDesc('sales_amount')
            ->get();

        return $this->table('Buyer-wise Sales Report', "Period: {$start->toDateString()} to {$end->toDateString()}", [
            ['key' => 'buyer_id', 'label' => 'Buyer ID'],
            ['key' => 'sales_amount', 'label' => 'Sales', 'align' => 'right', 'format' => 'money'],
        ], $rows);
    }

    protected function reportCountryWiseExportReport(array $filters): array
    {
        $report = $this->reportExportSalesRegister($filters);
        $report['title'] = 'Country-wise Export Report';
        $report['notes'][] = $this->proxyNote('Commercial (country field)');

        return $report;
    }

    protected function reportOrderWiseShipmentReport(array $filters): array
    {
        [$start, $end] = $this->query->parsePeriod($filters);
        $rows = \Illuminate\Support\Facades\DB::table('acc_cost_centers as cc')
            ->join('acc_journal_details as jd', 'cc.cost_center_id', '=', 'jd.cost_center_id')
            ->join('acc_journal_masters as jm', function ($join) {
                \ME\Erpaccount\Support\JournalQueryScopes::activeMasterOnJoin($join);
            })
            ->join('acc_chart_of_accounts as coa', 'coa.account_id', '=', 'jd.account_id')
            ->whereBetween('jm.journal_date', [$start->toDateString(), $end->toDateString()])
            ->where('coa.account_type', 'Revenue')
            ->groupBy('cc.cost_center_id', 'cc.cost_center_name', 'cc.reference_id')
            ->select(['cc.cost_center_name', 'cc.reference_id'])
            ->selectRaw('COALESCE(SUM(jd.credit_amount - jd.debit_amount), 0) as shipment_value')
            ->orderByDesc('shipment_value')
            ->get();

        return $this->table('Order / Style-wise Shipment Report', "Period: {$start->toDateString()} to {$end->toDateString()}", [
            ['key' => 'reference_id', 'label' => 'Order/Style Ref'],
            ['key' => 'cost_center_name', 'label' => 'Cost Center'],
            ['key' => 'shipment_value', 'label' => 'Export Value', 'align' => 'right', 'format' => 'money'],
        ], $rows);
    }

    protected function reportFobValueReport(array $filters): array
    {
        [$start, $end] = $this->query->parsePeriod($filters);
        $ids = $this->query->accountIdsByKeywords(['export', 'fob', 'sales'], ['Revenue']);
        $total = (float) $this->query->journalLines($start, $end, $ids)->sum(fn ($l) => (float) $l->credit_amount - (float) $l->debit_amount);

        return $this->table('FOB Value Report', "Period: {$start->toDateString()} to {$end->toDateString()}", [
            ['key' => 'metric', 'label' => 'Metric'],
            ['key' => 'amount', 'label' => 'Amount', 'align' => 'right', 'format' => 'money'],
        ], collect([(object) ['metric' => 'Total FOB / Export Realization (Ledger)', 'amount' => round($total, 2)]]));
    }

    protected function reportCifFobAdjustmentReport(array $filters): array
    {
        $report = $this->reportFobValueReport($filters);
        $report['title'] = 'CIF / FOB Adjustment Report';
        $report['notes'][] = $this->proxyNote('Commercial freight adjustment');

        return $report;
    }

    protected function reportExportRealizationReport(array $filters): array
    {
        [$start, $end] = $this->query->parsePeriod($filters);
        $ids = $this->query->accountIdsByKeywords(['bank', 'realization', 'export', 'receivable'], ['Asset', 'Revenue']);
        $lines = $this->query->journalLines($start, $end, $ids);

        return $this->table('Export Realization Report', "Period: {$start->toDateString()} to {$end->toDateString()}", [
            ['key' => 'journal_date', 'label' => 'Date'],
            ['key' => 'voucher_no', 'label' => 'Voucher'],
            ['key' => 'account_name', 'label' => 'Account'],
            ['key' => 'debit_amount', 'label' => 'Debit', 'align' => 'right', 'format' => 'money'],
            ['key' => 'credit_amount', 'label' => 'Credit', 'align' => 'right', 'format' => 'money'],
        ], $lines);
    }

    protected function reportSalesReturnReport(array $filters): array
    {
        [$start, $end] = $this->query->parsePeriod($filters);
        $ids = $this->query->accountIdsByKeywords(['return', 'sales return'], ['Revenue', 'Expense']);
        $lines = $this->query->journalLines($start, $end, $ids);

        return $this->table('Sales Return Report', "Period: {$start->toDateString()} to {$end->toDateString()}", [
            ['key' => 'journal_date', 'label' => 'Date'],
            ['key' => 'voucher_no', 'label' => 'Voucher'],
            ['key' => 'account_name', 'label' => 'Account'],
            ['key' => 'debit_amount', 'label' => 'Debit', 'align' => 'right', 'format' => 'money'],
            ['key' => 'credit_amount', 'label' => 'Credit', 'align' => 'right', 'format' => 'money'],
        ], $lines);
    }
}
