@extends(adminTheme().'layouts.app')

@section('title')
<title>{{websiteTitle('Bank Reconciliation Worksheet')}}</title>
@endsection

@section('contents')
    <div class="flex-grow-1">
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('erpaccount.bank-reconciliation.index') }}" class="form-row align-items-end">
                    <div class="form-group col-md-4">
                        <label>Bank Account</label>
                        <select name="bank_account_id" class="form-control" required>
                            @foreach ($bankAccounts as $bank)
                                <option value="{{ $bank->bank_account_id }}" {{ (int) $selectedBankAccountId === (int) $bank->bank_account_id ? 'selected' : '' }}>
                                    {{ $bank->bank_name }} | {{ optional($bank->chartOfAccount)->account_code }} - {{ optional($bank->chartOfAccount)->account_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label>From Date</label>
                        <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
                    </div>
                    <div class="form-group col-md-3">
                        <label>To Date</label>
                        <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
                    </div>
                    <div class="form-group col-md-2 d-flex">
                        <button type="submit" class="btn btn-primary flex-grow-1 mr-2">Filter</button>
                        <button type="button" class="btn btn-outline-secondary" data-toggle="modal" data-target="#uploadStatementModal">
                            <i class="fa fa-upload"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-light d-flex justify-content-between">
                        <strong>Internal Ledger Entries</strong>
                        <span class="badge badge-info">{{ $internalEntries->count() }}</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 530px;">
                            <table class="table table-sm table-hover mb-0" id="internalTable">
                                <thead class="thead-light sticky-top">
                                    <tr>
                                        <th>Match</th>
                                        <th>Date</th>
                                        <th>Voucher</th>
                                        <th>Debit</th>
                                        <th>Credit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($internalEntries as $entry)
                                        <tr data-detail-id="{{ $entry->detail_id }}" class="{{ $entry->is_reconciled ? 'table-success' : '' }}">
                                            <td>
                                                <input type="checkbox" class="internal-match-check" {{ $entry->is_reconciled ? 'checked disabled' : '' }}>
                                            </td>
                                            <td>{{ optional($entry->journalMaster)->journal_date?->format('Y-m-d') }}</td>
                                            <td>{{ optional($entry->journalMaster)->voucher_no }}</td>
                                            <td>{{ number_format((float) $entry->debit_amount, 2) }}</td>
                                            <td>{{ number_format((float) $entry->credit_amount, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center text-muted">No internal entries found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-light d-flex justify-content-between">
                        <strong>Bank Statement Entries</strong>
                        <span class="badge badge-info">{{ $statementEntries->count() }}</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 530px;">
                            <table class="table table-sm table-hover mb-0" id="statementTable">
                                <thead class="thead-light sticky-top">
                                    <tr>
                                        <th>Match</th>
                                        <th>Date</th>
                                        <th>Ref</th>
                                        <th>Debit</th>
                                        <th>Credit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($statementEntries as $entry)
                                        <tr data-statement-id="{{ $entry->statement_id }}" class="{{ $entry->is_reconciled ? 'table-success' : '' }}">
                                            <td>
                                                <input type="checkbox" class="statement-match-check" {{ $entry->is_reconciled ? 'checked disabled' : '' }}>
                                            </td>
                                            <td>{{ $entry->statement_date?->format('Y-m-d') }}</td>
                                            <td>{{ $entry->reference_no }}</td>
                                            <td>{{ number_format((float) $entry->debit_amount, 2) }}</td>
                                            <td>{{ number_format((float) $entry->credit_amount, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center text-muted">No statement entries found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="uploadStatementModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Upload Statement Rows</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <form method="POST" action="{{ route('erpaccount.bank-reconciliation.upload') }}" id="statementUploadForm">
                        @csrf
                        <input type="hidden" name="bank_account_id" value="{{ $selectedBankAccountId }}">
                        <input type="hidden" name="from_date" value="{{ $fromDate }}">
                        <input type="hidden" name="to_date" value="{{ $toDate }}">

                        <div class="modal-body">
                            <div class="table-responsive border rounded">
                                <table class="table table-sm mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Reference</th>
                                            <th>Description</th>
                                            <th>Debit</th>
                                            <th>Credit</th>
                                            <th>Balance</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="statementRowsBody"></tbody>
                                </table>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="addStatementRowBtn">Add Statement Row</button>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save Statement Entries</button>
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

            let selectedDetailId = null;
            let selectedStatementId = null;
            let statementRowIndex = 0;

            function tryMatch() {
                if (!selectedDetailId || !selectedStatementId) {
                    return;
                }

                $.ajax({
                    url: "{{ route('erpaccount.bank-reconciliation.match') }}",
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'Accept': 'application/json'
                    },
                    data: {
                        detail_id: selectedDetailId,
                        statement_id: selectedStatementId,
                        is_matched: 1
                    }
                }).done(function() {
                    const $internalRow = $('#internalTable tr[data-detail-id="' + selectedDetailId + '"]');
                    const $statementRow = $('#statementTable tr[data-statement-id="' + selectedStatementId + '"]');

                    $internalRow.addClass('table-success').find('input').prop('checked', true).prop('disabled', true);
                    $statementRow.addClass('table-success').find('input').prop('checked', true).prop('disabled', true);

                    selectedDetailId = null;
                    selectedStatementId = null;
                }).fail(function() {
                    alert('Unable to save reconciliation.');
                });
            }

            function appendStatementRow() {
                const row = `
                    <tr>
                        <td><input type="date" class="form-control form-control-sm" name="entries[${statementRowIndex}][statement_date]" required></td>
                        <td><input type="text" class="form-control form-control-sm" name="entries[${statementRowIndex}][reference_no]"></td>
                        <td><input type="text" class="form-control form-control-sm" name="entries[${statementRowIndex}][description]"></td>
                        <td><input type="number" step="0.01" min="0" class="form-control form-control-sm" name="entries[${statementRowIndex}][debit_amount]"></td>
                        <td><input type="number" step="0.01" min="0" class="form-control form-control-sm" name="entries[${statementRowIndex}][credit_amount]"></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm" name="entries[${statementRowIndex}][closing_balance]"></td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger remove-statement-row"><i class="fa fa-trash"></i></button></td>
                    </tr>
                `;
                $('#statementRowsBody').append(row);
                statementRowIndex += 1;
            }

            $(function() {
                appendStatementRow();

                $('#addStatementRowBtn').on('click', function() {
                    appendStatementRow();
                });

                $(document).on('click', '.remove-statement-row', function() {
                    $(this).closest('tr').remove();
                });

                $(document).on('change', '.internal-match-check', function() {
                    $('.internal-match-check').not(this).prop('checked', false);
                    selectedDetailId = $(this).is(':checked') ? $(this).closest('tr').data('detail-id') : null;
                    tryMatch();
                });

                $(document).on('change', '.statement-match-check', function() {
                    $('.statement-match-check').not(this).prop('checked', false);
                    selectedStatementId = $(this).is(':checked') ? $(this).closest('tr').data('statement-id') : null;
                    tryMatch();
                });
            });
        })(jQuery);
    </script>
@endpush
