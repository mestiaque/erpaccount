<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">
<head>
    <meta charset="UTF-8">
</head>
<body>
    <table border="1">
        <tr><th colspan="{{ count($report['columns']) }}">{{ $report['title'] }}</th></tr>
        <tr><th colspan="{{ count($report['columns']) }}">{{ $report['subtitle'] }}</th></tr>
        <tr>
            @foreach ($report['columns'] as $column)
                <th>{{ $column['label'] }}</th>
            @endforeach
        </tr>
        @foreach ($report['rows'] as $row)
            <tr>
                @foreach ($report['columns'] as $column)
                    @php $value = data_get($row, $column['key']); @endphp
                    <td>{{ is_numeric($value) && ($column['format'] ?? '') === 'money' ? number_format((float) $value, 2, '.', '') : $value }}</td>
                @endforeach
            </tr>
        @endforeach
        @if (!empty($report['summary']))
            <tr><td colspan="{{ count($report['columns']) }}"></td></tr>
            @foreach ($report['summary'] as $label => $value)
                <tr>
                    <td colspan="{{ max(1, count($report['columns']) - 1) }}"><strong>{{ $label }}</strong></td>
                    <td>{{ $value }}</td>
                </tr>
            @endforeach
        @endif
    </table>
</body>
</html>
