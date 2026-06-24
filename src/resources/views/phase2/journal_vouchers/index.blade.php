@extends(adminTheme().'layouts.app')

@section('title')
<title>{{websiteTitle('Journal Vouchers')}}</title>
@endsection

@section('contents')
    <div class="flex-grow-1">
        <div class="row">
            <div class="col-lg-9 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body pb-5">
                        <form action="{{ route('erpaccount.journal-vouchers.store') }}" method="POST" id="journalVoucherForm">
                            @csrf
    
                            <div class="form-row">
                                <div class="form-group col-md-3">
                                    <label for="journal_date">Journal Date</label>
                                    <input type="date" id="journal_date" name="journal_date" class="form-control" value="{{ old('journal_date', now()->toDateString()) }}" required>
                                </div>
                                <div class="form-group col-md-9">
                                    <label for="narration">Narration</label>
                                    <input type="text" id="narration" name="narration" class="form-control" value="{{ old('narration') }}" placeholder="Short narration for this voucher">
                                </div>
                            </div>
    
                            <div class="table-responsive border rounded">
                                <table class="table table-sm table-hover mb-0" id="journalRowsTable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="min-width: 220px;">Account</th>
                                            <th style="min-width: 130px;">Cost Center</th>
                                            <th style="min-width: 130px;">Party Type</th>
                                            <th style="min-width: 220px;">Party Ledger</th>
                                            <th style="min-width: 120px;">Debit</th>
                                            <th style="min-width: 120px;">Credit</th>
                                            <th style="width: 60px;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="journalRowsBody"></tbody>
                                </table>
                            </div>
    
                            <div class="mt-3 d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-primary" id="addJournalRowBtn">
                                    <i class="fa fa-plus mr-1"></i> Add New Row
                                </button>
                                <button type="submit" class="btn btn-success" id="submitJournalBtn" disabled>
                                    Submit Voucher
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
    
            <div class="col-lg-3 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Recent Vouchers</h6>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush small">
                            @forelse ($recentVouchers as $voucher)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <a href="{{ route('erpaccount.voucher-register.show', $voucher) }}">{{ $voucher->voucher_no }}</a>
                                    <span class="badge badge-info">{{ number_format((float) $voucher->total_debit, 2) }}</span>
                                </li>
                            @empty
                                <li class="list-group-item text-muted">No vouchers posted yet.</li>
                            @endforelse
                        </ul>
                        <div class="p-2 border-top">
                            <a href="{{ route('erpaccount.voucher-register.index') }}" class="btn btn-sm btn-outline-secondary btn-block">Open Voucher Register</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
        <div class="fixed-bottom border-top bg-white shadow-sm py-2" id="balanceBar">
            <div class="container-fluid">
                <div class="row align-items-center text-center">
                    <div class="col-md-3 font-weight-bold">Total Debit: <span id="totalDebit">0.00</span></div>
                    <div class="col-md-3 font-weight-bold">Total Credit: <span id="totalCredit">0.00</span></div>
                    <div class="col-md-3 font-weight-bold">Difference: <span id="totalDiff">0.00</span></div>
                    <div class="col-md-3"><span class="badge badge-secondary" id="balanceStateBadge">Not Balanced</span></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        (function($) {
            'use strict';

            const accountMeta = @json($accountMeta);
            const costCenters = @json($costCenters->map(fn ($c) => ['id' => $c->cost_center_id, 'name' => $c->cost_center_name]));
            let rowIndex = 0;

            function accountOptionsHtml(selected = '') {
                let html = '<option value="">Select Account</option>';
                @foreach ($accounts as $account)
                    html += `<option value="{{ $account->account_id }}" ${selected == '{{ $account->account_id }}' ? 'selected' : ''}>{{ $account->account_code }} - {{ $account->account_name }}</option>`;
                @endforeach
                return html;
            }

            function costCenterOptionsHtml(selected = '') {
                let html = '<option value="">N/A</option>';
                costCenters.forEach(function(item) {
                    html += `<option value="${item.id}" ${selected == item.id ? 'selected' : ''}>${item.name}</option>`;
                });
                return html;
            }

            function appendRow(defaultData = {}) {
                const selectedPartyType = defaultData.party_type || 'None';
                const html = `
                    <tr data-row-index="${rowIndex}">
                        <td>
                            <select class="form-control form-control-sm account-select" name="rows[${rowIndex}][account_id]" required>
                                ${accountOptionsHtml(defaultData.account_id || '')}
                            </select>
                        </td>
                        <td>
                            <select class="form-control form-control-sm cost-center-select d-none" name="rows[${rowIndex}][cost_center_id]">
                                ${costCenterOptionsHtml(defaultData.cost_center_id || '')}
                            </select>
                        </td>
                        <td>
                            <select class="form-control form-control-sm party-type-select d-none" name="rows[${rowIndex}][party_type]">
                                <option value="None" ${selectedPartyType === 'None' ? 'selected' : ''}>None</option>
                                <option value="Buyer" ${selectedPartyType === 'Buyer' ? 'selected' : ''}>Debitor (Buyer)</option>
                                <option value="Supplier" ${selectedPartyType === 'Supplier' ? 'selected' : ''}>Creditor (Supplier)</option>
                                <option value="Employee" ${selectedPartyType === 'Employee' ? 'selected' : ''}>Employee</option>
                            </select>
                        </td>
                        <td>
                            <select class="form-control form-control-sm party-id-select d-none" name="rows[${rowIndex}][party_id]" data-selected="${defaultData.party_id || ''}"><option value="">Select Party Ledger</option></select>
                        </td>
                        <td><input type="number" step="0.01" min="0" class="form-control form-control-sm debit-input" name="rows[${rowIndex}][debit]" value="${defaultData.debit || 0}"></td>
                        <td><input type="number" step="0.01" min="0" class="form-control form-control-sm credit-input" name="rows[${rowIndex}][credit]" value="${defaultData.credit || 0}"></td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger remove-row-btn"><i class="fa fa-trash"></i></button></td>
                    </tr>
                `;

                $('#journalRowsBody').append(html);
                rowIndex += 1;
            }

            function setDynamicFields($row) {
                const accountId = $row.find('.account-select').val();
                const meta = accountMeta[accountId] || null;

                const $costCenter = $row.find('.cost-center-select');
                const $partyType = $row.find('.party-type-select');
                const $partyId = $row.find('.party-id-select');

                if (meta && meta.requires_cost_center) {
                    $costCenter.removeClass('d-none');
                } else {
                    $costCenter.addClass('d-none').val('');
                }

                if (meta && meta.requires_party) {
                    $partyType.removeClass('d-none');
                    $partyId.removeClass('d-none');

                    const currentType = $partyType.val();
                    if (meta.preferred_party_type && currentType !== meta.preferred_party_type) {
                        $partyType.val(meta.preferred_party_type);
                        $partyId.data('selected', '');
                    }
                } else {
                    $partyType.addClass('d-none').val('None');
                    $partyId.addClass('d-none').html('<option value="">Select Party Ledger</option>');
                }
            }

            function fetchPartyOptions($row, selectedId = '') {
                const partyType = $row.find('.party-type-select').val();
                const $partySelect = $row.find('.party-id-select');
                const preselected = selectedId || $partySelect.data('selected') || '';

                if (!partyType || partyType === 'None') {
                    $partySelect.html('<option value="">Select Party Ledger</option>');
                    return;
                }

                $partySelect.html('<option value="">Loading...</option>');

                $.get("{{ route('erpaccount.journal-vouchers.party-options') }}", {
                    party_type: partyType
                }, function(response) {
                    let html = '<option value="">Select Party Ledger</option>';
                    (response.data || []).forEach(function(item) {
                        const selected = Number(preselected) === Number(item.id) ? 'selected' : '';
                        html += `<option value="${item.id}" ${selected}>${item.display_name || item.name}</option>`;
                    });
                    $partySelect.html(html);
                    $partySelect.data('selected', '');
                }).fail(function() {
                    $partySelect.html('<option value="">No party found</option>');
                });
            }

            function computeTotals() {
                let debit = 0;
                let credit = 0;

                $('.debit-input').each(function() {
                    debit += parseFloat($(this).val() || 0);
                });

                $('.credit-input').each(function() {
                    credit += parseFloat($(this).val() || 0);
                });

                const diff = Math.abs(debit - credit);
                const isBalanced = diff < 0.0001 && debit > 0 && credit > 0;

                $('#totalDebit').text(debit.toFixed(2));
                $('#totalCredit').text(credit.toFixed(2));
                $('#totalDiff').text((debit - credit).toFixed(2));

                if (isBalanced) {
                    $('#balanceBar').removeClass('bg-danger-light').addClass('bg-success-light');
                    $('#balanceStateBadge').removeClass('badge-secondary badge-danger').addClass('badge-success').text('Balanced');
                    $('#submitJournalBtn').prop('disabled', false);
                } else {
                    $('#balanceBar').removeClass('bg-success-light').addClass('bg-danger-light');
                    $('#balanceStateBadge').removeClass('badge-secondary badge-success').addClass('badge-danger').text('Not Balanced');
                    $('#submitJournalBtn').prop('disabled', true);
                }
            }

            $(function() {
                appendRow();
                appendRow();
                computeTotals();

                $('#addJournalRowBtn').on('click', function() {
                    appendRow();
                });

                $(document).on('click', '.remove-row-btn', function() {
                    if ($('#journalRowsBody tr').length <= 2) {
                        return;
                    }
                    $(this).closest('tr').remove();
                    computeTotals();
                });

                $(document).on('change', '.account-select', function() {
                    const $row = $(this).closest('tr');
                    setDynamicFields($row);

                    const isPartyVisible = !$row.find('.party-type-select').hasClass('d-none');
                    if (isPartyVisible && $row.find('.party-type-select').val() !== 'None') {
                        fetchPartyOptions($row);
                    }
                });

                $(document).on('change', '.party-type-select', function() {
                    const $row = $(this).closest('tr');
                    fetchPartyOptions($row);
                });

                $(document).on('input', '.debit-input, .credit-input', function() {
                    const $row = $(this).closest('tr');
                    const $debit = $row.find('.debit-input');
                    const $credit = $row.find('.credit-input');

                    if ($(this).hasClass('debit-input') && parseFloat($debit.val() || 0) > 0) {
                        $credit.val(0);
                    }
                    if ($(this).hasClass('credit-input') && parseFloat($credit.val() || 0) > 0) {
                        $debit.val(0);
                    }

                    computeTotals();
                });

                @if ($errors->any() && is_array(old('rows')))
                    $('#journalRowsBody').empty();
                    rowIndex = 0;
                    @foreach (old('rows') as $oldRow)
                        appendRow(@json($oldRow));
                    @endforeach
                    $('#journalRowsBody tr').each(function() {
                        const $row = $(this);
                        setDynamicFields($row);

                        if (!$row.find('.party-type-select').hasClass('d-none') && $row.find('.party-type-select').val() !== 'None') {
                            fetchPartyOptions($row, $row.find('.party-id-select').data('selected'));
                        }
                    });
                @endif
            });
        })(jQuery);
    </script>
@endpush

@push('css')
    <style>
        .bg-success-light { background-color: #e9f7ef !important; }
        .bg-danger-light { background-color: #fdecea !important; }
        #balanceBar { z-index: 1030; }
        body { padding-bottom: 76px; }
    </style>
@endpush
