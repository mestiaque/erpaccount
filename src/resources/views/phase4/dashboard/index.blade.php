@extends(adminTheme().'layouts.app')

@section('title')
<title>{{websiteTitle('Dashboard')}}</title>
@endsection

@section('contents')
    <div class="flex-grow-1">
        <div class="bg-light" style="">
            <div class="card border-0 shadow-lg text-white mb-4" style="background: linear-gradient(95deg, #020617 0%, #1e3a8a 55%, #4338ca 100%);">
                <div class="card-body p-4 p-lg-5">
                    <div class="row g-3 align-items-end justify-content-between">
                        <div class="col-lg-8">
                            <p class="small fw-semibold text-uppercase mb-2" style="letter-spacing: .28rem; color: #bfdbfe;">Executive Command Center</p>
                            <h1 class="h2 mb-2 text-white"> Accounts Dashboard</h1>
                            <p class="mb-0" style="color: #dbeafe;">Consolidated accounting intelligence from ledgers, LC liabilities, operational cost centers, and approval queues.</p>
                        </div>
                        <div class="col-lg-auto">
                            <div class="rounded-3 px-4 py-3" style="background-color: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.25);">
                                Financial Year: <span class="fw-semibold">{{ $periodLabel }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    
            <div class="row g-3 mb-4">
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card border-success h-100 shadow-sm">
                        <div class="card-body">
                            <p class="text-uppercase small fw-semibold text-muted mb-2" style="letter-spacing: .08rem;">Net Profit / Loss (YTD)</p>
                            <h2 class="fw-bold mb-1 {{ $metrics['net_profit_loss_ytd'] >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format((float) $metrics['net_profit_loss_ytd'], 2) }}</h2>
                            <p class="text-muted mb-0 small">{{ $currency }} {{ $metrics['net_profit_loss_ytd'] >= 0 ? 'profit zone' : 'loss zone' }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card border-info h-100 shadow-sm">
                        <div class="card-body">
                            <p class="text-uppercase small fw-semibold text-muted mb-2" style="letter-spacing: .08rem;">Available Cash &amp; Bank</p>
                            <h2 class="fw-bold mb-1 text-info">{{ number_format((float) $metrics['cash_bank_balance'], 2) }}</h2>
                            <p class="text-muted mb-0 small">{{ $currency }} liquidity index</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card border-secondary h-100 shadow-sm">
                        <div class="card-body">
                            <p class="text-uppercase small fw-semibold text-muted mb-2" style="letter-spacing: .08rem;">Assets vs Liabilities</p>
                            <div class="row g-2 mb-1">
                                <div class="col-6">
                                    <div class="small text-muted">Assets</div>
                                    <div class="fw-bold fs-5">{{ number_format((float) $metrics['total_assets'], 2) }}</div>
                                </div>
                                <div class="col-6">
                                    <div class="small text-muted">Liabilities</div>
                                    <div class="fw-bold fs-5">{{ number_format((float) $metrics['total_liabilities'], 2) }}</div>
                                </div>
                            </div>
                            <p class="text-muted mb-0 small">{{ $currency }} total corporate position</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card border-warning h-100 shadow-sm">
                        <div class="card-body">
                            <p class="text-uppercase small fw-semibold text-muted mb-2" style="letter-spacing: .08rem;">Active LC Exposure</p>
                            <h2 class="fw-bold mb-1 text-warning">{{ number_format((float) $metrics['lc_exposure'], 2) }}</h2>
                            <p class="text-muted mb-0 small">{{ $currency }} outstanding liabilities</p>
                        </div>
                    </div>
                </div>
            </div>
    
            <div class="row g-4 mb-4">
                <div class="col-xl-8">
                    <div class="card border-secondary shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                                <h3 class="h5 mb-0">Income vs Operating Expenses (Last 6 Months)</h3>
                                <span class="badge bg-secondary rounded-pill">Rolling 6M</span>
                            </div>
                            <div style="height: 330px;"><canvas id="incomeExpenseChart"></canvas></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <div class="card border-secondary shadow-sm h-100">
                        <div class="card-body">
                            <h3 class="h5">Capital Structure Mix</h3>
                            <p class="text-muted small mb-3">Assets vs Liabilities vs Equity</p>
                            <div style="height: 250px;"><canvas id="capitalMixChart"></canvas></div>
                            <div class="row g-2 text-center small mt-3">
                                <div class="col-4">
                                    <div class="rounded-3 px-2 py-2 bg-primary text-white">Assets<br><strong>{{ number_format((float) $capitalMix['assets'], 2) }}</strong></div>
                                </div>
                                <div class="col-4">
                                    <div class="rounded-3 px-2 py-2 bg-warning text-dark">Liabilities<br><strong>{{ number_format((float) $capitalMix['liabilities'], 2) }}</strong></div>
                                </div>
                                <div class="col-4">
                                    <div class="rounded-3 px-2 py-2 bg-success text-white">Equity<br><strong>{{ number_format((float) $capitalMix['equity'], 2) }}</strong></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    
            <div class="row g-4 mb-4">
                <div class="col-xl-6">
                    <div class="card border-secondary shadow-sm h-100">
                        <div class="card-body">
                            <h3 class="h5 mb-1">Top 5 Style/Order Profitability</h3>
                            <p class="text-muted small mb-3">Best margin contributors from cost center linked postings</p>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Style Name</th>
                                            <th class="text-end">Revenue</th>
                                            <th class="text-end">Total Costs</th>
                                            <th class="text-end">Net Margin %</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($leaderboard as $row)
                                            <tr>
                                                <td>
                                                    <div class="fw-semibold">{{ $row->cost_center_name }}</div>
                                                    <div class="small text-muted">Ref {{ $row->reference_id }}</div>
                                                </td>
                                                <td class="text-end">{{ number_format((float) $row->revenue_total, 2) }}</td>
                                                <td class="text-end">{{ number_format((float) $row->cost_total, 2) }}</td>
                                                <td class="text-end fw-semibold {{ $row->net_margin_percent >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format((float) $row->net_margin_percent, 2) }}%</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-center text-muted py-4">No profitability data available for the current period.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
    
                <div class="col-xl-6">
                    <div class="card border-secondary shadow-sm h-100">
                        <div class="card-body">
                            <h3 class="h5 mb-1">Financial Health Ratios</h3>
                            <p class="text-muted small mb-3">Current liquidity and operating performance indicators</p>
    
                            <div class="d-grid gap-4">
                                @php
                                    $currentRatioScore = min(100, max(0, ((float) $healthRatios['current_ratio'] / 2) * 100));
                                    $operatingMarginScore = min(100, max(0, ((float) $healthRatios['operating_margin_percent'] + 20) * 2.5));
                                @endphp
                                <div>
                                    <div class="d-flex justify-content-between small mb-2">
                                        <span class="fw-semibold text-muted">Current Ratio</span>
                                        <span class="fw-semibold">{{ number_format((float) $healthRatios['current_ratio'], 2) }}</span>
                                    </div>
                                    <div class="progress" role="progressbar" aria-label="Current Ratio" aria-valuenow="{{ (int) $currentRatioScore }}" aria-valuemin="0" aria-valuemax="100" style="height: .65rem;">
                                        <div class="progress-bar bg-info" style="width: {{ $currentRatioScore }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="d-flex justify-content-between small mb-2">
                                        <span class="fw-semibold text-muted">Operating Margin %</span>
                                        <span class="fw-semibold {{ $healthRatios['operating_margin_percent'] >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format((float) $healthRatios['operating_margin_percent'], 2) }}%</span>
                                    </div>
                                    <div class="progress" role="progressbar" aria-label="Operating Margin" aria-valuenow="{{ (int) $operatingMarginScore }}" aria-valuemin="0" aria-valuemax="100" style="height: .65rem;">
                                        <div class="progress-bar {{ $healthRatios['operating_margin_percent'] >= 0 ? 'bg-success' : 'bg-danger' }}" style="width: {{ $operatingMarginScore }}%"></div>
                                    </div>
                                </div>
                            </div>
    
                            <div class="border rounded-3 bg-light px-3 py-3 mt-4 small">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="text-muted">Operating Income (YTD)</span>
                                    <strong class="{{ $healthRatios['operating_income'] >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format((float) $healthRatios['operating_income'], 2) }} {{ $currency }}</strong>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mt-1">
                                    <span class="text-muted">Net Sales (YTD)</span>
                                    <strong>{{ number_format((float) $metrics['total_revenue_ytd'], 2) }} {{ $currency }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    
            <div class="card border {{ $metrics['pending_approvals'] > 0 ? 'border-danger bg-light' : 'border-success bg-light' }} shadow-sm">
                <div class="card-body px-4 py-4">
                    <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">
                        <div>
                            <p class="text-uppercase small fw-semibold mb-1 {{ $metrics['pending_approvals'] > 0 ? 'text-danger' : 'text-success' }}" style="letter-spacing: .08rem;">Action &amp; Warning Center</p>
                            <h3 class="h5 mb-1 {{ $metrics['pending_approvals'] > 0 ? 'text-danger' : 'text-success' }}">
                                {{ $metrics['pending_approvals'] }} Pending Approval{{ $metrics['pending_approvals'] === 1 ? '' : 's' }}
                            </h3>
                            <p class="mb-0 small {{ $metrics['pending_approvals'] > 0 ? 'text-danger' : 'text-success' }}">
                                Inventory Pending: {{ $metrics['pending_inventory'] }} | Payroll Pending: {{ $metrics['pending_payroll'] }}
                            </p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('erpaccount.manual-inventory.index') }}" class="btn btn-outline-secondary btn-sm">Inventory Queue</a>
                            <a href="{{ route('erpaccount.manual-payroll.index') }}" class="btn btn-outline-secondary btn-sm">Payroll Queue</a>
                            <a href="{{ route('erpaccount.manual-inventory.index') }}" class="btn btn-dark btn-sm">Go to Approval Queue</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        (function () {
            const labels = @json($trend['labels']);
            const income = @json($trend['income']);
            const expenses = @json($trend['expense']);

            const incomeExpenseCanvas = document.getElementById('incomeExpenseChart');
            if (incomeExpenseCanvas) {
                new Chart(incomeExpenseCanvas, {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [
                            {
                                label: 'Total Income',
                                data: income,
                                borderColor: '#2563eb',
                                backgroundColor: 'rgba(37,99,235,0.12)',
                                borderWidth: 3,
                                tension: 0.35,
                                fill: true,
                            },
                            {
                                label: 'Operating Expenses',
                                data: expenses,
                                borderColor: '#f97316',
                                backgroundColor: 'rgba(249,115,22,0.12)',
                                borderWidth: 3,
                                tension: 0.35,
                                fill: true,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: { duration: 900, easing: 'easeOutQuart' },
                        interaction: { mode: 'index', intersect: false },
                        plugins: { legend: { position: 'bottom' } },
                        scales: {
                            y: {
                                ticks: {
                                    callback: function(value) { return value.toLocaleString(); }
                                }
                            }
                        }
                    }
                });
            }

            const mixCanvas = document.getElementById('capitalMixChart');
            if (mixCanvas) {
                new Chart(mixCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: ['Assets', 'Liabilities', 'Equity'],
                        datasets: [{
                            data: [
                                Number(@json((float) $capitalMix['assets'])),
                                Number(@json((float) $capitalMix['liabilities'])),
                                Number(@json((float) $capitalMix['equity']))
                            ],
                            backgroundColor: ['#2563eb', '#f97316', '#16a34a'],
                            borderWidth: 0,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: { duration: 900, easing: 'easeOutQuart' },
                        cutout: '62%',
                        plugins: { legend: { position: 'bottom' } }
                    }
                });
            }
        })();
    </script>
@endpush
