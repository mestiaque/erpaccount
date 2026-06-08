@extends(adminTheme().'layouts.app')
@section('title')
<title>{{websiteTitle('Inventory Manual Journal')}}</title>
@endsection

@push('css')
    <style>
        .inventory-hero {
            border-radius: 16px;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 55%, #047857 100%);
            color: #fff;
        }

        .inventory-stat {
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.08);
            padding: 12px;
        }

        .inventory-muted {
            color: #64748b;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .inventory-preview-box {
            border-radius: 10px;
            background: #f8fafc;
            padding: 12px;
        }
    </style>
@endpush

@section('contents')
    <div class="flex-grow-1">
        <div class="inventory-hero p-4 mb-4 shadow-sm">
            <div class="row align-items-end">
                <div class="col-lg-8 mb-3 mb-lg-0">
                    <div class="small text-uppercase" style="letter-spacing:.18em;opacity:.85;">Phase 3 Manual Posting</div>
                    <h3 class="mb-2 mt-2">Inventory Financial Journal Screen</h3>
                    <p class="mb-0" style="opacity:.9;">
                        Manually enter the inventory voucher, verify journal impact, and post a direct double-entry to Accounts.
                    </p>
                </div>
                <div class="col-lg-4">
                    <div class="row">
                        <div class="col-6 mb-2">
                            <div class="inventory-stat">
                                <div class="small text-uppercase">Debit</div>
                                <strong id="debitDisplay">0.00</strong>
                            </div>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="inventory-stat">
                                <div class="small text-uppercase">Credit</div>
                                <strong id="creditDisplay">0.00</strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="inventory-stat">
                                <div class="small text-uppercase">Difference</div>
                                <strong id="differenceDisplay">0.00</strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="inventory-stat">
                                <div class="small text-uppercase">Status</div>
                                <strong id="balanceStatus">Awaiting input</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 pl-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('erpaccount.manual-inventory.store') }}" id="manualInventoryForm">
            @csrf

            <div class="row">
                <div class="col-xl-8 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                                <div>
                                    <h5 class="mb-1">Manual Inventory Journal Entry</h5>
                                    <p class="text-muted mb-0">Choose transaction type, enter amount, and capture audit remarks.</p>
                                </div>
                                <span class="badge badge-success">No API, manual only</span>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Date</label>
                                    <input type="date" name="entry_date" value="{{ old('entry_date', now()->toDateString()) }}" class="form-control" required>
                                </div>

                                <div class="form-group col-md-6">
                                    <label>Cost Center / Style Order ID</label>
                                    <select name="cost_center_id" class="form-control">
                                        <option value="">Optional - None</option>
                                        @foreach ($costCenters as $costCenter)
                                            <option value="{{ $costCenter->cost_center_id }}" @selected((string) old('cost_center_id') === (string) $costCenter->cost_center_id)>
                                                {{ $costCenter->cost_center_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-12">
                                    <label>Inventory Transaction Type</label>
                                    <select name="transaction_type" id="transactionType" class="form-control" required>
                                        <option value="">Select transaction type</option>
                                        @foreach ($transactionTypes as $transactionType)
                                            <option value="{{ $transactionType }}" @selected(old('transaction_type') === $transactionType)>
                                                {{ $transactionType }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-6">
                                    <label>Amount</label>
                                    <input type="number" step="0.01" min="0" name="amount" value="{{ old('amount', '0') }}" id="amountField" class="form-control" required>
                                </div>

                                <div class="form-group col-md-6">
                                    <label>Currency</label>
                                    <select name="currency" id="currencyField" class="form-control" required>
                                        @foreach ($currencies as $currency)
                                            <option value="{{ $currency }}" @selected(old('currency', 'BDT') === $currency)>{{ $currency }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-12 mb-0">
                                    <label>Narration / Remarks</label>
                                    <input type="text" name="remarks" value="{{ old('remarks') }}" placeholder="Store Challan / GRN / audit note" class="form-control" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 mb-4">
                    <div class="card shadow-sm mb-3">
                        <div class="card-body">
                            <h6 class="mb-1">Journal Preview</h6>
                            <p class="text-muted mb-3">The system posts one debit and one credit for selected type.</p>

                            <div class="inventory-preview-box mb-2">
                                <div class="inventory-muted">Debit Account</div>
                                <div class="font-weight-bold" id="debitAccount">Select a transaction type</div>
                            </div>
                            <div class="inventory-preview-box mb-2">
                                <div class="inventory-muted">Credit Account</div>
                                <div class="font-weight-bold" id="creditAccount">Select a transaction type</div>
                            </div>
                            <div class="inventory-preview-box">
                                <div class="inventory-muted">Ledger Impact</div>
                                <div class="font-weight-bold" id="ledgerImpact">Waiting for entry</div>
                            </div>

                            <div id="typeNotice" class="alert alert-warning mt-3 d-none mb-2"></div>
                            <div id="wipNotice" class="alert alert-danger mt-2 d-none mb-0">
                                A Cost Center / Style Order ID is required for Issue to Production (WIP).
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-3">
                        <div class="card-body bg-dark text-light" style="border-radius:.35rem;">
                            <h6 class="mb-3">Posting Rules</h6>
                            <ul class="mb-0 pl-3 small">
                                <li>Material Purchase: Debit Raw Material Inventory Account, Credit Supplier/Payable Account.</li>
                                <li>Issue to Production (WIP): Debit WIP Account with the selected cost center, Credit Raw Material Inventory Account.</li>
                                <li>Inventory Adjustment/Loss: Debit Inventory Adjustment/Loss Account, Credit Raw Material Inventory Account.</li>
                            </ul>
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center rounded bg-light p-2 mb-2">
                                <span class="text-muted">Total Debit</span>
                                <strong id="debitValue">0.00</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center rounded bg-light p-2 mb-2">
                                <span class="text-muted">Total Credit</span>
                                <strong id="creditValue">0.00</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center rounded bg-light p-2 mb-2">
                                <span class="text-muted">Difference</span>
                                <strong id="differenceValue">0.00</strong>
                            </div>

                            <div id="balanceOk" class="alert alert-success d-none mb-2">
                                Voucher is balanced and ready to post.
                            </div>
                            <div id="balanceBad" class="alert alert-danger d-none mb-2">
                                Adjust the form before posting.
                            </div>

                            <button
                                type="submit"
                                id="postInventoryBtn"
                                class="btn btn-success btn-block"
                                disabled
                            >
                                Post Inventory Voucher
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('js')
    <script>
        (function () {
            'use strict';

            const mapping = {
                'Material Purchase': {
                    debit: 'Raw Material Inventory Account',
                    credit: 'Supplier / Payable Account',
                    note: 'Use for direct material procurement entries.',
                    requiresCostCenter: false,
                },
                'Issue to Production (WIP)': {
                    debit: 'WIP Account',
                    credit: 'Raw Material Inventory Account',
                    note: 'Cost Center / Style Order ID will be attached to the debit line.',
                    requiresCostCenter: true,
                },
                'Inventory Adjustment/Loss': {
                    debit: 'Inventory Adjustment / Loss Account',
                    credit: 'Raw Material Inventory Account',
                    note: 'Use for shrinkage, write-off, or manual adjustment entries.',
                    requiresCostCenter: false,
                },
            };

            const transactionType = document.getElementById('transactionType');
            const amountField = document.getElementById('amountField');
            const currencyField = document.getElementById('currencyField');
            const costCenterField = document.querySelector('[name="cost_center_id"]');
            const remarksField = document.querySelector('[name="remarks"]');
            const entryDateField = document.querySelector('[name="entry_date"]');
            const debitValue = document.getElementById('debitValue');
            const creditValue = document.getElementById('creditValue');
            const differenceValue = document.getElementById('differenceValue');
            const balanceOk = document.getElementById('balanceOk');
            const balanceBad = document.getElementById('balanceBad');
            const postBtn = document.getElementById('postInventoryBtn');
            const debitAccount = document.getElementById('debitAccount');
            const creditAccount = document.getElementById('creditAccount');
            const ledgerImpact = document.getElementById('ledgerImpact');
            const typeNotice = document.getElementById('typeNotice');
            const wipNotice = document.getElementById('wipNotice');
            const money = (value) => Number.parseFloat(value || 0).toFixed(2);

            function refresh() {
                const type = transactionType.value;
                const amount = Number.parseFloat(amountField.value || 0);
                const mapped = mapping[type];
                const hasBaseFields = Boolean(entryDateField.value && type && amount > 0 && currencyField.value && remarksField.value);
                const requiresCostCenter = mapped ? mapped.requiresCostCenter : false;
                const hasCostCenter = Boolean(costCenterField.value);
                const balanced = hasBaseFields && (!requiresCostCenter || hasCostCenter);

                debitValue.textContent = money(amount);
                creditValue.textContent = money(amount);
                differenceValue.textContent = money(0);
                ledgerImpact.textContent = mapped ? `${mapped.debit} -> ${mapped.credit}` : 'Waiting for entry';

                if (mapped) {
                    debitAccount.textContent = mapped.debit;
                    creditAccount.textContent = mapped.credit;
                    typeNotice.textContent = mapped.note;
                    typeNotice.classList.remove('d-none');
                } else {
                    debitAccount.textContent = 'Select a transaction type';
                    creditAccount.textContent = 'Select a transaction type';
                    typeNotice.classList.add('d-none');
                }

                wipNotice.classList.toggle('d-none', !(type === 'Issue to Production (WIP)' && !hasCostCenter));
                balanceOk.classList.toggle('d-none', !balanced);
                balanceBad.classList.toggle('d-none', balanced);
                postBtn.disabled = !balanced;

                if (!entryDateField.value || !type) {
                    document.getElementById('balanceStatus').textContent = 'Awaiting input';
                } else if (balanced) {
                    document.getElementById('balanceStatus').textContent = 'Balanced';
                } else {
                    document.getElementById('balanceStatus').textContent = 'Out of balance';
                }

                document.getElementById('debitDisplay').textContent = money(amount);
                document.getElementById('creditDisplay').textContent = money(amount);
                document.getElementById('differenceDisplay').textContent = money(0);
            }

            [transactionType, amountField, currencyField, costCenterField, remarksField, entryDateField].forEach((field) => {
                if (!field) return;
                field.addEventListener('input', refresh);
                field.addEventListener('change', refresh);
            });

            refresh();
        })();
    </script>
@endpush
