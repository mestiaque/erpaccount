@extends(adminTheme().'layouts.app')

@section('title')
<title>{{websiteTitle('Payroll Manual Entry')}}</title>
@endsection

@section('contents')
    <div class="flex-grow-1">
        <div class="bg-light" >
            <div class="card border-0 shadow-lg text-white mb-4" style="background: linear-gradient(95deg, #0f172a 0%, #1e293b 55%, #b45309 100%);">
                <div class="card-body p-4 p-lg-5">
                    <div class="row g-3 align-items-end justify-content-between">
                        <div class="col-lg-8">
                            <p class="small fw-semibold text-uppercase mb-2" style="letter-spacing: .28rem; color: #fde68a;">Manual Posting</p>
                            <h1 class="h2 mb-2">Payroll &amp; Wages Manual Entry Screen</h1>
                            <p class="mb-0" style="color: #f1f5f9;">Enter monthly payroll totals manually, verify the double-entry balance in real time, and post one consolidated journal voucher directly into the Accounts module.</p>
                        </div>
                        <div class="col-lg-4">
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="rounded-3 p-2" style="background-color: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.2);">
                                        <div class="small text-uppercase">Debit</div>
                                        <div class="fw-semibold" id="debitDisplay">0.00</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="rounded-3 p-2" style="background-color: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.2);">
                                        <div class="small text-uppercase">Credit</div>
                                        <div class="fw-semibold" id="creditDisplay">0.00</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="rounded-3 p-2" style="background-color: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.2);">
                                        <div class="small text-uppercase">Difference</div>
                                        <div class="fw-semibold" id="differenceDisplay">0.00</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="rounded-3 p-2" style="background-color: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.2);">
                                        <div class="small text-uppercase">Status</div>
                                        <div class="fw-semibold" id="balanceStatus">Awaiting input</div>
                                    </div>
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
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('erpaccount.manual-payroll.store') }}" id="manualPayrollForm">
                @csrf

                <div class="row g-4">
                    <div class="col-xl-8">
                        <div class="card border-secondary shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                                    <div>
                                        <h2 class="h5 mb-1">Manual Summary Entry</h2>
                                        <p class="small text-muted mb-0">Choose the month and enter the high-level totals for posting.</p>
                                    </div>
                                    <span class="badge bg-warning text-dark rounded-pill">Manual only, no external link</span>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Month / Year</label>
                                        <input type="month" name="month" value="{{ old('month', now()->format('Y-m')) }}" class="form-control" required>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Narration / Reference Note</label>
                                        <input type="text" name="narration" value="{{ old('narration') }}" placeholder="Manual payroll voucher for March 2026" class="form-control">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Gross Salary / Basic</label>
                                        <input type="number" step="0.01" min="0" name="gross_salary" value="{{ old('gross_salary', '0') }}" class="form-control amount-field" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Total Allowances</label>
                                        <input type="number" step="0.01" min="0" name="total_allowances" value="{{ old('total_allowances', '0') }}" class="form-control amount-field" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Total Bonus</label>
                                        <input type="number" step="0.01" min="0" name="total_bonus" value="{{ old('total_bonus', '0') }}" class="form-control amount-field" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Total Advance Salary Adjusted</label>
                                        <input type="number" step="0.01" min="0" name="advance_salary_adjusted" value="{{ old('advance_salary_adjusted', '0') }}" class="form-control amount-field" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Total Provident Fund Deduction</label>
                                        <input type="number" step="0.01" min="0" name="provident_fund_deduction" value="{{ old('provident_fund_deduction', '0') }}" class="form-control amount-field" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Net Payable Amount</label>
                                        <input type="number" step="0.01" min="0" name="net_payable" value="{{ old('net_payable', '0') }}" class="form-control amount-field" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="card border-secondary shadow-sm mb-4">
                            <div class="card-body">
                                <h3 class="h6 mb-1">Double-Entry Check</h3>
                                <p class="small text-muted mb-3">The voucher can only be posted when the totals balance exactly.</p>

                                <div class="d-grid gap-2">
                                    <div class="d-flex justify-content-between rounded-3 bg-light px-3 py-2">
                                        <span class="small text-muted">Total Debit</span>
                                        <strong id="debitLine">0.00</strong>
                                    </div>
                                    <div class="d-flex justify-content-between rounded-3 bg-light px-3 py-2">
                                        <span class="small text-muted">Total Credit</span>
                                        <strong id="creditLine">0.00</strong>
                                    </div>
                                    <div class="d-flex justify-content-between rounded-3 bg-light px-3 py-2">
                                        <span class="small text-muted">Difference</span>
                                        <strong id="differenceLine">0.00</strong>
                                    </div>
                                </div>

                                <div id="balanceWarning" class="alert alert-danger d-none mt-3 mb-0 py-2">Debit and credit totals must match before posting.</div>
                                <div id="balanceSuccess" class="alert alert-success d-none mt-3 mb-0 py-2">Voucher is balanced and ready to post.</div>
                            </div>
                        </div>

                        <div class="card border-dark bg-dark text-light shadow-sm mb-4">
                            <div class="card-body">
                                <h3 class="h6">Posting Rules</h3>
                                <ul class="mb-0 ps-3 small">
                                    <li>Debit uses the salary and expense side of the manual entry.</li>
                                    <li>Credit uses advance salary adjustment, provident fund deduction, and net payable.</li>
                                    <li>The journal is saved directly into the accounting tables as a standalone manual voucher.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="card border-secondary shadow-sm">
                            <div class="card-body">
                                <button type="submit" id="postPayrollBtn" class="btn btn-warning w-100 fw-semibold" disabled>
                                    Post Payroll Voucher
                                </button>
                                <p class="small text-muted text-center mt-2 mb-0">The button unlocks automatically when the voucher balances.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('js')
    <script>
        (function () {
            'use strict';

            const money = (value) => Number.parseFloat(value || 0).toFixed(2);
            const fields = Array.from(document.querySelectorAll('.amount-field'));
            const debitDisplay = document.getElementById('debitDisplay');
            const creditDisplay = document.getElementById('creditDisplay');
            const differenceDisplay = document.getElementById('differenceDisplay');
            const debitLine = document.getElementById('debitLine');
            const creditLine = document.getElementById('creditLine');
            const differenceLine = document.getElementById('differenceLine');
            const statusLine = document.getElementById('balanceStatus');
            const warningBox = document.getElementById('balanceWarning');
            const successBox = document.getElementById('balanceSuccess');
            const submitButton = document.getElementById('postPayrollBtn');
            const monthField = document.querySelector('input[name="month"]');

            function total(name) {
                const field = document.querySelector('[name="' + name + '"]');
                return field ? Number.parseFloat(field.value || 0) : 0;
            }

            function refreshBalance() {
                const debit = total('gross_salary') + total('total_allowances') + total('total_bonus');
                const credit = total('advance_salary_adjusted') + total('provident_fund_deduction') + total('net_payable');
                const difference = Math.abs(debit - credit);
                const monthSelected = Boolean(monthField && monthField.value);
                const balanced = monthSelected && difference < 0.005;

                debitDisplay.textContent = money(debit);
                creditDisplay.textContent = money(credit);
                differenceDisplay.textContent = money(difference);
                debitLine.textContent = money(debit);
                creditLine.textContent = money(credit);
                differenceLine.textContent = money(difference);

                warningBox.classList.toggle('d-none', balanced);
                successBox.classList.toggle('d-none', !balanced);
                submitButton.disabled = !balanced;
                statusLine.textContent = !monthSelected ? 'Select month' : balanced ? 'Balanced' : 'Out of balance';
                statusLine.className = 'mt-1 text-lg font-semibold ' + (!monthSelected ? 'text-muted' : balanced ? 'text-success' : 'text-danger');
                differenceLine.className = balanced ? 'text-success' : 'text-danger';
            }

            fields.forEach((field) => {
                field.addEventListener('input', refreshBalance);
                field.addEventListener('change', refreshBalance);
            });

            if (monthField) {
                monthField.addEventListener('change', refreshBalance);
                monthField.addEventListener('input', refreshBalance);
            }

            refreshBalance();
        })();
    </script>
@endpush
