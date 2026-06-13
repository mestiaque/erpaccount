@extends(adminTheme().'layouts.app')

@section('title')
<title>{{websiteTitle('Accounts System Tutorial')}}</title>
@endsection

@section('contents')
    <div class="flex-grow-1">
        <div class="card border-0 shadow-lg text-white mb-4" style="background: linear-gradient(100deg, #111827 0%, #0f766e 55%, #1d4ed8 100%);">
            <div class="card-body p-4 p-lg-5">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <p class="small text-uppercase mb-2" style="letter-spacing: .25rem; color: #a7f3d0;">Bangla Operating Manual</p>
                        <h1 class="h2 mb-3 text-white">ERP Accounts Full Tutorial</h1>
                        <p class="mb-3" style="color: #dbeafe; font-size: 1.02rem; line-height: 1.8;">
                            এই টিউটোরিয়াল follow করলে setup থেকে দৈনিক posting, bank reconciliation, এবং report export পর্যন্ত পুরো accounts workflow এক পেজে বুঝতে পারবেন।
                        </p>
                        <div class="d-flex flex-wrap">
                            <a href="#section-map" class="btn btn-light btn-sm mr-2 mb-2">Start Here</a>
                            <a href="#section-entry" class="btn btn-outline-light btn-sm mr-2 mb-2">Entry Workflow</a>
                            <a href="#section-rules" class="btn btn-outline-light btn-sm mr-2 mb-2">Field Logic</a>
                            <a href="#section-reports" class="btn btn-outline-light btn-sm mr-2 mb-2">Report Matrix</a>
                            <a href="#section-errors" class="btn btn-outline-light btn-sm mb-2">Troubleshooting</a>
                        </div>
                    </div>
                    <div class="col-lg-4 mt-4 mt-lg-0">
                        <div class="rounded p-4" style="background-color: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.2);">
                            <div class="small text-uppercase mb-2" style="letter-spacing: .15rem; color: #bfdbfe;">Core Flow</div>
                            <div class="h5 mb-2 text-white">Setup -> Post -> Match -> Analyze</div>
                            <div style="color: #dbeafe; line-height: 1.7;">
                                1) Master setup
                                <br>2) Voucher posting
                                <br>3) Bank reconciliation
                                <br>4) Reports and decision
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="section-map" class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <strong>Navigation Map: কোন কাজের জন্য কোন স্ক্রিন</strong>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th style="min-width: 230px;">Module</th>
                                <th>কাজ</th>
                                <th style="min-width: 220px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Chart of Accounts</strong></td>
                                <td>Ledger create/update, account type classify</td>
                                <td><a href="{{ route('erpaccount.chart-of-accounts.index') }}">Open Chart of Accounts</a></td>
                            </tr>
                            <tr>
                                <td><strong>Bank &amp; Cash Accounts</strong></td>
                                <td>Bank account এবং ledger mapping</td>
                                <td><a href="{{ route('erpaccount.bank-accounts.index') }}">Open Bank &amp; Cash Accounts</a></td>
                            </tr>
                            <tr>
                                <td><strong>Financial Periods</strong></td>
                                <td>Posting control (open/close date window)</td>
                                <td><a href="{{ route('erpaccount.financial-periods.index') }}">Open Financial Periods</a></td>
                            </tr>
                            <tr>
                                <td><strong>Universal Journal Voucher</strong></td>
                                <td>General ledger entries (adjustment, accrual, transfer)</td>
                                <td><a href="{{ route('erpaccount.journal-vouchers.index') }}">Open Journal Vouchers</a></td>
                            </tr>
                            <tr>
                                <td><strong>Cash &amp; Bank Vouchers</strong></td>
                                <td>Receipt এবং payment based bank/cash movement</td>
                                <td><a href="{{ route('erpaccount.cash-bank-vouchers.index') }}">Open Cash &amp; Bank Vouchers</a></td>
                            </tr>
                            <tr>
                                <td><strong>Voucher Register</strong></td>
                                <td>Posted voucher verification and audit trail</td>
                                <td><a href="{{ route('erpaccount.voucher-register.index') }}">Open Voucher Register</a></td>
                            </tr>
                            <tr>
                                <td><strong>Bank Reconciliation</strong></td>
                                <td>Statement upload + internal ledger matching</td>
                                <td><a href="{{ route('erpaccount.bank-reconciliation.index') }}">Open Bank Reconciliation</a></td>
                            </tr>
                            <tr>
                                <td><strong>Reports Center</strong></td>
                                <td>Financial এবং operational reports generate/export</td>
                                <td><a href="{{ route('erpaccount.reports.index') }}">Open Reports Center</a></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-lg-8 mb-3 mb-lg-0">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-light">
                        <strong>Quick Start Checklist (Go-live আগে)</strong>
                    </div>
                    <div class="card-body">
                        <ol class="mb-0 pl-3">
                            <li class="mb-2">COA-তে প্রয়োজনীয় ledgers active করুন।</li>
                            <li class="mb-2">Bank ledger mapping complete করুন।</li>
                            <li class="mb-2">Current date cover করা open financial period নিশ্চিত করুন।</li>
                            <li class="mb-2">একটি test Journal Voucher post করুন।</li>
                            <li class="mb-2">একটি test Cash/Bank Voucher post করুন।</li>
                            <li class="mb-2">Voucher Register-এ amounts এবং narration verify করুন।</li>
                            <li>Trial Balance বা Reports Center থেকে data consistency check করুন।</li>
                        </ol>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow-sm h-100 border-warning">
                    <div class="card-header bg-light">
                        <strong>Critical Controls</strong>
                    </div>
                    <div class="card-body">
                        <ul class="mb-0 pl-3">
                            <li class="mb-2">Closed period-এ posting হবে না।</li>
                            <li class="mb-2">Unbalanced journal submit করবেন না।</li>
                            <li class="mb-2">Reconciliation screen নতুন journal create করে না।</li>
                            <li class="mb-2">Voucher void-এর impact reports-এ পড়ে।</li>
                            <li>Report date filter ভুল হলে analysis ভুল হবে।</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div id="section-entry" class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <strong>End-to-End Entry Workflow</strong>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6 mb-3">
                        <div class="border rounded p-3 h-100">
                            <h3 class="h6 mb-2">Step 1: Universal Journal Voucher</h3>
                            <p class="text-muted mb-2">Accrual, adjustment, reclass, non-cash entries এখানে দিন।</p>
                            <p class="mb-0 small">Example: depreciation, provision, prepaid adjustment.</p>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <div class="border rounded p-3 h-100">
                            <h3 class="h6 mb-2">Step 2: Cash &amp; Bank Voucher</h3>
                            <p class="text-muted mb-2">Receipt/Payment transaction এখানে post করুন।</p>
                            <p class="mb-0 small">Example: supplier payment, customer collection, bank charge.</p>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <div class="border rounded p-3 h-100">
                            <h3 class="h6 mb-2">Step 3: Voucher Register Review</h3>
                            <p class="text-muted mb-2">Posted voucher number, lines, and narration verify করুন।</p>
                            <p class="mb-0 small">Mismatch হলে source screen-এ ফিরে correction দিন।</p>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <div class="border rounded p-3 h-100">
                            <h3 class="h6 mb-2">Step 4: Bank Reconciliation</h3>
                            <p class="text-muted mb-2">Statement upload করে existing internal ledger line-এর সাথে match করুন।</p>
                            <p class="mb-0 small">Internal line না থাকলে আগে voucher post করতে হবে।</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="section-rules" class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <strong>Against Account Logic (Journal + Cash/Bank)</strong>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Against Account select করার পর dynamic fields unlock/hide হয়। এটা data quality ensure করার নিয়ম।</p>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Against Account condition</th>
                                <th>কি unlock হবে</th>
                                <th>Business meaning</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Account type = Expense বা Revenue</td>
                                <td>Cost Center</td>
                                <td>Cost/profit tracking cost center wise করা যাবে</td>
                            </tr>
                            <tr>
                                <td>Account name-এ receivable বা payable আছে</td>
                                <td>Party Type + Party Ledger</td>
                                <td>Party-wise outstanding tracking করা যাবে</td>
                            </tr>
                            <tr>
                                <td>উপরের condition match করে না</td>
                                <td>Dependent fields hidden থাকবে</td>
                                <td>অপ্রয়োজনীয় data entry বন্ধ থাকে</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="alert alert-info mt-3 mb-0">
                    Short rule: Expense/Revenue -> Cost Center, Receivable/Payable -> Party fields.
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <strong>Bank Reconciliation Practical Rule</strong>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    Bank Reconciliation screen নতুন internal ledger entry create করে না।
                    আগে Journal/Cash-Bank voucher থেকে entry post করতে হবে, তারপর statement row-এর সাথে match করতে হবে।
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="border rounded p-3 h-100 bg-light">
                            <strong>Step 1</strong>
                            <p class="mb-0 text-muted small">Cash/Bank related voucher post করুন।</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="border rounded p-3 h-100 bg-light">
                            <strong>Step 2</strong>
                            <p class="mb-0 text-muted small">Bank statement entries upload করুন।</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="border rounded p-3 h-100 bg-light">
                            <strong>Step 3</strong>
                            <p class="mb-0 text-muted small">Matching checkbox দিয়ে rows pair করুন।</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="section-reports" class="card shadow-sm mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <strong>Report Matrix: কোন রিপোর্ট কখন</strong>
                <a href="{{ route('erpaccount.reports.index') }}" class="btn btn-sm btn-outline-success">Open Reports Center</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-sm mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Report</th>
                                <th>Primary use</th>
                                <th>Best time</th>
                                <th>Output focus</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Trial Balance</td>
                                <td>Ledger balancing check</td>
                                <td>Daily close / month end</td>
                                <td>Debit-credit integrity</td>
                            </tr>
                            <tr>
                                <td>Profit &amp; Loss</td>
                                <td>Income বনাম expense analysis</td>
                                <td>Weekly / monthly</td>
                                <td>Operating performance</td>
                            </tr>
                            <tr>
                                <td>Balance Sheet</td>
                                <td>Financial position review</td>
                                <td>Month end</td>
                                <td>Assets-Liabilities-Equity</td>
                            </tr>
                            <tr>
                                <td>Bank Reconciliation Statement</td>
                                <td>Book বনাম bank statement gap</td>
                                <td>Daily / weekly</td>
                                <td>Unreconciled items</td>
                            </tr>
                            <tr>
                                <td>Party Ledger</td>
                                <td>Buyer/Supplier outstanding review</td>
                                <td>Collection/payment planning</td>
                                <td>Party-wise movement</td>
                            </tr>
                            <tr>
                                <td>Voucher Register</td>
                                <td>Audit trail and voucher trace</td>
                                <td>Any verification time</td>
                                <td>Line-level evidence</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="section-errors" class="row mb-4">
            <div class="col-lg-6 mb-3">
                <div class="card shadow-sm h-100 border-danger">
                    <div class="card-header bg-light">
                        <strong>Common Errors and Fix</strong>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="font-weight-bold">Error: Posting blocked (period closed)</div>
                            <div class="text-muted">Fix: Financial Periods-এ date range open করুন।</div>
                        </div>
                        <div class="mb-3">
                            <div class="font-weight-bold">Error: Cost Center unlock হচ্ছে না</div>
                            <div class="text-muted">Fix: Against account Expense/Revenue কি না দেখুন।</div>
                        </div>
                        <div class="mb-3">
                            <div class="font-weight-bold">Error: Party Ledger list empty</div>
                            <div class="text-muted">Fix: Party Type select হয়েছে কি না এবং receivable/payable account কিনা check করুন।</div>
                        </div>
                        <div>
                            <div class="font-weight-bold">Error: Reconciliation-এ internal entries নেই</div>
                            <div class="text-muted">Fix: আগে Cash-Bank বা Journal voucher post করুন।</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-3">
                <div class="card shadow-sm h-100 border-success">
                    <div class="card-header bg-light">
                        <strong>Daily Closing Checklist</strong>
                    </div>
                    <div class="card-body">
                        <ul class="mb-0 pl-3">
                            <li class="mb-2">আজকের vouchers সব post হয়েছে কি না নিশ্চিত করুন।</li>
                            <li class="mb-2">Voucher Register থেকে random sample verify করুন।</li>
                            <li class="mb-2">Bank entry থাকলে reconciliation pending check করুন।</li>
                            <li class="mb-2">Trial Balance mismatch check করুন।</li>
                            <li class="mb-2">High-value party movement review করুন।</li>
                            <li>PDF/Excel export লাগলে archive করে রাখুন।</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <h3 class="h5 mb-3">Practical Examples</h3>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="border rounded p-3 h-100 bg-light">
                            <strong>Supplier Payment</strong>
                            <p class="mb-0 text-muted">Payment voucher -> Voucher Register verify -> Bank Reconciliation match.</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="border rounded p-3 h-100 bg-light">
                            <strong>Customer Collection</strong>
                            <p class="mb-0 text-muted">Receipt voucher -> Party check -> statement এলে reconciliation।</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="border rounded p-3 h-100 bg-light">
                            <strong>Month End Adjustment</strong>
                            <p class="mb-0 text-muted">Journal entries -> Trial Balance -> P&amp;L and Balance Sheet review.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
