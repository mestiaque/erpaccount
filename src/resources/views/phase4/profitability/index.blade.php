@extends(adminTheme().'layouts.app')

@section('title')
<title>{{websiteTitle('Style & Order Profitability Monitor')}}</title>
@endsection

@section('contents')
    <div class="flex-grow-1">
        <div class="bg-light" >
            <div class="card border-0 shadow-lg text-white mb-4" style="background: linear-gradient(95deg, #020617 0%, #312e81 55%, #047857 100%);">
                <div class="card-body p-4 p-lg-5">
                    <p class="small fw-semibold text-uppercase mb-2" style="letter-spacing: .28rem; color: #a7f3d0;">Profitability</p>
                    <h1 class="h2 mb-2 text-white">Style &amp; Order Profitability Monitor</h1>
                    <p class="mb-0" style="color: #ccfbf1;">Analyze one style/job order at a time by cost center with real journal-level cost and revenue classification.</p>
                </div>
            </div>

            <form method="GET" action="{{ route('erpaccount.style-profitability.index') }}" class="card border-secondary shadow-sm mb-4">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-9">
                            <label class="form-label">Select Style / Job Order (Cost Center)</label>
                            <select name="cost_center_id" class="form-select">
                                <option value="">Choose a cost center</option>
                                @foreach ($costCenters as $center)
                                    <option value="{{ $center->cost_center_id }}" @selected($selectedCostCenter && $selectedCostCenter->cost_center_id === $center->cost_center_id)>
                                        {{ $center->cost_center_name }} ({{ $center->reference_id }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-grid">
                            <button type="submit" class="btn btn-success">Generate Profitability</button>
                        </div>
                    </div>
                </div>
            </form>

            @if ($selectedCostCenter)
                <div class="row g-4">
                    <div class="col-xl-7">
                        <div class="card border-secondary shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-start justify-content-between mb-3 gap-2 flex-wrap">
                                    <div>
                                        <h3 class="h5 mb-1">Profitability Summary</h3>
                                        <p class="small text-muted mb-0">{{ $selectedCostCenter->cost_center_name }} (Ref {{ $selectedCostCenter->reference_id }})</p>
                                    </div>
                                    <span class="badge rounded-pill {{ $metrics['gross_profit'] >= 0 ? 'bg-success' : 'bg-danger' }}">
                                        {{ $metrics['gross_profit'] >= 0 ? 'Profitable' : 'Loss Position' }}
                                    </span>
                                </div>

                                <div class="row g-2">
                                    <div class="col-sm-6">
                                        <div class="border rounded-3 p-3 h-100">
                                            <div class="small text-uppercase text-muted fw-semibold" style="letter-spacing: .06rem;">Total Export Revenue</div>
                                            <div class="fs-5 fw-semibold mt-1">{{ number_format((float) $metrics['export_revenue'], 2) }} {{ $currency }}</div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="border rounded-3 p-3 h-100">
                                            <div class="small text-uppercase text-muted fw-semibold" style="letter-spacing: .06rem;">Fabric Cost</div>
                                            <div class="fs-5 fw-semibold mt-1">{{ number_format((float) $metrics['fabric_cost'], 2) }} {{ $currency }}</div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="border rounded-3 p-3 h-100">
                                            <div class="small text-uppercase text-muted fw-semibold" style="letter-spacing: .06rem;">Accessories &amp; Trims</div>
                                            <div class="fs-5 fw-semibold mt-1">{{ number_format((float) $metrics['accessories_cost'], 2) }} {{ $currency }}</div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="border rounded-3 p-3 h-100">
                                            <div class="small text-uppercase text-muted fw-semibold" style="letter-spacing: .06rem;">CM / Labor Cost</div>
                                            <div class="fs-5 fw-semibold mt-1">{{ number_format((float) $metrics['cm_labor_cost'], 2) }} {{ $currency }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="border rounded-3 bg-light p-3 mt-3">
                                    <div class="d-flex justify-content-between small">
                                        <span class="text-muted">Total Cost</span>
                                        <strong>{{ number_format((float) $metrics['total_cost'], 2) }} {{ $currency }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between small mt-2">
                                        <span class="text-muted">Gross Profit</span>
                                        <strong class="{{ $metrics['gross_profit'] >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format((float) $metrics['gross_profit'], 2) }} {{ $currency }}</strong>
                                    </div>
                                    <div class="border-top pt-2 mt-2 small fw-semibold">
                                        Net Order Profit Margin:
                                        <span class="ms-1 {{ $metrics['gross_margin_percent'] >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format((float) $metrics['gross_margin_percent'], 2) }}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-5">
                        <div class="card border-secondary shadow-sm mb-4">
                            <div class="card-body">
                                <h3 class="h5 mb-3">Cost Composition</h3>
                                <div style="height: 250px;"><canvas id="profitMixChart"></canvas></div>
                            </div>
                        </div>

                        <div class="card border-secondary shadow-sm">
                            <div class="card-body">
                                <h3 class="h5 mb-3">Recent Style Ledger Lines</h3>
                                <div class="table-responsive" style="max-height: 250px;">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Voucher</th>
                                                <th>Account</th>
                                                <th class="text-end">Net</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($recentLines as $line)
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::parse($line->journal_date)->format('d M Y') }}</td>
                                                    <td>{{ $line->voucher_no }}</td>
                                                    <td>{{ $line->account_name }}</td>
                                                    <td class="text-end">{{ number_format((float) $line->debit_amount - (float) $line->credit_amount, 2) }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="4" class="text-center text-muted py-4">No journal lines for selected cost center.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="card border-secondary shadow-sm">
                    <div class="card-body py-5 text-center text-muted">
                        Select a style/order to generate profitability instantly.
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        (function () {
            const canvas = document.getElementById('profitMixChart');
            if (!canvas) return;

            const values = [
                Number(@json((float) $metrics['fabric_cost'])),
                Number(@json((float) $metrics['accessories_cost'])),
                Number(@json((float) $metrics['cm_labor_cost'])),
            ];

            new Chart(canvas, {
                type: 'doughnut',
                data: {
                    labels: ['Fabric Cost', 'Accessories & Trims', 'CM / Labor'],
                    datasets: [{
                        data: values,
                        backgroundColor: ['#0ea5e9', '#8b5cf6', '#f97316'],
                        borderWidth: 0,
                    }]
                },
                options: {
                    plugins: { legend: { position: 'bottom' } },
                    cutout: '65%',
                }
            });
        })();
    </script>
@endpush
