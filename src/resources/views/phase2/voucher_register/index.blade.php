@extends(adminTheme().'layouts.app')

@section('title')
<title>{{websiteTitle('Voucher Register')}}</title>
@endsection

@section('contents')
    <div class="flex-grow-1">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-start mb-4">
                    <div>
                        <h2 class="h3 mb-1">Voucher Register</h2>
                        <p class="text-muted mb-0">Search, review, and void posted journal vouchers.</p>
                    </div>
                    <a href="{{ route('erpaccount.journal-vouchers.index') }}" class="btn btn-primary">
                        <i class="fa fa-plus mr-1"></i> New Journal Voucher
                    </a>
                </div>

                <form method="GET" class="mb-4">
                    <div class="form-row">
                        <div class="form-group col-md-2">
                            <label>Voucher No</label>
                            <input type="text" name="voucher_no" class="form-control" value="{{ $filters['voucher_no'] ?? '' }}">
                        </div>
                        <div class="form-group col-md-2">
                            <label>Source</label>
                            <select name="source_module" class="form-control">
                                <option value="">All</option>
                                @foreach ($sourceModules as $module)
                                    <option value="{{ $module }}" @selected(($filters['source_module'] ?? '') === $module)>{{ $module }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="">All</option>
                                <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                                <option value="voided" @selected(($filters['status'] ?? '') === 'voided')>Voided</option>
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            <label>From</label>
                            <input type="date" name="start_date" class="form-control" value="{{ $filters['start_date'] ?? '' }}">
                        </div>
                        <div class="form-group col-md-2">
                            <label>To</label>
                            <input type="date" name="end_date" class="form-control" value="{{ $filters['end_date'] ?? '' }}">
                        </div>
                        <div class="form-group col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-outline-primary btn-block">Filter</button>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Voucher No</th>
                                <th>Date</th>
                                <th>Source</th>
                                <th class="text-right">Debit</th>
                                <th class="text-right">Credit</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($vouchers as $voucher)
                                <tr>
                                    <td>{{ $voucher->voucher_no }}</td>
                                    <td>{{ $voucher->journal_date?->format('Y-m-d') }}</td>
                                    <td>{{ $voucher->source_module }}</td>
                                    <td class="text-right">{{ number_format((float) $voucher->total_debit, 2) }}</td>
                                    <td class="text-right">{{ number_format((float) $voucher->total_credit, 2) }}</td>
                                    <td>
                                        @if ($voucher->is_voided)
                                            <span class="badge badge-danger">Voided</span>
                                        @else
                                            <span class="badge badge-success">Active</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('erpaccount.voucher-register.show', $voucher) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                        @if (!$voucher->is_voided)
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-danger voucher-void-btn"
                                                data-toggle="modal"
                                                data-target="#voidVoucherModal"
                                                data-voucher-id="{{ $voucher->journal_id }}"
                                                data-voucher-no="{{ $voucher->voucher_no }}"
                                            >
                                                Void
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-muted text-center">No vouchers found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $vouchers->links() }}
            </div>
        </div>

        <div class="modal fade" id="voidVoucherModal" tabindex="-1" role="dialog" aria-labelledby="voidVoucherModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="voidVoucherModalLabel">Void Voucher</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="voidVoucherForm" method="POST" action="#">
                        @csrf
                        @method('PATCH')

                        <div class="modal-body">
                            <p class="mb-2">You are about to void voucher: <strong id="voidVoucherNo"></strong></p>
                            <div class="form-group mb-0">
                                <label for="void_reason">Reason</label>
                                <input
                                    type="text"
                                    id="void_reason"
                                    name="void_reason"
                                    class="form-control"
                                    maxlength="255"
                                    required
                                    placeholder="Why is this voucher being voided?"
                                >
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Void Voucher</button>
                        </div>
                    </form>
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
                $(document).on('click', '.voucher-void-btn', function() {
                    const voucherId = $(this).data('voucher-id');
                    const voucherNo = $(this).data('voucher-no');

                    $('#voidVoucherNo').text(voucherNo);
                    $('#void_reason').val('');
                    $('#voidVoucherForm').attr('action', `{{ url('/erpaccount/voucher-register') }}/${voucherId}/void`);
                });
            });
        })(jQuery);
    </script>
@endpush
