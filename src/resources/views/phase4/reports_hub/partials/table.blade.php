<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            @if (($report['render_type'] ?? '') === 'comparative')
                {{-- ── Grouped Comparative Table ── --}}
                <table class="table table-sm mb-0" style="font-size:0.82rem;">
                    <thead style="background:#1e3a5f; color:#fff; position:sticky; top:0; z-index:1;">
                        <tr>
                            <th style="width:14%">Account Type</th>
                            <th style="width:22%">Account Group</th>
                            <th>Account Title</th>
                            <th class="text-right" style="width:13%">{{ $report['curr_label'] ?? 'Current' }}</th>
                            <th class="text-right" style="width:13%">{{ $report['prev_label'] ?? 'Previous' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $lastType = null; $lastGroup = null; @endphp
                        @forelse ($report['rows'] as $row)
                            @php $rt = $row->_row_type ?? 'data'; @endphp

                            @if ($rt === 'type_header')
                                <tr style="background:#0f172a; color:#f1f5f9;">
                                    <td colspan="5" class="font-weight-bold py-2 pl-3" style="letter-spacing:.04em; font-size:.78rem; text-transform:uppercase;">
                                        <i class="fa fa-layer-group mr-1"></i> {{ $row->label }}
                                    </td>
                                </tr>
                                @php $lastType = $row->label; $lastGroup = null; @endphp

                            @elseif ($rt === 'group_header')
                                <tr style="background:#e2e8f0;">
                                    <td></td>
                                    <td colspan="4" class="font-weight-bold text-dark small py-1 pl-2">
                                        <i class="fa fa-folder-open mr-1 text-muted"></i> {{ $row->label }}
                                    </td>
                                </tr>
                                @php $lastGroup = $row->label; @endphp

                            @elseif ($rt === 'group_total')
                                <tr style="background:#f0fdf4; border-top:1px solid #bbf7d0;">
                                    <td></td>
                                    <td colspan="2" class="text-muted small font-weight-bold pl-3">{{ $row->label }}</td>
                                    <td class="text-right font-weight-bold" style="color:#166534;">
                                        {{ $row->bal_curr !== null ? number_format($row->bal_curr, 0) : '' }}
                                    </td>
                                    <td class="text-right font-weight-bold" style="color:#1e40af;">
                                        {{ $row->bal_prev !== null ? number_format($row->bal_prev, 0) : '' }}
                                    </td>
                                </tr>

                            @elseif ($rt === 'type_total')
                                <tr style="background:#1e3a5f; color:#f1f5f9; border-top:2px solid #0f172a;">
                                    <td colspan="3" class="font-weight-bold pl-3 py-2">{{ $row->label }}</td>
                                    <td class="text-right font-weight-bold py-2">
                                        {{ $row->bal_curr !== null ? number_format($row->bal_curr, 0) : '' }}
                                    </td>
                                    <td class="text-right font-weight-bold py-2">
                                        {{ $row->bal_prev !== null ? number_format($row->bal_prev, 0) : '' }}
                                    </td>
                                </tr>
                                <tr><td colspan="5" style="height:6px; background:#f8fafc;"></td></tr>

                            @else
                                @php
                                    $currFmt = $row->bal_curr ? number_format($row->bal_curr, 0) : '-';
                                    $prevFmt = $row->bal_prev ? number_format($row->bal_prev, 0) : '-';
                                    $changed = abs(($row->bal_curr ?? 0) - ($row->bal_prev ?? 0)) > 0.009 && ($row->bal_prev ?? 0) != 0;
                                @endphp
                                <tr class="{{ $changed ? 'table-warning' : '' }}" style="background:#fff;">
                                    <td class="text-muted small"></td>
                                    <td class="text-muted small"></td>
                                    <td class="pl-4 small" style="color:#334155;">{{ $row->account_name }}</td>
                                    <td class="text-right small" style="color:#166534; font-weight:{{ $row->bal_curr ? '600' : '400' }};">
                                        {{ $currFmt }}
                                    </td>
                                    <td class="text-right small" style="color:#1e40af; font-weight:{{ $row->bal_prev ? '600' : '400' }};">
                                        {{ $prevFmt }}
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No data for selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @else
                {{-- ── Standard Flat Table ── --}}
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
            @endif
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
