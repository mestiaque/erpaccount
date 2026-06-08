@extends(adminTheme().'layouts.app')

@section('title')
<title>{{websiteTitle('Commercial LC Tracker')}}</title>
@endsection


@section('contents')
    <div class="flex-grow-1">
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-muted small mb-1">LC Margin Utilization</p>
                        @php
                            $marginLimit = max((float) $summary['total_margin_limit'], 1);
                            $marginUsed = (float) $summary['total_margin_used'];
                            $marginPercent = min(100, ($marginUsed / $marginLimit) * 100);
                        @endphp
                        <h4 class="mb-2">{{ number_format($marginUsed, 2) }} / {{ number_format($marginLimit, 2) }}</h4>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" style="width: {{ $marginPercent }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Bank Commissions & Acceptance Costs Paid</p>
                        <h4 class="mb-2">{{ number_format((float) $summary['total_commission_paid'] + (float) $summary['total_acceptance_cost'], 2) }}</h4>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-info" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Outstanding Liability</p>
                        <h4 class="mb-2">{{ number_format((float) $summary['total_liability'], 2) }}</h4>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-warning" style="width: {{ min(100, ((float) $summary['total_liability'] / max((float) $summary['total_margin_limit'], 1)) * 100) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <strong>Export Master LCs</strong>
                        <span class="badge badge-primary">{{ $masterLcs->count() }}</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>LC Ref</th>
                                        <th>Value</th>
                                        <th>Margin Used</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($masterLcs as $lc)
                                        <tr>
                                            <td>{{ $lc->lc_id_reference }}</td>
                                            <td>{{ $lc->currency }} {{ number_format((float) $lc->total_lc_value, 2) }}</td>
                                            <td>{{ number_format((float) $lc->bank_margin_used, 2) }}</td>
                                            <td>
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-info lc-view-btn"
                                                    data-toggle="modal"
                                                    data-target="#lcDetailsModal"
                                                    data-lc-ref="{{ $lc->lc_id_reference }}"
                                                    data-lc-type="{{ $lc->lc_type }}"
                                                    data-lc-value="{{ number_format((float) $lc->total_lc_value, 2, '.', '') }}"
                                                    data-margin-used="{{ number_format((float) $lc->bank_margin_used, 2, '.', '') }}"
                                                    data-commission="{{ number_format((float) $lc->bank_commission_paid, 2, '.', '') }}"
                                                    data-acceptance="{{ number_format((float) $lc->acceptance_cost_paid, 2, '.', '') }}"
                                                    data-clearing="{{ number_format((float) $lc->customs_clearing_cost, 2, '.', '') }}"
                                                    data-freight="{{ number_format((float) $lc->freight_cost, 2, '.', '') }}"
                                                    data-liability="{{ number_format((float) $lc->outstanding_liability, 2, '.', '') }}">
                                                    View Ledger Bills
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted py-4">No Master LC records found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <strong>Import Back-to-Back LCs</strong>
                        <span class="badge badge-primary">{{ $b2bLcs->count() }}</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>LC Ref</th>
                                        <th>Value</th>
                                        <th>Freight</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($b2bLcs as $lc)
                                        <tr>
                                            <td>{{ $lc->lc_id_reference }}</td>
                                            <td>{{ $lc->currency }} {{ number_format((float) $lc->total_lc_value, 2) }}</td>
                                            <td>{{ number_format((float) $lc->freight_cost, 2) }}</td>
                                            <td>
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-info lc-view-btn"
                                                    data-toggle="modal"
                                                    data-target="#lcDetailsModal"
                                                    data-lc-ref="{{ $lc->lc_id_reference }}"
                                                    data-lc-type="{{ $lc->lc_type }}"
                                                    data-lc-value="{{ number_format((float) $lc->total_lc_value, 2, '.', '') }}"
                                                    data-margin-used="{{ number_format((float) $lc->bank_margin_used, 2, '.', '') }}"
                                                    data-commission="{{ number_format((float) $lc->bank_commission_paid, 2, '.', '') }}"
                                                    data-acceptance="{{ number_format((float) $lc->acceptance_cost_paid, 2, '.', '') }}"
                                                    data-clearing="{{ number_format((float) $lc->customs_clearing_cost, 2, '.', '') }}"
                                                    data-freight="{{ number_format((float) $lc->freight_cost, 2, '.', '') }}"
                                                    data-liability="{{ number_format((float) $lc->outstanding_liability, 2, '.', '') }}">
                                                    View Ledger Bills
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted py-4">No B2B LC records found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
        <div class="modal fade" id="lcDetailsModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">LC Ledger Snapshot</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-1"><strong>LC Ref:</strong> <span id="lcModalRef"></span></p>
                        <p class="mb-1"><strong>Type:</strong> <span id="lcModalType"></span></p>
                        <p class="mb-1"><strong>Total Value:</strong> <span id="lcModalValue"></span></p>
                        <hr>
                        <p class="mb-1"><strong>Margin Used:</strong> <span id="lcModalMargin"></span></p>
                        <p class="mb-1"><strong>Bank Commission:</strong> <span id="lcModalCommission"></span></p>
                        <p class="mb-1"><strong>Acceptance Cost:</strong> <span id="lcModalAcceptance"></span></p>
                        <p class="mb-1"><strong>Clearing Cost:</strong> <span id="lcModalClearing"></span></p>
                        <p class="mb-1"><strong>Freight Cost:</strong> <span id="lcModalFreight"></span></p>
                        <p class="mb-0"><strong>Outstanding Liability:</strong> <span id="lcModalLiability"></span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        (function($) {
            'use strict';

            $(function() {
                $('.lc-view-btn').on('click', function() {
                    $('#lcModalRef').text($(this).data('lc-ref'));
                    $('#lcModalType').text($(this).data('lc-type').replace(/_/g, ' '));
                    $('#lcModalValue').text($(this).data('lc-value'));
                    $('#lcModalMargin').text($(this).data('margin-used'));
                    $('#lcModalCommission').text($(this).data('commission'));
                    $('#lcModalAcceptance').text($(this).data('acceptance'));
                    $('#lcModalClearing').text($(this).data('clearing'));
                    $('#lcModalFreight').text($(this).data('freight'));
                    $('#lcModalLiability').text($(this).data('liability'));
                });
            });
        })(jQuery);
    </script>
@endpush
