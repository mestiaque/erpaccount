@extends(adminTheme().'layouts.app')

@section('title')
<title>{{websiteTitle('Integration Approval Queue')}}</title>
@endsection

@push('css')
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .approval-container {
            font-family: 'Hind Siliguri', 'Outfit', sans-serif;
            color: #1e293b;
            background-color: #f8fafc;
        }

        .header-hero {
            background: linear-gradient(135deg, #0f172a 0%, #3b82f6 100%);
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.08);
            color: #fff;
        }

        .stat-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            transition: all 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .nav-tabs-custom {
            border-bottom: 2px solid #e2e8f0;
        }

        .nav-tabs-custom .nav-link {
            border: none;
            border-bottom: 3px solid transparent;
            color: #64748b;
            font-weight: 600;
            padding: 12px 24px;
            transition: all 0.2s ease;
        }

        .nav-tabs-custom .nav-link:hover {
            color: #3b82f6;
            border-bottom-color: #cbd5e1;
        }

        .nav-tabs-custom .nav-link.active {
            color: #3b82f6;
            background: transparent;
            border-bottom-color: #3b82f6;
        }

        .table-custom thead th {
            background-color: #f1f5f9;
            color: #475569;
            font-weight: 600;
            border-bottom: 2px solid #cbd5e1;
            font-size: 13px;
        }

        .table-custom td {
            font-size: 13.5px;
            vertical-align: middle;
        }

        .payload-badge {
            background-color: #f1f5f9;
            color: #475569;
            font-family: monospace;
            font-size: 11px;
            padding: 4px 8px;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
        }
    </style>
@endpush

@section('contents')
<div class="approval-container flex-grow-1 p-2">
    <!-- Header Hero Section -->
    <div class="card border-0 header-hero mb-4">
        <div class="card-body p-4 p-lg-5">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <p class="small text-uppercase mb-2 font-weight-bold" style="letter-spacing: .25rem; color: #93c5fd;">Action & Warning Center</p>
                    <h1 class="h2 mb-3 text-white font-weight-bold">পেন্ডিং ইন্টিগ্রেশন অ্যাপ্রুভাল কিউ (Approval Queue)</h1>
                    <p class="mb-0 text-light" style="font-size: 1.05rem; opacity: 0.9;">
                        ইনভেন্টরি পোস্টিং ও পেরোল মডিউল থেকে আসা অটো-সিস্টেম ভাউচারগুলো বুক-কিপিং করার আগে ডেবিট/ক্রেডিট সমতা ও প্রজেক্ট কস্ট সেন্টার রিভিউ করুন।
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Feedback Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="fa fa-check-circle mr-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="fa fa-exclamation-triangle mr-2"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Summary Counters -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="card stat-card bg-white h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-primary text-white p-3 mr-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                        <i class="fa fa-clipboard-list fa-lg"></i>
                    </div>
                    <div>
                        <h6 class="text-muted small text-uppercase mb-1 font-weight-bold">মোট পেন্ডিং ভাউচার</h6>
                        <h3 class="mb-0 font-weight-bold">{{ $pendingInventory->count() + $pendingPayroll->count() }} টি</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="card stat-card bg-white h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-success text-white p-3 mr-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                        <i class="fa fa-boxes-stacked fa-lg"></i>
                    </div>
                    <div>
                        <h6 class="text-muted small text-uppercase mb-1 font-weight-bold">পেন্ডিং ইনভেন্টরি লগস</h6>
                        <h3 class="mb-0 font-weight-bold text-success">{{ $pendingInventory->count() }} টি</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card bg-white h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-warning text-white p-3 mr-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                        <i class="fa fa-wallet fa-lg"></i>
                    </div>
                    <div>
                        <h6 class="text-muted small text-uppercase mb-1 font-weight-bold">পেন্ডিং পেরোল ব্যাচ</h6>
                        <h3 class="mb-0 font-weight-bold text-warning">{{ $pendingPayroll->count() }} টি</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs Container -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white pb-0 pt-3 border-0">
            <ul class="nav nav-tabs nav-tabs-custom card-header-tabs" id="approvalTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="inventory-tab" data-toggle="tab" href="#inventory" role="tab" aria-controls="inventory" aria-selected="true">
                        <i class="fa fa-boxes mr-2"></i> ইনভেন্টরি পোস্টিং কিউ ({{ $pendingInventory->count() }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="payroll-tab" data-toggle="tab" href="#payroll" role="tab" aria-controls="payroll" aria-selected="false">
                        <i class="fa fa-users-gear mr-2"></i> পেরোল ইন্টিগ্রেশন কিউ ({{ $pendingPayroll->count() }})
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body p-4">
            <div class="tab-content" id="approvalTabsContent">
                
                <!-- Tab Pane: Inventory Queue -->
                <div class="tab-pane fade show active" id="inventory" role="tabpanel" aria-labelledby="inventory-tab">
                    @if($pendingInventory->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="fa fa-circle-check fa-4x text-success mb-3"></i>
                            <h5 class="font-weight-bold">পেন্ডিং ইনভেন্টরি কিউ সম্পূর্ণ খালি!</h5>
                            <p class="mb-0 small">সব ইনভেন্টরি ট্রানজ্যাকশন সফলভাবে পোস্ট করা হয়েছে।</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-custom">
                                <thead>
                                    <tr>
                                        <th>Log ID</th>
                                        <th>উৎস মডিউল</th>
                                        <th>ট্রানজ্যাকশন টাইপ</th>
                                        <th>রেফারেন্স নং</th>
                                        <th>বিবরণ</th>
                                        <th class="text-right">সিস্টেম মূল্য (BDT)</th>
                                        <th>পেলোড মেটাডাটা</th>
                                        <th class="text-center">অ্যাকশন</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingInventory as $item)
                                        <tr>
                                            <td class="font-weight-bold text-dark">#{{ $item->inventory_log_id }}</td>
                                            <td><span class="badge badge-info">{{ $item->source_module }}</span></td>
                                            <td><span class="badge badge-secondary">{{ str_replace('_', ' ', $item->transaction_type) }}</span></td>
                                            <td class="font-weight-bold text-primary">{{ $item->reference_no }}</td>
                                            <td>{{ $item->description }}</td>
                                            <td class="text-right font-weight-bold text-dark">{{ number_format((float) $item->system_valuation, 2) }}</td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-1">
                                                    @foreach($item->decoded_payload as $key => $val)
                                                        <span class="payload-badge mr-1 mb-1">{{ $key }}: {{ is_array($val) ? json_encode($val) : $val }}</span>
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <form method="POST" action="{{ route('erpaccount.approvals.approve-inventory', $item->inventory_log_id) }}" onsubmit="return confirm('আপনি কি নিশ্চিত যে এই ইনভেন্টরি ভাউচারটি অনুমোদন ও পোস্ট করতে চান?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm font-weight-bold">
                                                        <i class="fa fa-check-double mr-1"></i> Approve & Post
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <!-- Tab Pane: Payroll Queue -->
                <div class="tab-pane fade" id="payroll" role="tabpanel" aria-labelledby="payroll-tab">
                    @if($pendingPayroll->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="fa fa-circle-check fa-4x text-success mb-3"></i>
                            <h5 class="font-weight-bold">পেন্ডিং পেরোল কিউ সম্পূর্ণ খালি!</h5>
                            <p class="mb-0 small">সব পেরোল ট্রানজ্যাকশন সফলভাবে পোস্ট করা হয়েছে।</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-custom">
                                <thead>
                                    <tr>
                                        <th>Batch ID</th>
                                        <th>মাসের নাম</th>
                                        <th>বিবরণ ও বিবরণী</th>
                                        <th class="text-right">বেসিক বেতন (BDT)</th>
                                        <th class="text-right">ভাতা ও ওভারটাইম (BDT)</th>
                                        <th class="text-right">PF কর্তন (BDT)</th>
                                        <th class="text-right">অগ্রিম বেতন সমন্বয় (BDT)</th>
                                        <th class="text-right text-success">নিট প্রদেয় (BDT)</th>
                                        <th class="text-center">অ্যাকশন</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingPayroll as $item)
                                        <tr>
                                            <td class="font-weight-bold text-dark">#{{ $item->payroll_batch_id }}</td>
                                            <td><span class="badge badge-primary px-3 py-2" style="font-size: 12px;"><i class="fa fa-calendar-days mr-1"></i> {{ $item->payroll_month }}</span></td>
                                            <td>
                                                <div class="font-weight-bold text-dark">{{ $item->summary_label }}</div>
                                                <small class="text-muted">
                                                    স্টাফ সংখ্যা: {{ $item->decoded_payload['staff_count'] ?? 0 }} জন | শ্রমিক সংখ্যা: {{ $item->decoded_payload['factory_workers'] ?? 0 }} জন
                                                </small>
                                            </td>
                                            <td class="text-right font-weight-bold text-dark">{{ number_format((float) $item->total_basic, 2) }}</td>
                                            <td class="text-right font-weight-bold text-dark">{{ number_format((float) ($item->total_allowances + $item->total_overtime), 2) }}</td>
                                            <td class="text-right font-weight-bold text-danger">{{ number_format((float) $item->total_pf_deduction, 2) }}</td>
                                            <td class="text-right font-weight-bold text-danger">{{ number_format((float) $item->total_advance_adjusted, 2) }}</td>
                                            <td class="text-right font-weight-bold text-success">{{ number_format((float) $item->net_payable, 2) }}</td>
                                            <td class="text-center">
                                                <form method="POST" action="{{ route('erpaccount.approvals.approve-payroll', $item->payroll_batch_id) }}" onsubmit="return confirm('আপনি কি নিশ্চিত যে এই পেরোল ভাউচারটি অনুমোদন ও পোস্ট করতে চান?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm font-weight-bold">
                                                        <i class="fa fa-check-double mr-1"></i> Approve & Post
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
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
                var hash = window.location.hash;
                if (hash) {
                    $('.nav-tabs-custom a[href="' + hash + '"]').tab('show');
                }
            });
        })(jQuery);
    </script>
@endpush
