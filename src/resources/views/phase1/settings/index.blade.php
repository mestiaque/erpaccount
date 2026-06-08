@extends(adminTheme().'layouts.app')

@section('title')
<title>{{websiteTitle('Settings')}}</title>
@endsection

@section('contents')
<div class="flex-grow-1">
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h4 mb-1">Tax / VAT Configuration</h2>
                    <p class="text-muted mb-4">Define dynamic tax percentages and map each tax to a liability or expense ledger.</p>

                    <form action="{{ route('erpaccount.tax-rates.store') }}" method="POST" class="border rounded p-3 mb-4 bg-light">
                        @csrf

                        <div class="form-group">
                            <label for="tax_name">Tax Name</label>
                            <input type="text" id="tax_name" name="tax_name" required value="{{ old('tax_name') }}" class="form-control" placeholder="VAT 5% / Source Tax 2%">
                        </div>

                        <div class="form-group">
                            <label for="percentage">Percentage</label>
                            <input type="number" id="percentage" name="percentage" min="0" max="100" step="0.01" required value="{{ old('percentage') }}" class="form-control" placeholder="0.00">
                        </div>

                        <div class="form-group">
                            <label for="ledger_account_id">Ledger Account</label>
                            <select id="ledger_account_id" name="ledger_account_id" required class="form-control">
                                <option value="">Select Liability/Expense Ledger</option>
                                @foreach ($ledgerAccounts as $ledger)
                                    <option value="{{ $ledger->account_id }}" {{ (string) old('ledger_account_id') === (string) $ledger->account_id ? 'selected' : '' }}>
                                        {{ $ledger->account_type }} | {{ $ledger->account_code }} - {{ $ledger->account_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="custom-control custom-checkbox mb-3">
                            <input type="checkbox" class="custom-control-input" id="tax_is_active" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                            <label class="custom-control-label" for="tax_is_active">Active</label>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">Create Tax Rate</button>
                    </form>

                    <div class="table-responsive border rounded">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Tax</th>
                                    <th>Rate</th>
                                    <th>Ledger</th>
                                    <th>Status</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($taxRates as $tax)
                                    <tr>
                                        <td class="font-weight-bold">{{ $tax->tax_name }}</td>
                                        <td>{{ number_format((float) $tax->percentage, 2) }}%</td>
                                        <td>{{ optional($tax->chartOfAccount)->account_code }} - {{ optional($tax->chartOfAccount)->account_name }}</td>
                                        <td>
                                            <span class="badge {{ $tax->is_active ? 'badge-success' : 'badge-secondary' }}">
                                                {{ $tax->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="text-right">
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-primary tax-edit-btn"
                                                data-toggle="modal"
                                                data-target="#editTaxModal"
                                                data-tax-id="{{ $tax->tax_rate_id }}"
                                                data-tax-name="{{ $tax->tax_name }}"
                                                data-percentage="{{ $tax->percentage }}"
                                                data-ledger-account-id="{{ $tax->ledger_account_id }}"
                                                data-is-active="{{ $tax->is_active ? 1 : 0 }}"
                                            >
                                                Edit
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-muted text-center">No tax rates configured yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h4 mb-1">Financial Period Setup</h2>
                    <p class="text-muted mb-4">Define fiscal windows and lock or unlock periods instantly using the status switch.</p>

                    <form action="{{ route('erpaccount.financial-periods.store') }}" method="POST" class="border rounded p-3 mb-4 bg-light">
                        @csrf

                        <div class="form-group">
                            <label for="period_name">Period Name</label>
                            <input type="text" id="period_name" name="period_name" required value="{{ old('period_name') }}" class="form-control" placeholder="FY 2026-2027">
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="start_date">Start Date</label>
                                <input type="date" id="start_date" name="start_date" required value="{{ old('start_date') }}" class="form-control">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="end_date">End Date</label>
                                <input type="date" id="end_date" name="end_date" required value="{{ old('end_date') }}" class="form-control">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">Create Financial Period</button>
                    </form>

                    <div class="table-responsive border rounded">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Period</th>
                                    <th>Range</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($periods as $period)
                                    <tr>
                                        <td class="font-weight-bold">{{ $period->period_name }}</td>
                                        <td>{{ $period->start_date->format('Y-m-d') }} to {{ $period->end_date->format('Y-m-d') }}</td>
                                        <td>
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input period-toggle" id="period_{{ $period->period_id }}" data-url="{{ route('erpaccount.financial-periods.update', $period) }}" {{ $period->is_closed ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="period_{{ $period->period_id }}">
                                                    <span class="badge period-status {{ $period->is_closed ? 'badge-danger' : 'badge-success' }}">
                                                        {{ $period->is_closed ? 'Closed' : 'Open' }}
                                                    </span>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-muted text-center">No financial periods configured yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editTaxModal" tabindex="-1" role="dialog" aria-labelledby="editTaxModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTaxModalLabel">Edit Tax Rate</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editTaxForm" action="#" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="modal-body">
                        <div class="form-group">
                            <label for="edit_tax_name">Tax Name</label>
                            <input type="text" id="edit_tax_name" name="tax_name" required class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="edit_percentage">Percentage</label>
                            <input type="number" id="edit_percentage" name="percentage" min="0" max="100" step="0.01" required class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="edit_ledger_account_id">Ledger Account</label>
                            <select id="edit_ledger_account_id" name="ledger_account_id" required class="form-control">
                                <option value="">Select Liability/Expense Ledger</option>
                                @foreach ($ledgerAccounts as $ledger)
                                    <option value="{{ $ledger->account_id }}">
                                        {{ $ledger->account_type }} | {{ $ledger->account_code }} - {{ $ledger->account_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="edit_tax_is_active" name="is_active" value="1">
                            <label class="custom-control-label" for="edit_tax_is_active">Active</label>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Tax Rate</button>
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
                var csrfToken = $('meta[name="csrf-token"]').attr('content');

                $(document).on('click', '.tax-edit-btn', function() {
                    const taxId = $(this).data('tax-id');
                    $('#edit_tax_name').val($(this).data('tax-name'));
                    $('#edit_percentage').val($(this).data('percentage'));
                    $('#edit_ledger_account_id').val(String($(this).data('ledger-account-id')));
                    $('#edit_tax_is_active').prop('checked', Number($(this).data('is-active')) === 1);
                    $('#editTaxForm').attr('action', `{{ url('/erpaccount/tax-rates') }}/${taxId}`);
                });

                $('.period-toggle').on('change', function() {
                    var $toggle = $(this);
                    var previous = !$toggle.prop('checked');
                    var $badge = $toggle.closest('td').find('.period-status');

                    $.ajax({
                        url: $toggle.data('url'),
                        type: 'POST',
                        dataType: 'json',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        data: {
                            _method: 'PUT',
                            is_closed: $toggle.prop('checked') ? 1 : 0
                        }
                    }).done(function() {
                        if ($toggle.prop('checked')) {
                            $badge.removeClass('badge-success').addClass('badge-danger').text('Closed');
                        } else {
                            $badge.removeClass('badge-danger').addClass('badge-success').text('Open');
                        }
                    }).fail(function() {
                        $toggle.prop('checked', previous);
                        alert('Unable to update period status.');
                    });
                });
            });
        })(jQuery);
    </script>
@endpush
