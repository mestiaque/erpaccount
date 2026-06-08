<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Party Ledger Print</title>
    <style>
        body { font-family: Arial, sans-serif; color: #111827; margin: 24px; }
        h1 { margin: 0 0 6px; }
        .meta { font-size: 12px; color: #4b5563; margin-bottom: 16px; }
        .stats { margin: 10px 0 16px; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th, td { border: 1px solid #d1d5db; padding: 6px 8px; }
        th { background: #f3f4f6; text-align: left; }
        .right { text-align: right; }
        .double-total td { border-top: 3px double #111827; font-weight: 700; }
        @media print {
            .no-print { display: none; }
            body { margin: 10mm; }
        }
    </style>
</head>
<body>
    <button class="no-print" onclick="window.print()">Print / Save as PDF</button>

    <h1>Party Ledger History</h1>
    <div class="meta">
        Range: {{ $startDate->toDateString() }} to {{ $endDate->toDateString() }} |
        Party: {{ $report['party_type'] }} - {{ $report['party_name'] }} (ID: {{ $report['party_id'] }})
    </div>
    <div class="meta">
        Ledger: {{ $report['party_ledger'] ?? 'Not Linked' }}
    </div>

    <div class="stats">
        Opening: {{ number_format((float) $report['opening_balance'], 2) }} {{ $report['opening_side'] }} |
        Period Debit: {{ number_format((float) $report['period_debit'], 2) }} |
        Period Credit: {{ number_format((float) $report['period_credit'], 2) }} |
        Closing: {{ number_format((float) $report['closing_balance'], 2) }} {{ $report['closing_side'] }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Voucher</th>
                <th>Account</th>
                <th>Narration</th>
                <th class="right">Debit</th>
                <th class="right">Credit</th>
                <th class="right">Running Balance</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($report['rows'] as $row)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($row->journal_date)->format('Y-m-d') }}</td>
                    <td>{{ $row->voucher_no }}</td>
                    <td>{{ $row->account_label }}</td>
                    <td>{{ $row->narration }}</td>
                    <td class="right">{{ number_format((float) $row->debit_amount, 2) }}</td>
                    <td class="right">{{ number_format((float) $row->credit_amount, 2) }}</td>
                    <td class="right">{{ $row->running_balance_label }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="right">No rows found.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="double-total">
                <td colspan="4">Period Total</td>
                <td class="right">{{ number_format((float) $report['period_debit'], 2) }}</td>
                <td class="right">{{ number_format((float) $report['period_credit'], 2) }}</td>
                <td class="right">{{ number_format((float) $report['closing_balance'], 2) }} {{ $report['closing_side'] }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
