<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Report Print</title>
    <style>
        body { font-family: Arial, sans-serif; color: #111827; margin: 24px; }
        h1 { margin: 0 0 6px; }
        h2 { margin: 24px 0 10px; font-size: 18px; }
        .meta { font-size: 12px; color: #4b5563; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th, td { border: 1px solid #d1d5db; padding: 6px 8px; }
        th { background: #f3f4f6; text-align: left; }
        .right { text-align: right; }
        .double-total td { border-top: 3px double #111827; font-weight: 700; }
        .section { margin-bottom: 24px; }
        .badge-ok { color: #047857; font-weight: 700; }
        .badge-bad { color: #b91c1c; font-weight: 700; }
        @media print {
            .no-print { display: none; }
            body { margin: 10mm; }
        }
    </style>
</head>
<body>
    <button class="no-print" onclick="window.print()">Print / Save as PDF</button>

    <h1>Standard Financial Report</h1>
    <div class="meta">Type: {{ str_replace('_', ' ', ucfirst($reportType)) }} | Range: {{ $startDate->toDateString() }} to {{ $endDate->toDateString() }}</div>

    @if ($reportType === 'trial_balance')
        <div class="section">
            <h2>Trial Balance</h2>
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Account</th>
                        <th>Type</th>
                        <th class="right">Period Debit</th>
                        <th class="right">Period Credit</th>
                        <th class="right">Closing Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($report['rows'] as $row)
                        <tr>
                            <td>{{ $row->account_code }}</td>
                            <td>{{ $row->account_name }}</td>
                            <td>{{ $row->account_type }}</td>
                            <td class="right">{{ number_format((float) $row->period_debit, 2) }}</td>
                            <td class="right">{{ number_format((float) $row->period_credit, 2) }}</td>
                            <td class="right">{{ number_format((float) $row->closing_balance, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="double-total">
                        <td colspan="3">TOTAL</td>
                        <td class="right">{{ number_format((float) $report['total_debits'], 2) }}</td>
                        <td class="right">{{ number_format((float) $report['total_credits'], 2) }}</td>
                        <td class="right">{{ $report['is_balanced'] ? 'Balanced' : 'Not Balanced' }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif

    @if ($reportType === 'profit_and_loss')
        <div class="section">
            <h2>Profit &amp; Loss Statement</h2>
            <table>
                <thead>
                    <tr>
                        <th>Account</th>
                        <th>Type</th>
                        <th class="right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($report['revenue_rows'] as $row)
                        <tr>
                            <td>{{ $row->account_name }}</td>
                            <td>Revenue</td>
                            <td class="right">{{ number_format((float) $row->amount, 2) }}</td>
                        </tr>
                    @endforeach
                    @foreach ($report['expense_rows'] as $row)
                        <tr>
                            <td>{{ $row->account_name }}</td>
                            <td>Expense</td>
                            <td class="right">{{ number_format((float) $row->amount, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="double-total">
                        <td colspan="2">Total Revenue</td>
                        <td class="right">{{ number_format((float) $report['total_revenue'], 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="2">Total Expenses</td>
                        <td class="right">{{ number_format((float) $report['total_expenses'], 2) }}</td>
                    </tr>
                    <tr class="double-total">
                        <td colspan="2">Net Profit</td>
                        <td class="right">{{ number_format((float) $report['net_profit'], 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif

    @if ($reportType === 'balance_sheet')
        <div class="section">
            <h2>Balance Sheet</h2>
            <table>
                <thead>
                    <tr>
                        <th>Account</th>
                        <th>Group</th>
                        <th class="right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($report['assets'] as $row)
                        <tr>
                            <td>{{ $row->account_name }}</td>
                            <td>{{ $row->balance_group }}</td>
                            <td class="right">{{ number_format((float) $row->amount, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="double-total">
                        <td colspan="2">Total Assets</td>
                        <td class="right">{{ number_format((float) $report['total_assets'], 2) }}</td>
                    </tr>
                    @foreach ($report['liabilities_equity'] as $row)
                        <tr>
                            <td>{{ $row->account_name }}</td>
                            <td>{{ $row->balance_group }}</td>
                            <td class="right">{{ number_format((float) $row->amount, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="double-total">
                        <td colspan="2">Total Liabilities + Equity</td>
                        <td class="right">{{ number_format((float) $report['total_liabilities_equity'], 2) }}</td>
                    </tr>
                </tbody>
            </table>
            <p class="{{ $report['is_balanced'] ? 'badge-ok' : 'badge-bad' }}">{{ $report['is_balanced'] ? 'Balanced' : 'Not Balanced' }}</p>
        </div>
    @endif
</body>
</html>
