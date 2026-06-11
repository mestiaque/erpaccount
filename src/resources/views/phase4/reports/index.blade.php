@extends(adminTheme().'layouts.app')

@section('title')
<title>{{websiteTitle('Financial Reports Generator')}}</title>
@endsection

@section('contents')
    <div class="flex-grow-1">
        <div class="bg-light" >
            <div class="card border-0 shadow-lg text-white mb-4" style="background: linear-gradient(95deg, #020617 0%, #1e293b 55%, #1d4ed8 100%);">
                <div class="card-body p-4 p-lg-5">
                    <p class="small fw-semibold text-uppercase mb-2" style="letter-spacing: .28rem; color: #bfdbfe;">Reports</p>
                    <h1 class="h2 mb-2 text-white">Financial Statement Builder</h1>
                    <p class="mb-0" style="color: #dbeafe;">Generate Trial Balance, Profit &amp; Loss, or Balance Sheet with date-range filters and export actions.</p>
                </div>
            </div>

            <form method="GET" action="{{ route('erpaccount.financial-reports.index') }}" class="card border-secondary shadow-sm mb-4">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-xl-2 col-md-6">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" value="{{ $filters['start_date'] }}" class="form-control">
                        </div>
                        <div class="col-xl-2 col-md-6">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" value="{{ $filters['end_date'] }}" class="form-control">
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <label class="form-label">Report Type</label>
                            <select name="report_type" class="form-select">
                                <option value="trial_balance" @selected($filters['report_type'] === 'trial_balance')>Trial Balance</option>
                                <option value="profit_and_loss" @selected($filters['report_type'] === 'profit_and_loss')>Profit &amp; Loss</option>
                                <option value="balance_sheet" @selected($filters['report_type'] === 'balance_sheet')>Balance Sheet</option>
                            </select>
                        </div>
                        <div class="col-xl-2 col-md-6 d-grid">
                            <button type="submit" class="btn btn-primary">Generate Report</button>
                        </div>
                        <div class="col-xl-1 col-md-6 d-grid">
                            <a href="{{ route('erpaccount.financial-reports.export-excel', $filters) }}" class="btn btn-outline-secondary">
                                Excel
                            </a>
                        </div>
                        <div class="col-xl-2 col-md-6 d-grid">
                            <a href="{{ route('erpaccount.financial-reports.print', $filters) }}" target="_blank" class="btn btn-outline-secondary">
                                PDF
                            </a>
                        </div>
                    </div>
                </div>
            </form>

            <div class="card border-secondary shadow-sm">
                <div class="card-body">
                @if ($reportType === 'trial_balance')
                    <h3 class="h5 mb-3">Trial Balance</h3>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Account</th>
                                    <th>Type</th>
                                    <th class="text-end">Period Debit</th>
                                    <th class="text-end">Period Credit</th>
                                    <th class="text-end">Closing Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($report['rows'] as $row)
                                    <tr>
                                        <td>{{ $row->account_code }}</td>
                                        <td>{{ $row->account_name }}</td>
                                        <td>{{ $row->account_type }}</td>
                                        <td class="text-end">{{ number_format((float) $row->period_debit, 2) }}</td>
                                        <td class="text-end">{{ number_format((float) $row->period_credit, 2) }}</td>
                                        <td class="text-end">{{ number_format((float) $row->closing_balance, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-active fw-bold">
                                    <td colspan="3">Grand Total</td>
                                    <td class="text-end">{{ number_format((float) $report['total_debits'], 2) }}</td>
                                    <td class="text-end">{{ number_format((float) $report['total_credits'], 2) }}</td>
                                    <td class="text-end">{{ $report['is_balanced'] ? 'Balanced' : 'Not Balanced' }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif

                @if ($reportType === 'profit_and_loss')
                    <h3 class="h5 mb-3">Profit &amp; Loss Statement</h3>
                    <div class="row g-4">
                        <div class="col-lg-6">
                            <h4 class="small fw-semibold text-uppercase text-success mb-2" style="letter-spacing: .07rem;">Revenue</h4>
                            <div class="border rounded-3 p-3">
                                @forelse ($report['revenue_rows'] as $row)
                                    <div class="d-flex align-items-center justify-content-between small py-1">
                                        <span>{{ $row->account_name }}</span>
                                        <span class="fw-semibold">{{ number_format((float) $row->amount, 2) }}</span>
                                    </div>
                                @empty
                                    <div class="small text-muted">No revenue entries in selected period.</div>
                                @endforelse
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <h4 class="small fw-semibold text-uppercase text-danger mb-2" style="letter-spacing: .07rem;">Expenses</h4>
                            <div class="border rounded-3 p-3">
                                @forelse ($report['expense_rows'] as $row)
                                    <div class="d-flex align-items-center justify-content-between small py-1">
                                        <span>{{ $row->account_name }}</span>
                                        <span class="fw-semibold">{{ number_format((float) $row->amount, 2) }}</span>
                                    </div>
                                @empty
                                    <div class="small text-muted">No expense entries in selected period.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 border rounded-3 bg-light p-3">
                        <div class="d-flex align-items-center justify-content-between py-1 small"><span>Total Revenue</span><strong>{{ number_format((float) $report['total_revenue'], 2) }} {{ $currency }}</strong></div>
                        <div class="d-flex align-items-center justify-content-between py-1 small"><span>Total Expenses</span><strong>{{ number_format((float) $report['total_expenses'], 2) }} {{ $currency }}</strong></div>
                        <div class="mt-2 border-top pt-2 fw-bold {{ $report['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                            Net Profit: {{ number_format((float) $report['net_profit'], 2) }} {{ $currency }}
                        </div>
                    </div>
                @endif

                @if ($reportType === 'balance_sheet')
                    <h3 class="h5 mb-3">Balance Sheet</h3>
                    <div class="row g-4">
                        <div class="col-lg-6">
                            <h4 class="small fw-semibold text-uppercase text-primary mb-2" style="letter-spacing: .07rem;">Assets</h4>
                            <div class="border rounded-3 p-3">
                                @php $assetGroup = null; @endphp
                                @foreach ($report['assets'] as $row)
                                    @if ($assetGroup !== $row->balance_group)
                                        @php $assetGroup = $row->balance_group; @endphp
                                        <div class="mt-3 border-bottom pb-1 small fw-bold text-uppercase text-muted" style="letter-spacing: .05rem;">{{ $assetGroup }}</div>
                                    @endif
                                    <div class="d-flex align-items-center justify-content-between py-1 small">
                                        <span>{{ $row->account_name }}</span>
                                        <span class="fw-semibold">{{ number_format((float) $row->amount, 2) }}</span>
                                    </div>
                                @endforeach
                                <div class="mt-3 border-top pt-2 small fw-bold">Total Assets: {{ number_format((float) $report['total_assets'], 2) }} {{ $currency }}</div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <h4 class="small fw-semibold text-uppercase text-indigo mb-2" style="letter-spacing: .07rem; color: #4338ca;">Liabilities + Equity</h4>
                            <div class="border rounded-3 p-3">
                                @php $leGroup = null; @endphp
                                @foreach ($report['liabilities_equity'] as $row)
                                    @if ($leGroup !== $row->balance_group)
                                        @php $leGroup = $row->balance_group; @endphp
                                        <div class="mt-3 border-bottom pb-1 small fw-bold text-uppercase text-muted" style="letter-spacing: .05rem;">{{ $leGroup }}</div>
                                    @endif
                                    <div class="d-flex align-items-center justify-content-between py-1 small">
                                        <span>{{ $row->account_name }}</span>
                                        <span class="fw-semibold">{{ number_format((float) $row->amount, 2) }}</span>
                                    </div>
                                @endforeach
                                <div class="mt-3 border-top pt-2 small fw-bold">Total Liabilities + Equity: {{ number_format((float) $report['total_liabilities_equity'], 2) }} {{ $currency }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 alert {{ $report['is_balanced'] ? 'alert-success' : 'alert-danger' }} mb-0 py-2">
                        {{ $report['is_balanced'] ? 'Balance Sheet is perfectly balanced.' : 'Balance Sheet mismatch detected.' }}
                    </div>
                @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
@endpush
