@extends(adminTheme().'layouts.app')

@section('title')
<title>{{websiteTitle('Party Ledger History')}}</title>
@endsection

@section('contents')
    <div class="flex-grow-1">
        <div class="card border-0 shadow-lg text-white mb-4" style="background: linear-gradient(95deg, #0f172a 0%, #1e293b 55%, #065f46 100%);">
            <div class="card-body p-4 p-lg-5">
                <p class="small font-weight-bold text-uppercase mb-2" style="letter-spacing: .2rem; color: #bbf7d0;">Phase 4 Reports</p>
                <h1 class="h3 mb-2">Party Ledger History</h1>
                <p class="mb-0" style="color: #dcfce7;">Track full transaction history with opening balance, running balance, and closing balance by Buyer, Supplier, or Employee.</p>
            </div>
        </div>

        <form method="GET" action="{{ route('erpaccount.party-ledger.index') }}" class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="form-row align-items-end">
                    <div class="form-group col-md-2">
                        <label>Start Date</label>
                        <input type="date" name="start_date" value="{{ $filters['start_date'] }}" class="form-control">
                    </div>
                    <div class="form-group col-md-2">
                        <label>End Date</label>
                        <input type="date" name="end_date" value="{{ $filters['end_date'] }}" class="form-control">
                    </div>
                    <div class="form-group col-md-2">
                        <label>Party Type</label>
                        <select name="party_type" id="party_type_filter" class="form-control">
                            <option value="">Select Type</option>
                            <option value="Buyer" {{ $filters['party_type'] === 'Buyer' ? 'selected' : '' }}>Buyer</option>
                            <option value="Supplier" {{ $filters['party_type'] === 'Supplier' ? 'selected' : '' }}>Supplier</option>
                            <option value="Employee" {{ $filters['party_type'] === 'Employee' ? 'selected' : '' }}>Employee</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Party Ledger</label>
                        <select name="party_id" id="party_id_filter" class="form-control" data-selected="{{ $filters['party_id'] ?? '' }}">
                            <option value="">Select Party Ledger</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Account (Optional)</label>
                        <select name="account_id" class="form-control">
                            <option value="">All Party Accounts</option>
                            @foreach ($partyLedgerAccounts as $account)
                                <option value="{{ $account->account_id }}" {{ (string) $filters['account_id'] === (string) $account->account_id ? 'selected' : '' }}>
                                    {{ $account->account_code }} - {{ $account->account_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="d-flex flex-wrap justify-content-between align-items-center mt-2">
                    <div>
                        <button type="submit" class="btn btn-primary">Show Ledger</button>
                    </div>
                    <div class="d-flex flex-wrap mt-2 mt-md-0">
                        @if ($report)
                            <a class="btn btn-outline-secondary mr-2" href="{{ route('erpaccount.party-ledger.export-excel', request()->query()) }}">Excel</a>
                            <a class="btn btn-outline-secondary" target="_blank" href="{{ route('erpaccount.party-ledger.print', request()->query()) }}">Print</a>
                        @endif
                    </div>
                </div>
            </div>
        </form>

        @if (!$report)
            <div class="card shadow-sm">
                <div class="card-body text-muted">
                    Select Party Type and Party Ledger to view detailed history with running balance.
                </div>
            </div>
        @else
            <div class="row mb-3">
                <div class="col-md-3 mb-2">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="small text-muted">Opening Balance</div>
                            <div class="h5 mb-0">{{ number_format((float) $report['opening_balance'], 2) }} {{ $report['opening_side'] }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-2">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="small text-muted">Period Debit</div>
                            <div class="h5 mb-0 text-success">{{ number_format((float) $report['period_debit'], 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-2">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="small text-muted">Period Credit</div>
                            <div class="h5 mb-0 text-danger">{{ number_format((float) $report['period_credit'], 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-2">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="small text-muted">Closing Balance</div>
                            <div class="h5 mb-0">{{ number_format((float) $report['closing_balance'], 2) }} {{ $report['closing_side'] }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <strong>{{ $report['party_type'] }} Ledger:</strong>
                    {{ $report['party_name'] }} (ID: {{ $report['party_id'] }})
                    @if (!empty($report['party_ledger']))
                        <span class="text-muted">| Ledger: {{ $report['party_ledger'] }}</span>
                    @endif
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Voucher</th>
                                    <th>Account</th>
                                    <th>Narration</th>
                                    <th class="text-right">Debit</th>
                                    <th class="text-right">Credit</th>
                                    <th class="text-right">Running Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($report['rows'] as $row)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($row->journal_date)->format('Y-m-d') }}</td>
                                        <td>{{ $row->voucher_no }}</td>
                                        <td>{{ $row->account_label }}</td>
                                        <td>{{ $row->narration }}</td>
                                        <td class="text-right">{{ number_format((float) $row->debit_amount, 2) }}</td>
                                        <td class="text-right">{{ number_format((float) $row->credit_amount, 2) }}</td>
                                        <td class="text-right font-weight-bold">{{ $row->running_balance_label }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">No transactions found in selected period.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('js')
    <script>
        (function($) {
            'use strict';

            function loadPartyOptions(selectedId = '') {
                const partyType = $('#party_type_filter').val();
                const $partyId = $('#party_id_filter');

                if (!partyType) {
                    $partyId.html('<option value="">Select Party Ledger</option>');
                    return;
                }

                $partyId.html('<option value="">Loading...</option>');

                $.get("{{ route('erpaccount.journal-vouchers.party-options') }}", {
                    party_type: partyType
                }, function(response) {
                    let html = '<option value="">Select Party Ledger</option>';
                    (response.data || []).forEach(function(item) {
                        const selected = Number(selectedId) === Number(item.id) ? 'selected' : '';
                        html += `<option value="${item.id}" ${selected}>${item.display_name || item.name}</option>`;
                    });
                    $partyId.html(html);
                }).fail(function() {
                    $partyId.html('<option value="">No party found</option>');
                });
            }

            $(function() {
                loadPartyOptions($('#party_id_filter').data('selected') || '');

                $('#party_type_filter').on('change', function() {
                    loadPartyOptions('');
                });
            });
        })(jQuery);
    </script>
@endpush
