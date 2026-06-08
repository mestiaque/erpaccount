<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $report['title'] }} - PDF</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; color: #111; font-size: 12px; }
        h1 { font-size: 18px; margin: 0 0 4px; }
        .meta { color: #555; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; }
        th { background: #f3f4f6; }
        .right { text-align: right; }
        .notes { margin: 12px 0; padding: 8px; background: #eff6ff; border-left: 4px solid #3b82f6; }
        .summary { margin-top: 16px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">
    <button class="no-print" onclick="window.print()">Print / Save as PDF</button>
    <h1>{{ $report['title'] }}</h1>
    <div class="meta">{{ $report['subtitle'] }} | Generated: {{ now()->format('d M Y H:i') }}</div>

    @if (!empty($report['notes']))
        @foreach ($report['notes'] as $note)
            <div class="notes">{{ $note }}</div>
        @endforeach
    @endif

    <table>
        <thead>
            <tr>
                @foreach ($report['columns'] as $column)
                    <th class="{{ ($column['align'] ?? '') === 'right' ? 'right' : '' }}">{{ $column['label'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($report['rows'] as $row)
                <tr>
                    @foreach ($report['columns'] as $column)
                        @php
                            $value = data_get($row, $column['key']);
                            if (($column['format'] ?? null) === 'money' && is_numeric($value)) {
                                $value = number_format((float) $value, 2);
                            }
                        @endphp
                        <td class="{{ ($column['align'] ?? '') === 'right' ? 'right' : '' }}">{{ $value }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    @if (!empty($report['summary']))
        <div class="summary">
            <h3>Summary</h3>
            @foreach ($report['summary'] as $label => $value)
                <div><strong>{{ $label }}:</strong> {{ $value }}</div>
            @endforeach
        </div>
    @endif
</body>
</html>
