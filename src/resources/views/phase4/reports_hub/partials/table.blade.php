<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        @foreach ($report['columns'] as $column)
                            <th class="{{ ($column['align'] ?? '') === 'right' ? 'text-right' : '' }}">{{ $column['label'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($report['rows'] as $row)
                        <tr>
                            @foreach ($report['columns'] as $column)
                                @php
                                    $value = data_get($row, $column['key']);
                                    if (($column['format'] ?? null) === 'money' && is_numeric($value)) {
                                        $value = number_format((float) $value, 2);
                                    }
                                @endphp
                                <td class="{{ ($column['align'] ?? '') === 'right' ? 'text-right' : '' }}">{{ $value }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($report['columns']) }}" class="text-center text-muted py-4">No data for selected filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if (!empty($report['summary']))
        <div class="card-footer bg-light">
            @foreach ($report['summary'] as $label => $value)
                <div class="d-flex justify-content-between small py-1">
                    <span>{{ $label }}</span>
                    <strong>{{ $value }}</strong>
                </div>
            @endforeach
        </div>
    @endif
</div>
