@extends(adminTheme().'layouts.app')

@section('title')
<title>{{websiteTitle('Cash & Bank Vouchers')}}</title>
@endsection

@section('contents')
    <div class="flex-grow-1">
        <div class="card shadow-sm">
            <div class="card-body">
                <ul class="nav nav-tabs" id="cashBankTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="receipt-tab" data-toggle="tab" href="#receiptPanel" role="tab">Receipt Voucher (Debit Cash/Bank)</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="payment-tab" data-toggle="tab" href="#paymentPanel" role="tab">Payment Voucher (Credit Cash/Bank)</a>
                    </li>
                </ul>
    
                <div class="tab-content pt-4" id="cashBankTabsContent">
                    <div class="tab-pane fade show active" id="receiptPanel" role="tabpanel">
                        <form method="POST" action="{{ route('erpaccount.cash-bank-vouchers.receipt.store') }}" id="receiptForm">
                            @csrf
                            <input type="hidden" name="voucher_type" value="receipt">
                            @include('erpaccount::phase2.cash_bank.partials.form', ['formPrefix' => 'receipt'])
                        </form>
                    </div>
                    <div class="tab-pane fade" id="paymentPanel" role="tabpanel">
                        <form method="POST" action="{{ route('erpaccount.cash-bank-vouchers.payment.store') }}" id="paymentForm">
                            @csrf
                            <input type="hidden" name="voucher_type" value="payment">
                            @include('erpaccount::phase2.cash_bank.partials.form', ['formPrefix' => 'payment'])
                        </form>
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

            const accountMeta = @json($accountMeta);
            const costCenters = @json($costCenters->map(fn ($c) => ['id' => $c->cost_center_id, 'name' => $c->cost_center_name]));
            const offsetAccountOptions = (() => {
                let html = '<option value="">Select Account</option>';
                @foreach ($offsetAccounts as $account)
                    html += `<option value="{{ $account->account_id }}">{{ $account->account_code }} - {{ $account->account_name }}</option>`;
                @endforeach
                return html;
            })();

            function costCenterOptions() {
                let html = '<option value="">N/A</option>';
                costCenters.forEach(function(item) {
                    html += `<option value="${item.id}">${item.name}</option>`;
                });
                return html;
            }

            function appendRow(prefix) {
                const $tbody = $('#' + prefix + 'RowsBody');
                const index = $tbody.find('tr').length;

                const row = `
                    <tr>
                        <td>
                            <select class="form-control form-control-sm ${prefix}-account" name="rows[${index}][account_id]" required>
                                ${offsetAccountOptions}
                            </select>
                        </td>
                        <td>
                            <select class="form-control form-control-sm ${prefix}-cost-center d-none" name="rows[${index}][cost_center_id]">
                                ${costCenterOptions()}
                            </select>
                        </td>
                        <td>
                            <select class="form-control form-control-sm ${prefix}-party-type d-none" name="rows[${index}][party_type]">
                                <option value="None">None</option>
                                <option value="Buyer">Debitor (Buyer)</option>
                                <option value="Supplier">Creditor (Supplier)</option>
                                <option value="Employee">Employee</option>
                            </select>
                        </td>
                        <td>
                            <select class="form-control form-control-sm ${prefix}-party-id d-none" name="rows[${index}][party_id]">
                                <option value="">Select Party Ledger</option>
                            </select>
                        </td>
                        <td>
                            <input type="number" step="0.01" min="0.01" class="form-control form-control-sm ${prefix}-amount" name="rows[${index}][amount]" required>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-danger ${prefix}-remove-row"><i class="fa fa-trash"></i></button>
                        </td>
                    </tr>
                `;

                $tbody.append(row);
            }

            function setConditionalFields(prefix, $row) {
                const accountId = $row.find('.' + prefix + '-account').val();
                const meta = accountMeta[accountId] || null;

                const $costCenter = $row.find('.' + prefix + '-cost-center');
                const $partyType = $row.find('.' + prefix + '-party-type');
                const $partyId = $row.find('.' + prefix + '-party-id');

                if (meta && meta.requires_cost_center) {
                    $costCenter.removeClass('d-none');
                } else {
                    $costCenter.addClass('d-none').val('');
                }

                if (meta && meta.requires_party) {
                    $partyType.removeClass('d-none');
                    $partyId.removeClass('d-none');
                } else {
                    $partyType.addClass('d-none').val('None');
                    $partyId.addClass('d-none').html('<option value="">Select Party Ledger</option>');
                }
            }

            function fetchPartyOptions(prefix, $row) {
                const partyType = $row.find('.' + prefix + '-party-type').val();
                const $partySelect = $row.find('.' + prefix + '-party-id');

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
                        html += `<option value="${item.id}">${item.display_name || item.name}</option>`;
                    });
                    $partySelect.html(html);
                }).fail(function() {
                    $partySelect.html('<option value="">No party found</option>');
                });
            }

            function updateTotal(prefix) {
                let total = 0;
                $('.' + prefix + '-amount').each(function() {
                    total += parseFloat($(this).val() || 0);
                });
                $('#' + prefix + 'Total').text(total.toFixed(2));
            }

            function initVoucher(prefix) {
                appendRow(prefix);

                $('#' + prefix + 'AddRow').on('click', function() {
                    appendRow(prefix);
                });

                $(document).on('click', '.' + prefix + '-remove-row', function() {
                    if ($('#' + prefix + 'RowsBody tr').length <= 1) {
                        return;
                    }
                    $(this).closest('tr').remove();
                    updateTotal(prefix);
                });

                $(document).on('change', '.' + prefix + '-account', function() {
                    const $row = $(this).closest('tr');
                    setConditionalFields(prefix, $row);

                    const isPartyVisible = !$row.find('.' + prefix + '-party-type').hasClass('d-none');
                    if (isPartyVisible && $row.find('.' + prefix + '-party-type').val() !== 'None') {
                        fetchPartyOptions(prefix, $row);
                    }
                });

                $(document).on('change', '.' + prefix + '-party-type', function() {
                    fetchPartyOptions(prefix, $(this).closest('tr'));
                });

                $(document).on('input', '.' + prefix + '-amount', function() {
                    updateTotal(prefix);
                });
            }

            $(function() {
                initVoucher('receipt');
                initVoucher('payment');
            });
        })(jQuery);
    </script>
@endpush
