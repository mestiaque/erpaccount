@extends(adminTheme().'layouts.app')

@section('title')
<title>{{websiteTitle('Accounts System Operating Manual & Tutorial')}}</title>
@endsection

@push('css')
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Modern Premium Aesthetics */
        .tutorial-container {
            font-family: 'Hind Siliguri', 'Outfit', sans-serif;
            color: #1e293b;
            background-color: #f8fafc;
        }

        .gradient-hero {
            background: linear-gradient(135deg, #0f172a 0%, #0f766e 40%, #1e4ed8 100%);
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.15);
            transition: all 0.3s ease;
        }
        
        .gradient-hero:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(15, 23, 42, 0.2);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 12px;
            color: #fff;
        }

        /* Sidebar Navigation styling */
        .doc-sidebar {
            position: sticky;
            top: 20px;
            z-index: 100;
        }

        .doc-nav-item {
            font-weight: 500;
            color: #475569;
            border-left: 3px solid transparent;
            transition: all 0.2s ease;
            cursor: pointer;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            border-radius: 0;
            background: #fff;
        }

        .doc-nav-item:hover {
            color: #0f766e;
            background-color: #f1f5f9;
            border-left-color: #0d9488;
            padding-left: 20px;
        }

        .doc-nav-item.active {
            color: #fff !important;
            background: linear-gradient(90deg, #0f766e 0%, #0d9488 100%) !important;
            border-left-color: #0f172a;
            box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2);
        }

        .doc-nav-item i {
            width: 24px;
            font-size: 16px;
        }

        /* Card styles */
        .card-custom {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            margin-bottom: 24px;
            overflow: hidden;
        }

        .card-custom:hover {
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.08), 0 4px 6px -2px rgba(0,0,0,0.04);
        }

        .card-custom-header {
            background-color: #fff;
            border-bottom: 1px solid #f1f5f9;
            padding: 16px 20px;
        }

        .badge-custom {
            padding: 6px 12px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
        }

        /* Workflow Diagrams */
        .flow-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            background: #fff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        .flow-node {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border: 1px solid #cbd5e1;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
            min-width: 220px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.04);
            margin: 8px 0;
            transition: all 0.2s ease;
        }

        .flow-node:hover {
            transform: scale(1.03);
            border-color: #0d9488;
            background: linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 100%);
        }

        .flow-arrow {
            color: #94a3b8;
            font-size: 18px;
            margin: 4px 0;
        }

        /* Decision Tree widget */
        .decision-card {
            background: linear-gradient(135deg, #ffffff 0%, #f0fdfa 100%);
            border: 1px solid #ccfbf1;
            border-radius: 12px;
            padding: 24px;
        }

        /* Custom Alert colors */
        .alert-info-light {
            background-color: #f0fdfa;
            border-color: #ccfbf1;
            color: #0f766e;
        }

        .alert-warning-light {
            background-color: #fffbeb;
            border-color: #fef3c7;
            color: #b45309;
        }

        .alert-danger-light {
            background-color: #fef2f2;
            border-color: #fee2e2;
            color: #b91c1c;
        }

        /* Quick Links badge */
        .quick-badge {
            background-color: rgba(255, 255, 255, 0.15);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.25);
            transition: all 0.2s ease;
            margin-right: 8px;
            margin-bottom: 8px;
        }

        .quick-badge:hover {
            background-color: #fff;
            color: #0f766e;
            text-decoration: none;
        }

        /* Table header styling */
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

        /* Smooth section transitions */
        .doc-section {
            display: none;
            animation: fadeIn 0.4s ease-in-out forwards;
        }

        .doc-section.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .timeline-steps {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            margin-top: 20px;
        }

        .timeline-step {
            text-align: center;
            position: relative;
            z-index: 2;
            width: 25%;
        }

        .timeline-step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #cbd5e1;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin: 0 auto 10px auto;
            border: 4px solid #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .timeline-step.active .timeline-step-circle {
            background-color: #0d9488;
            color: white;
            box-shadow: 0 4px 10px rgba(13, 148, 136, 0.3);
        }

        .timeline-progress {
            position: absolute;
            top: 20px;
            left: 12.5%;
            width: 75%;
            height: 4px;
            background-color: #cbd5e1;
            z-index: 1;
        }

        .timeline-progress-bar {
            width: 100%;
            height: 100%;
            background-color: #0d9488;
            transition: width 0.3s ease;
        }

        .database-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 16px;
        }

        .db-table-card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        .db-table-header {
            background-color: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 8px 12px;
            font-weight: bold;
            font-size: 13.5px;
            color: #334155;
            font-family: 'Outfit', sans-serif;
        }

        .db-table-body {
            padding: 8px 12px;
            font-size: 12px;
        }

        .db-field {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            border-bottom: 1px dashed #f1f5f9;
        }

        .db-field:last-child {
            border-bottom: none;
        }

        .pk-indicator { color: #f59e0b; font-weight: bold; }
        .fk-indicator { color: #3b82f6; font-weight: bold; }

        /* ─── Module Tutorial Enhancements ─────────────────────────── */

        /* Alert-style callout boxes (like GitHub markdown alerts) */
        .alert-callout {
            border-left: 4px solid;
            border-radius: 0 8px 8px 0;
            padding: 12px 16px;
            margin-bottom: 16px;
        }
        .alert-callout-important {
            border-left-color: #f59e0b;
            background-color: #fffbeb;
            color: #78350f;
        }
        .alert-callout-important .callout-title { color: #d97706; font-weight: 700; margin-bottom: 4px; }
        .alert-callout-note {
            border-left-color: #3b82f6;
            background-color: #eff6ff;
            color: #1e3a8a;
        }
        .alert-callout-note .callout-title { color: #2563eb; font-weight: 700; margin-bottom: 4px; }
        .alert-callout-tip {
            border-left-color: #10b981;
            background-color: #ecfdf5;
            color: #064e3b;
        }
        .alert-callout-tip .callout-title { color: #059669; font-weight: 700; margin-bottom: 4px; }

        /* COA Mandatory Validation Table */
        .coa-mandatory-table thead th {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);
            color: #fff;
            font-weight: 600;
            font-size: 12.5px;
            border-bottom: none;
            vertical-align: middle;
        }
        .coa-mandatory-table thead th small { opacity: 0.75; }
        .coa-mandatory-table tbody tr:hover { background-color: #f0fdfa; }
        .coa-mandatory-table .keyword-cell code {
            background-color: #1e3a5f;
            color: #a7f3d0;
            padding: 2px 7px;
            border-radius: 4px;
            font-size: 11.5px;
            display: inline-block;
            margin-bottom: 2px;
        }
        .coa-mandatory-table .keyword-cell small { font-size: 10.5px; color: #94a3b8; }

        /* Dark journal entry preview block */
        .journal-preview {
            background-color: #0f172a;
            color: #e2e8f0;
            border-radius: 8px;
            padding: 14px 18px;
            font-family: 'Courier New', Consolas, monospace;
            font-size: 13px;
            line-height: 1.9;
            border-left: 4px solid #0d9488;
        }
        .journal-preview .dr  { color: #f87171; font-weight: bold; }
        .journal-preview .cr  { color: #34d399; font-weight: bold; }
        .journal-preview .arr { color: #64748b; }

        /* Transaction type selection cards */
        .trans-type-card {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 16px 12px;
            text-align: center;
            transition: all 0.2s ease;
            background: #fff;
        }
        .trans-type-card:hover {
            border-color: #0d9488;
            background-color: #f0fdfa;
            box-shadow: 0 4px 12px rgba(13,148,136,0.15);
        }

        /* Reconciliation formula block */
        .recon-formula {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px 20px;
            font-family: 'Courier New', Consolas, monospace;
            font-size: 13px;
            line-height: 2;
            color: #1e293b;
        }
        .recon-formula .recon-total { border-top: 1px solid #94a3b8; font-weight: bold; }
        .recon-formula .recon-success { color: #059669; font-weight: bold; }

        /* Developer seeder code block */
        .seeder-block {
            background-color: #0d1117;
            color: #c9d1d9;
            border-radius: 8px;
            padding: 18px 20px;
            font-family: 'Courier New', Consolas, monospace;
            font-size: 12px;
            line-height: 1.7;
            overflow-x: auto;
            border: 1px solid #30363d;
            max-height: 500px;
            overflow-y: auto;
        }
        .seeder-block .php-comment  { color: #8b949e; }
        .seeder-block .php-keyword  { color: #ff7b72; }
        .seeder-block .php-string   { color: #a5d6ff; }
        .seeder-block .php-variable { color: #ffa657; }
        .seeder-block .php-method   { color: #79c0ff; }
        .seeder-block .php-section  { color: #ffd700; font-weight: bold; }
    </style>
@endpush

@section('contents')
<div class="tutorial-container flex-grow-1 p-2">
    <!-- Gradient Hero Section -->
    <div class="card border-0 gradient-hero text-white mb-4">
        <div class="card-body p-4 p-lg-5">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <p class="small text-uppercase mb-2 font-weight-bold" style="letter-spacing: .25rem; color: #a7f3d0;">ERP Accounts Operating SOP</p>
                    <h1 class="h2 mb-3 text-white font-weight-bold">অ্যাকাউন্টিং মডিউল ইউজার ম্যানুয়াল ও SOP</h1>
                    <p class="mb-3 text-light" style="font-size: 1.05rem; line-height: 1.8; opacity: 0.95;">
                        এই নির্দেশিকাটি এমনভাবে তৈরি করা হয়েছে যাতে হিসাববিজ্ঞানে কোনো পূর্ব অভিজ্ঞতা না থাকা সত্ত্বেও যেকোনো সাধারণ ব্যবহারকারী পুরো ERP সিস্টেমের অ্যাকাউন্টিং মডিউলটি সাবলীলভাবে পরিচালনা করতে পারেন।
                    </p>
                    <div class="d-flex flex-wrap mt-4">
                        <span class="btn btn-light btn-sm mr-2 mb-2 px-3 quick-link-btn" data-target="section-overview">শুরু করুন</span>
                        <span class="btn quick-badge btn-sm mr-2 mb-2 px-3 quick-link-btn" data-target="section-coa">Chart of Accounts</span>
                        <span class="btn quick-badge btn-sm mr-2 mb-2 px-3 quick-link-btn" data-target="section-jv">Journal Entry</span>
                        <span class="btn quick-badge btn-sm mr-2 mb-2 px-3 quick-link-btn" data-target="section-cashbank">Receipt & Payment</span>
                        <span class="btn quick-badge btn-sm mr-2 mb-2 px-3 quick-link-btn" data-target="section-recon">Bank Reconciliation</span>
                        <span class="btn quick-badge btn-sm mr-2 mb-2 px-3 quick-link-btn" data-target="section-decision">লেজার সিলেকশন গাইড</span>
                        <span class="btn quick-badge btn-sm mr-2 mb-2 px-3 quick-link-btn" data-target="section-payroll">Payroll Module</span>
                        <span class="btn quick-badge btn-sm mr-2 mb-2 px-3 quick-link-btn" data-target="section-inventory">Inventory Journal</span>
                    </div>
                </div>
                <div class="col-lg-4 mt-4 mt-lg-0">
                    <div class="glass-card p-4">
                        <div class="small text-uppercase mb-2 font-weight-bold" style="letter-spacing: .15rem; color: #fef08a;">Core Accounts Flow</div>
                        <div class="h5 mb-3 text-white">সেটআপ → পোস্টিং → ম্যাচিং → অ্যানালিটিক্স</div>
                        <div style="line-height: 1.8; font-size: 13.5px; opacity: 0.9;">
                            ১. <strong>মাস্টার ডাটা সেটআপ</strong> (COA, ব্যাংক, কস্ট সেন্টার) <br>
                            ২. <strong>দৈনিক কার্যক্রম পোস্টিং</strong> (JV, Cash/Bank ভাউচার) <br>
                            ৩. <strong>ব্যাংক রিকনসিলিয়েশন</strong> (স্টেটমেন্ট লোড ও ম্যাচ)<br>
                            ৪. <strong>রিপোর্ট জেনারেশন</strong> (P&L, ট্রায়াল ব্যালেন্স, বিএস)
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Workspace with Sidebar and Content Panel -->
    <div class="row">
        <!-- Sticky Sidebar Navigation Column -->
        <div class="col-md-4 col-xl-3 mb-4">
            <div class="card shadow-sm doc-sidebar">
                <div class="card-header bg-dark text-white font-weight-bold py-3">
                    <i class="fa fa-book-open-reader mr-2 text-warning"></i> ম্যানুয়াল নেভিগেশন ম্যাপ
                </div>
                <div class="list-group list-group-flush" id="docNavList">
                    <div class="list-group-item bg-light text-uppercase font-weight-bold small text-muted px-3 py-2">ভূমিকা ও প্রস্তুতি</div>
                    <span class="list-group-item list-group-item-action doc-nav-item active" data-target="section-overview"><i class="fa fa-home"></i>ওভারভিউ ও কুইক স্টার্ট</span>
                    <span class="list-group-item list-group-item-action doc-nav-item" data-target="section-database"><i class="fa fa-database"></i>ডাটাবেজ ও আরকিটেকচার</span>
                    
                    <div class="list-group-item bg-light text-uppercase font-weight-bold small text-muted px-3 py-2">সিস্টেম কনফিগারেশন (Setup)</div>
                    <span class="list-group-item list-group-item-action doc-nav-item" data-target="section-coa"><i class="fa fa-sitemap"></i>Chart of Accounts</span>
                    <span class="list-group-item list-group-item-action doc-nav-item" data-target="section-bank"><i class="fa fa-university"></i>Bank & Cash Accounts</span>
                    <span class="list-group-item list-group-item-action doc-nav-item" data-target="section-cost-center"><i class="fa fa-tags"></i>Cost Center Management</span>
                    <span class="list-group-item list-group-item-action doc-nav-item" data-target="section-period"><i class="fa fa-calendar-alt"></i>Tax & Financial Period</span>
                    
                    <div class="list-group-item bg-light text-uppercase font-weight-bold small text-muted px-3 py-2">দৈনিক ভাউচার পোস্টিং</div>
                    <span class="list-group-item list-group-item-action doc-nav-item" data-target="section-jv"><i class="fa fa-edit"></i>Universal Journal Voucher</span>
                    <span class="list-group-item list-group-item-action doc-nav-item" data-target="section-cashbank"><i class="fa fa-wallet"></i>Cash & Bank Vouchers</span>
                    <span class="list-group-item list-group-item-action doc-nav-item" data-target="section-register"><i class="fa fa-list-ol"></i>Voucher Register</span>
                    <span class="list-group-item list-group-item-action doc-nav-item" data-target="section-recon"><i class="fa fa-sync-alt"></i>Bank Reconciliation</span>
                    
                    <div class="list-group-item bg-light text-uppercase font-weight-bold small text-muted px-3 py-2">মডিউল টিউটোরিয়াল (Module Tutorials)</div>
                    <span class="list-group-item list-group-item-action doc-nav-item" data-target="section-payroll"><i class="fa fa-calculator"></i>Payroll Management</span>
                    <span class="list-group-item list-group-item-action doc-nav-item" data-target="section-inventory"><i class="fa fa-boxes"></i>Inventory Financial Journal</span>
                    <span class="list-group-item list-group-item-action doc-nav-item" data-target="section-lc"><i class="fa fa-ship"></i>Commercial LC Tracker</span>
                    
                    <div class="list-group-item bg-light text-uppercase font-weight-bold small text-muted px-3 py-2">অ্যাকাউন্টিং ডিশিশন গাইড</div>
                    <span class="list-group-item list-group-item-action doc-nav-item text-success font-weight-bold" data-target="section-decision"><i class="fa fa-exchange-alt"></i>লেজার সিলেকশন ডিশিশন ট্রি</span>
                    <span class="list-group-item list-group-item-action doc-nav-item" data-target="section-logic-guides"><i class="fa fa-graduation-cap"></i>Voucher & Party Guides</span>
                    <span class="list-group-item list-group-item-action doc-nav-item" data-target="section-workflows"><i class="fa fa-random"></i>ব্যবসায়িক SOP ওয়ার্কফ্লো</span>
                    <span class="list-group-item list-group-item-action doc-nav-item text-primary font-weight-bold" data-target="section-demodata"><i class="fa fa-table"></i>১০ সেট টেস্ট ডেমো ডাটা গাইড</span>
                    
                    <div class="list-group-item bg-light text-uppercase font-weight-bold small text-muted px-3 py-2">সহায়তা ও ভুল সংশোধন</div>
                    <span class="list-group-item list-group-item-action doc-nav-item text-danger" data-target="section-troubleshoot"><i class="fa fa-exclamation-triangle"></i>সাধারণ ভুলসমূহ ও FAQ</span>
                </div>
            </div>
        </div>

        <!-- Documentation Content Panel Column -->
        <div class="col-md-8 col-xl-9">
            
            <!-- SECTION: Overview & Quick Start -->
            <div id="section-overview" class="doc-section active">
                <div class="card card-custom">
                    <div class="card-custom-header d-flex justify-content-between align-items-center">
                        <h2 class="h4 mb-0 text-primary font-weight-bold">১. ভূমিকা ও কুইক স্টার্ট গাইড (Go-Live Checklist)</h2>
                        <span class="badge badge-success badge-custom">Overview</span>
                    </div>
                    <div class="card-body">
                        <h4 class="h6 font-weight-bold text-dark mb-2">এই মডিউলের উদ্দেশ্য:</h4>
                        <p class="text-muted">
                            এই ERP সিস্টেমের অ্যাকাউন্টিং মডিউলটি মূলত একটি মাল্টি-লেয়ার ডাবল-এন্ট্রি ফাইন্যান্সিয়াল বুক-কিপিং ইঞ্জিন। যা কোম্পানির সাধারণ খরচ থেকে শুরু করে বেতন হিসাব, পণ্যের কাঁচামাল ক্রয়-বিক্রয় এবং এলসি ট্র্যাকিংয়ের খরচগুলো স্বয়ংক্রিয়ভাবে বা ম্যানুয়ালি ট্র্যাক ও জার্নাল তৈরি করতে পারে।
                        </p>
                        
                        <div class="row mt-4">
                            <div class="col-lg-6 mb-3">
                                <div class="border rounded p-3 bg-light h-100">
                                    <h5 class="h6 font-weight-bold text-success"><i class="fa fa-check-circle mr-2"></i>গো-লাইভ চেকলিস্ট (Go-Live Checklist)</h5>
                                    <p class="small text-muted">সিস্টেম চালু করার ঠিক আগে এই ধাপগুলো নিশ্চিত করুন:</p>
                                    <ol class="small pl-3 text-muted">
                                        <li class="mb-2"><strong>Chart of Accounts:</strong> কোম্পানির সাথে মানানসই লেজার অ্যাকাউন্টের চার্ট সাজানো শেষ করুন।</li>
                                        <li class="mb-2"><strong>Bank Mapping:</strong> সমস্ত সচল ব্যাংক অ্যাকাউন্ট লেজারের সাথে লিংক করুন।</li>
                                        <li class="mb-2"><strong>Cost Centers:</strong> কোম্পানির ডিপার্টমেন্ট/প্রজেক্ট তৈরি করে নিশ্চিত করুন।</li>
                                        <li class="mb-2"><strong>Financial Period:</strong> বর্তমান মাসটি 'Open' রাখা নিশ্চিত করুন যাতে পোস্টিং ব্লক না হয়।</li>
                                        <li class="mb-2"><strong>Test Journal:</strong> একটি টেস্ট জার্নাল এন্ট্রি দিয়ে ট্রায়াল ব্যালেন্সে চেক করুন।</li>
                                    </ol>
                                </div>
                            </div>
                            <div class="col-lg-6 mb-3">
                                <div class="border border-danger rounded p-3 bg-light h-100">
                                    <h5 class="h6 font-weight-bold text-danger"><i class="fa fa-shield-alt mr-2"></i>গুরুত্বপূর্ণ নিয়ন্ত্রণ ও অভ্যন্তরীণ নীতিমালা (Controls)</h5>
                                    <ul class="small pl-3 text-muted">
                                        <li class="mb-2"><strong>পিরিয়ড লক কন্ট্রোল:</strong> কোনো আর্থিক মাস ক্লোজ করে দিলে, ব্যাক-ডেটেড এন্ট্রি দিতে পারবেন না।</li>
                                        <li class="mb-2"><strong>অটো-ব্যালেন্সিং বাধা:</strong> ডেবিট এবং ক্রেডিট ফিগার সমান না হলে ভাউচার পোস্ট হবে না।</li>
                                        <li class="mb-2"><strong>রিকনসিলিয়েশন নিয়ম:</strong> রিকনসিলিয়েশন পেজ থেকে নতুন কোনো ব্যাংক এন্ট্রি তৈরি করা যায় না। এটি শুধু বুক এন্ট্রি ও ব্যাংক স্টেটমেন্টকে মেলায়।</li>
                                        <li class="mb-2"><strong>ভয়েড ভাউচার হিস্ট্রি:</strong> ভুল এন্ট্রি ডিলিট করা যায় না, অডিট ট্রেইল রাখার স্বার্থে শুধু 'Void' (বাতিল) করা যায়।</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- System Modules Navigation Map -->
                        <h4 class="h6 font-weight-bold text-dark mt-4 mb-3">Accounts System স্ক্রিন ও রাউট ম্যাপ:</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-custom">
                                <thead>
                                    <tr>
                                        <th>স্ক্রিন বা মডিউল</th>
                                        <th>রাউট বা লিংক</th>
                                        <th>কখন ও কেন ব্যবহার করবেন</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Chart of Accounts (COA)</strong></td>
                                        <td><code>/erpaccount/chart-of-accounts</code></td>
                                        <td>লেজার অ্যাকাউন্ট বা ব্যালেন্স শিটের হেড তৈরি করতে।</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Bank Accounts Link</strong></td>
                                        <td><code>/erpaccount/bank-accounts</code></td>
                                        <td>বাস্তব ব্যাংক অ্যাকাউন্ট সমূহের বিবরণ লেজারে ম্যাপিং করতে।</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Cost Centers</strong></td>
                                        <td><code>/erpaccount/cost-centers</code></td>
                                        <td>শাখা, ডিপার্টমেন্ট বা নির্দিষ্ট কাজের কস্ট ক্যাটাগরি তৈরি করতে।</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tax Rates & Financial Period</strong></td>
                                        <td><code>/erpaccount/tax-rates</code></td>
                                        <td>ভ্যাট রেট নির্ধারণ ও পোস্টিং মাস ক্লোজ বা লক করতে।</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Universal Voucher Entry</strong></td>
                                        <td><code>/erpaccount/journal-vouchers</code></td>
                                        <td>সমন্বয়, ক্যাশবিহীন ও জার্নাল পোস্টিংয়ের জন্য।</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Cash & Bank Vouchers</strong></td>
                                        <td><code>/erpaccount/cash-bank-vouchers</code></td>
                                        <td>নগদ বা ব্যাংকের মাধ্যমে যেকোনো রশিদ গ্রহণ ও পেমেন্ট করতে।</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Voucher Register</strong></td>
                                        <td><code>/erpaccount/voucher-register</code></td>
                                        <td>পোস্ট হওয়া সব ভাউচার খুঁজতে, দেখতে ও বাতিল করতে।</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Bank Reconciliation</strong></td>
                                        <td><code>/erpaccount/bank-reconciliation</code></td>
                                        <td>ব্যাংক স্টেটমেন্ট ও লেজারের গরমিল মেলাতে।</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Executive Dashboard</strong></td>
                                        <td><code>/erpaccount/executive-dashboard</code></td>
                                        <td>কোম্পানির আর্থিক স্বাস্থ্য ও ক্যাশ ব্যালেন্স একনজরে দেখতে।</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Reports Center</strong></td>
                                        <td><code>/erpaccount/reports</code></td>
                                        <td>ট্রায়াল ব্যালেন্স, লাভ-ক্ষতি ও ব্যালেন্স শিট দেখতে।</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION: Database Structure -->
            <div id="section-database" class="doc-section">
                <div class="card card-custom">
                    <div class="card-custom-header d-flex justify-content-between align-items-center">
                        <h2 class="h4 mb-0 text-primary font-weight-bold">২. ডাটাবেজ টেবিল স্ট্রাকচার ও আরকিটেকচার</h2>
                        <span class="badge badge-secondary badge-custom">Database schema</span>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">
                            ERP সিস্টেমটি কীভাবে ডাটা সংগ্রহ করে তা নিচে দেখানো হলো। এখানে মূল রিলেশনাল ডাটা টেবিল ও কলাম সমূহের লিস্ট দেওয়া হলো।
                        </p>

                        <div class="database-grid mt-3">
                            <!-- Table Card 1: Chart of accounts -->
                            <div class="db-table-card">
                                <div class="db-table-header">acc_chart_of_accounts</div>
                                <div class="db-table-body text-muted">
                                    <div class="db-field"><span class="font-weight-bold text-dark">account_id</span><span class="pk-indicator">PK (BigInt)</span></div>
                                    <div class="db-field"><span>account_code</span><span>Varchar (Unique)</span></div>
                                    <div class="db-field"><span>account_name</span><span>Varchar</span></div>
                                    <div class="db-field"><span>account_type</span><span>Enum (Asset, Liability, Equity, Revenue, Expense)</span></div>
                                    <div class="db-field"><span>parent_id</span><span class="fk-indicator">FK (Nullable)</span></div>
                                    <div class="db-field"><span>is_reconcilable</span><span>Boolean</span></div>
                                    <div class="db-field"><span>is_active</span><span>Boolean</span></div>
                                </div>
                            </div>
                            <!-- Table Card 2: Cost Centers -->
                            <div class="db-table-card">
                                <div class="db-table-header">acc_cost_centers</div>
                                <div class="db-table-body text-muted">
                                    <div class="db-field"><span class="font-weight-bold text-dark">cost_center_id</span><span class="pk-indicator">PK (BigInt)</span></div>
                                    <div class="db-field"><span>cost_center_code</span><span>Varchar (Unique)</span></div>
                                    <div class="db-field"><span>cost_center_name</span><span>Varchar</span></div>
                                    <div class="db-field"><span>cost_center_type</span><span>Varchar</span></div>
                                    <div class="db-field"><span>is_active</span><span>Boolean</span></div>
                                </div>
                            </div>
                            <!-- Table Card 3: Journal Masters -->
                            <div class="db-table-card">
                                <div class="db-table-header">acc_journal_masters</div>
                                <div class="db-table-body text-muted">
                                    <div class="db-field"><span class="font-weight-bold text-dark">journal_id</span><span class="pk-indicator">PK (BigInt)</span></div>
                                    <div class="db-field"><span>voucher_no</span><span>Varchar (Unique)</span></div>
                                    <div class="db-field"><span>journal_date</span><span>Date</span></div>
                                    <div class="db-field"><span>source_module</span><span>Varchar (Manual, Inventory, LC, Payroll)</span></div>
                                    <div class="db-field"><span>narration</span><span>Text (Nullable)</span></div>
                                    <div class="db-field"><span>is_voided</span><span>Boolean</span></div>
                                    <div class="db-field"><span>void_reason</span><span>Text (Nullable)</span></div>
                                </div>
                            </div>
                            <!-- Table Card 4: Journal Details -->
                            <div class="db-table-card">
                                <div class="db-table-header">acc_journal_details</div>
                                <div class="db-table-body text-muted">
                                    <div class="db-field"><span class="font-weight-bold text-dark">detail_id</span><span class="pk-indicator">PK (BigInt)</span></div>
                                    <div class="db-field"><span>journal_id</span><span class="fk-indicator">FK (acc_journal_masters)</span></div>
                                    <div class="db-field"><span>account_id</span><span class="fk-indicator">FK (acc_chart_of_accounts)</span></div>
                                    <div class="db-field"><span>cost_center_id</span><span class="fk-indicator">FK (Nullable)</span></div>
                                    <div class="db-field"><span>party_type</span><span>Enum (Buyer, Supplier, Employee, None)</span></div>
                                    <div class="db-field"><span>party_id</span><span>Int (External ID)</span></div>
                                    <div class="db-field"><span>debit_amount</span><span>Decimal (15,2)</span></div>
                                    <div class="db-field"><span>credit_amount</span><span>Decimal (15,2)</span></div>
                                    <div class="db-field"><span>reconciled_at</span><span>Datetime</span></div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info-light mt-4 mb-0">
                            <strong>রিলেশনাল লজিক:</strong> প্রতিটি ভাউচারের বিপরীতে ১টি <code>acc_journal_masters</code> রেকর্ড এবং ন্যূনতম ২টি <code>acc_journal_details</code> রেকর্ড ডেবিট-ক্রেডিট ব্যালেন্সিং অবস্থায় সেভ হয়।
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION: Chart of Accounts (COA) -->
            <div id="section-coa" class="doc-section">
                <div class="card card-custom">
                    <div class="card-custom-header d-flex justify-content-between align-items-center">
                        <h2 class="h4 mb-0 text-primary font-weight-bold">৩. Chart of Accounts (হিসাব তালিকা কনফিগারেশন)</h2>
                        <span class="badge badge-success badge-custom">Config</span>
                    </div>
                    <div class="card-body">
                        <h4 class="h6 font-weight-bold text-dark">এই মডিউলের কাজ:</h4>
                        <p class="text-muted">
                            Chart of Accounts হলো কোম্পানির সমস্ত লেজার বা হিসাবের তালিকা। সিস্টেমের সমস্ত ট্রানজ্যাকশন শেষ পর্যন্ত কোনো না কোনো লেজারে গিয়ে জমা হয়। এটি ট্রায়াল ব্যালেন্স, লাভ-ক্ষতি এবং ব্যালেন্স শিটের মূল অবকাঠামো গঠন করে।
                        </p>
                        
                        <div class="alert alert-info-light my-3">
                            <strong>কারা ব্যবহার করবে:</strong> কোম্পানি সেটআপের সময় ফাইন্যান্স ম্যানেজার বা চিফ অ্যাকাউন্ট্যান্ট। নতুন লেজার তৈরির প্রয়োজন হলে যেকোনো সিনিয়র মেম্বার।
                        </div>

                        <h4 class="h6 font-weight-bold text-dark mt-4">Screen Breakdown (স্ক্রিন বিবরণ ও ফিল্ডসমূহ):</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered table-custom">
                                <thead>
                                    <tr>
                                        <th>Field Name</th>
                                        <th>উদ্দেশ্য ও ভূমিকা</th>
                                        <th>প্রয়োজনীয়তা</th>
                                        <th>ডাটার উৎস</th>
                                        <th>উদাহরণ</th>
                                        <th>ভুল ডাটা দিলে কী ক্ষতি হবে</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Account Code</strong></td>
                                        <td>প্রতিটি হিসাবের অনন্য কোড নম্বর।</td>
                                        <td>Required (বাধ্যতামূলক)</td>
                                        <td>ইউজার টাইপ করবেন (কোম্পানির অ্যাকাউন্টিং পলিসি অনুযায়ী)।</td>
                                        <td><code>1110-001</code></td>
                                        <td>ডুপ্লিকেট কোড হলে ডাটাবেজ ইরর দেখাবে। অগোছালো কোড হলে রিপোর্ট ভুল গ্রুপে যাবে।</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Account Name</strong></td>
                                        <td>লেজার অ্যাকাউন্টের শিরোনাম।</td>
                                        <td>Required</td>
                                        <td>ইউজার টাইপ করবেন।</td>
                                        <td><code>Accounts Receivable</code>, <code>Office Rent</code></td>
                                        <td>ভুল টাইপ করলে বা বানান ভুল হলে ভাউচার পোস্টিংয়ের সময় ভুল লেজার সিলেক্ট হবে।</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Account Type</strong></td>
                                        <td>লেজারটি কোন ক্যাটাগরির হিসাব।</td>
                                        <td>Required</td>
                                        <td>ড্রপডাউন (Asset, Liability, Equity, Revenue, Expense)</td>
                                        <td><code>Asset</code> (সম্পদ) বা <code>Expense</code> (ব্যয়)</td>
                                        <td>ভুল টাইপ সিলেক্ট করলে খরচ চলে যাবে ব্যালেন্স শিটে আর অ্যাসেট চলে যাবে P&L রিপোর্টে। যা মারাত্মক কর ফাঁকি বা ভুল রিপোর্ট দেখাবে।</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Parent Account</strong></td>
                                        <td>মূল ক্যাটাগরি বা প্যারেন্ট লেজার যার আন্ডারে এই হিসাবটি থাকবে।</td>
                                        <td>Optional (প্যারেন্ট অ্যাকাউন্ট না থাকলে এটি নিজে রুট হবে)</td>
                                        <td>বিদ্যমান লেজার অ্যাকাউন্ট লিস্ট থেকে ড্রপডাউন</td>
                                        <td><code>Current Assets</code></td>
                                        <td>ভুল প্যারেন্ট দিলে হায়ারার্কি ভেঙ্গে যাবে ও সাব-টোটাল হিসেব ভুল হবে।</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Reconcilable</strong></td>
                                        <td>এই অ্যাকাউন্টটি ব্যাংক স্টেটমেন্টের সাথে মেলানো প্রয়োজন কি না।</td>
                                        <td>Optional (চেক বক্স)</td>
                                        <td>ইউজার চেক করবেন।</td>
                                        <td><code>Checked (টিক দেওয়া)</code></td>
                                        <td>চেক না করলে ব্যাংক রিকনসিলিয়েশন স্ক্রিনে এই ব্যাংক বা ক্যাশ অ্যাকাউন্টটি খুঁজে পাওয়া যাবে না।</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Buttons Breakdown -->
                        <h4 class="h6 font-weight-bold text-dark mt-4">বাটন সমূহের ভূমিকা:</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered table-custom">
                                <thead>
                                    <tr>
                                        <th>বাটন</th>
                                        <th>উদ্দেশ্য</th>
                                        <th>অ্যাকশন / ক্লিক করলে কী হবে</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Add Account</strong></td>
                                        <td>নতুন লেজার তৈরির ফর্ম ওপেন করা।</td>
                                        <td>একটি পপ-আপ ফর্ম (Modal) খুলবে যেখানে লেজারের বিস্তারিত ইনপুট নেওয়া হবে।</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Save Account</strong></td>
                                        <td>নতুন ইনপুট দেওয়া লেজার ডাটাবেজে রেকর্ড করা।</td>
                                        <td>ডাটা ভ্যালিডেট করে সফল হলে ডাটাবেজে সেভ করবে এবং স্ক্রিন রিফ্রেশ করবে।</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Edit (পেন্সিল আইকন)</strong></td>
                                        <td>বিদ্যমান লেজার সংশোধন করা।</td>
                                        <td>নির্দিষ্ট লেজারের তথ্য এডিট ফর্মে লোড করবে। কোড পরিবর্তন বা নিষ্ক্রিয় করার জন্য এটি ব্যবহৃত হয়।</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Business Scenario -->
                        <div class="border border-success rounded p-3 bg-light mt-4">
                            <h5 class="h6 font-weight-bold text-success"><i class="fa fa-lightbulb"></i> বাস্তব ব্যবসায়িক উদাহরণ (Scenario):</h5>
                            <p class="small text-muted">
                                <strong>দৃশ্যপট:</strong> নতুন ফিনান্সিয়াল বছর শুরু হয়েছে। কোম্পানি নতুন একটি ব্যাংক অ্যাকাউন্ট ডাচ-বাংলা ব্যাংকে ওপেন করেছে এবং হিসাব বহিতে এটি যুক্ত করতে চায়।
                            </p>
                            <ol class="small pl-3 text-muted">
                                <li><code>Chart of Accounts</code> স্ক্রিনে যান। <strong>Add Account</strong> বাটনে ক্লিক করুন।</li>
                                <li><code>Account Code</code> বক্সে দিন <code>1120-005</code> এবং <code>Account Name</code> দিন <code>DBBL Bank A/C 1205</code>।</li>
                                <li><code>Account Type</code> সিলেক্ট করুন <code>Asset</code> এবং <code>Parent Account</code> সিলেক্ট করুন <code>Cash & Bank Accounts</code>।</li>
                                <li><code>Reconcilable</code> বক্সে টিক দিন এবং <code>Active</code> বক্সে টিক দিন।</li>
                                <li><strong>Save Account</strong> বাটনে ক্লিক করুন। লেজারটি সফলভাবে তৈরি হবে।</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION: Bank & Cash Accounts Mapping -->
            <div id="section-bank" class="doc-section">
                <div class="card card-custom">
                    <div class="card-custom-header d-flex justify-content-between align-items-center">
                        <h2 class="h4 mb-0 text-primary font-weight-bold">৪. Bank & Cash Accounts (ব্যাংক হিসাব ম্যাপিং)</h2>
                        <span class="badge badge-success badge-custom">Config</span>
                    </div>
                    <div class="card-body">
                        <h4 class="h6 font-weight-bold text-dark">এই মডিউলের কাজ:</h4>
                        <p class="text-muted">
                            Chart of Accounts-এ তৈরি করা ব্যাংক লেজার অ্যাকাউন্টের সাথে বাস্তব ব্যাংকের অ্যাকাউন্ট নম্বর ও ব্রাঞ্চের সংযোগ স্থাপন করা।
                        </p>
                        <h4 class="h6 font-weight-bold text-dark mt-4">Screen Breakdown & Field Details:</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered table-custom">
                                <thead>
                                    <tr>
                                        <th>Field Name</th>
                                        <th>উদ্দেশ্য</th>
                                        <th>বাধ্যতামূলক?</th>
                                        <th>ডাটার উৎস</th>
                                        <th>উদাহরণ</th>
                                        <th>ভুল দিলে সমস্যা</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Bank Name</strong></td>
                                        <td>ব্যাংকের আনুষ্ঠানিক নাম।</td>
                                        <td>Required</td>
                                        <td>ইউজার টাইপ করবেন।</td>
                                        <td><code>Dutch-Bangla Bank PLC</code></td>
                                        <td>রিকনসিলিয়েশন বা পেমেন্ট ভাউচারের রিপোর্টে ভুল ব্যাংক নাম প্রিন্ট হবে।</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Branch Name</strong></td>
                                        <td>ব্যাংকের নির্দিষ্ট ব্রাঞ্চের নাম।</td>
                                        <td>Required</td>
                                        <td>ইউজার টাইপ করবেন।</td>
                                        <td><code>Motijheel Branch</code></td>
                                        <td>ভুল ব্রাঞ্চ দিলে ব্যাংক ডিরেক্টরি বা চেক প্রিন্টিংয়ে সমস্যা হবে।</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Account Number</strong></td>
                                        <td>ব্যাংক হিসাবের মূল নাম্বার।</td>
                                        <td>Required</td>
                                        <td>ইউজার টাইপ করবেন।</td>
                                        <td><code>102.120.14567</code></td>
                                        <td>স্টেটমেন্ট আপলোড করার সময় মিলবে না, ভুল অ্যাকাউন্টে পোস্টিং হতে পারে।</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Account Type</strong></td>
                                        <td>হিসাবের ধরন (চলতি, সঞ্চয়ী ইত্যাদি)।</td>
                                        <td>Required</td>
                                        <td>ইউজার টাইপ করবেন।</td>
                                        <td><code>Current</code>, <code>Savings</code></td>
                                        <td>ডকুমেন্টেশন তৈরিতে ভুল দেখাবে।</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Mapped Ledger</strong></td>
                                        <td>COA থেকে তৈরি করা লেজারটি যার সাথে বাস্তব ব্যাংকের ট্রানজ্যাকশন কানেক্ট হবে।</td>
                                        <td>Required</td>
                                        <td>ড্রপডাউন (COA এর Asset টাইপ লেজার লিস্ট)</td>
                                        <td><code>1120-005 - DBBL Bank A/C 1205</code></td>
                                        <td>ভুল লেজার ম্যাপ করলে সম্পূর্ণ ব্যাংকিং লেনদেন ভুল লেজারে পোস্টিং হবে।</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Buttons Breakdown -->
                        <h4 class="h6 font-weight-bold text-dark mt-4">বাটন সমূহের ভূমিকা:</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered table-custom">
                                <thead>
                                    <tr>
                                        <th>বাটন</th>
                                        <th>উদ্দেশ্য</th>
                                        <th>ক্লিক করলে কী হবে</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Add Bank Account</strong></td>
                                        <td>ব্যাংক লিংক করার ফর্ম ওপেন করা।</td>
                                        <td>নতুন এন্ট্রির পপ-আপ ফর্ম ওপেন হবে।</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Edit Account</strong></td>
                                        <td>ব্যাংকের লিংক বা ইনফরমেশন আপডেট করা।</td>
                                        <td>বর্তমান ব্যাংক ডাটা ফর্মে লোড করবে এবং সাবমিট দিলে ডাটা আপডেট করবে।</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Delete Account</strong></td>
                                        <td>ম্যাপিং ডিলিট করা।</td>
                                        <td>লেনদেন শুরু না হলে ডিলিট সফল হবে, ট্রানজ্যাকশন থাকলে সিস্টেম ব্লক করবে।</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION: Cost Center Management -->
            <div id="section-cost-center" class="doc-section">
                <div class="card card-custom">
                    <div class="card-custom-header d-flex justify-content-between align-items-center">
                        <h2 class="h4 mb-0 text-primary font-weight-bold">৫. Cost Center Management (ব্যয় কেন্দ্র ব্যবস্থাপনা)</h2>
                        <span class="badge badge-success badge-custom">Config</span>
                    </div>
                    <div class="card-body">

                        <div class="alert-callout alert-callout-note mb-4">
                            <strong>Cost Center কী?</strong> Cost Center হলো একটি ট্যাগ বা লেবেল — যেটি দিয়ে কোনো খরচ বা আয় কোন বিভাগ/প্রজেক্ট/স্টাইলের জন্য হয়েছে তা চিহ্নিত করা হয়।
                            COA-তে শুধু লেজার থাকে (যেমন: Electricity Bill), কিন্তু কস্ট সেন্টার দিয়ে বলা যায় এই বিলটি কোন ফ্লোরের বা কোন অর্ডারের।
                        </div>

                        <h5 class="font-weight-bold text-success mt-3 mb-3"><i class="fa fa-th-large mr-2"></i>Cost Center Type — কত ধরনের?</h5>
                        <div class="table-responsive mb-4">
                            <table class="table table-sm table-bordered">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Type</th>
                                        <th>বাংলা নাম</th>
                                        <th>কখন ব্যবহার করবেন</th>
                                        <th>উদাহরণ</th>
                                        <th>রিপোর্টে প্রভাব</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Department</strong></td>
                                        <td>বিভাগ</td>
                                        <td>অফিসের স্থায়ী বিভাগের খরচ ট্র্যাক করতে</td>
                                        <td><code>Cutting Dept</code>, <code>Admin Office</code></td>
                                        <td>Department-wise P&amp;L রিপোর্টে আলাদা লাইন</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Branch</strong></td>
                                        <td>শাখা</td>
                                        <td>একাধিক শাখা/কারখানা আলাদাভাবে ম্যানেজ করতে</td>
                                        <td><code>Factory – Hatiabo</code>, <code>Factory – Mirpur</code></td>
                                        <td>Branch-wise খরচ তুলনামূলক রিপোর্ট</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Project</strong></td>
                                        <td>প্রকল্প</td>
                                        <td>নির্দিষ্ট মেয়াদি কাজের খরচ ট্র্যাক করতে</td>
                                        <td><code>New Building Construction</code>, <code>ERP Implementation</code></td>
                                        <td>Project-wise খরচ বনাম বাজেট তুলনা</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Style / Order</strong></td>
                                        <td>স্টাইল / অর্ডার</td>
                                        <td>RMG: প্রতিটি Buyer Order-এর কস্টিং করতে</td>
                                        <td><code>Style-H&amp;M-2024-001</code>, <code>PO-ZARA-456</code></td>
                                        <td>Order-wise CM Cost ও Profitability রিপোর্ট</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h5 class="font-weight-bold text-success mt-4 mb-3"><i class="fa fa-lock-open mr-2"></i>Cost Center কখন Unlock হয়? (ভাউচারে)</h5>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="border-left border-success pl-3 py-2 mb-3">
                                    <strong class="text-success d-block mb-1"><i class="fa fa-check-circle mr-1"></i>Unlock হবে (সিলেক্ট করা বাধ্যতামূলক)</strong>
                                    <p class="small text-muted mb-0">COA-তে সিলেক্ট করা লেজারের <strong>Account Type = Expense</strong> অথবা <strong>Account Type = Revenue</strong> হলে।</p>
                                    <p class="small text-muted mt-1 mb-0">উদাহরণ: Electricity Bill (Expense), Sales Revenue (Revenue)</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border-left border-danger pl-3 py-2 mb-3">
                                    <strong class="text-danger d-block mb-1"><i class="fa fa-times-circle mr-1"></i>Locked থাকবে (নিষ্ক্রিয়)</strong>
                                    <p class="small text-muted mb-0">সিলেক্ট করা লেজারের Account Type = <strong>Asset</strong>, <strong>Liability</strong>, বা <strong>Equity</strong> হলে।</p>
                                    <p class="small text-muted mt-1 mb-0">উদাহরণ: Cash in Hand (Asset), Salary Payable (Liability)</p>
                                </div>
                            </div>
                        </div>

                        <h5 class="font-weight-bold text-success mt-4 mb-3"><i class="fa fa-chart-bar mr-2"></i>রিপোর্টে Cost Center-এর প্রভাব</h5>
                        <div class="table-responsive mb-4">
                            <table class="table table-sm table-bordered">
                                <thead style="background:#1e3a5f; color:#fff;">
                                    <tr>
                                        <th>রিপোর্ট নাম</th>
                                        <th>Cost Center ছাড়া</th>
                                        <th>Cost Center সহ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Profit &amp; Loss Statement</td>
                                        <td>শুধু পুরো কোম্পানির মোট লাভ/ক্ষতি</td>
                                        <td>প্রতিটি Department / Branch-এর আলাদা P&amp;L দেখা যাবে</td>
                                    </tr>
                                    <tr>
                                        <td>Expense Analysis Report</td>
                                        <td>শুধু লেজার অনুযায়ী খরচের তালিকা</td>
                                        <td>কোন বিভাগ সবচেয়ে বেশি বিদ্যুৎ/ভাড়া খরচ করেছে</td>
                                    </tr>
                                    <tr>
                                        <td>Order / Style Costing</td>
                                        <td>মোট উৎপাদন খরচ</td>
                                        <td>প্রতিটি Buyer Order-এর আলাদা CM Cost দেখা যাবে</td>
                                    </tr>
                                    <tr>
                                        <td>Trial Balance</td>
                                        <td>সকল লেজারের ব্যালেন্স একসাথে</td>
                                        <td>নির্দিষ্ট Cost Center Filter করে দেখা যাবে</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h5 class="font-weight-bold text-success mt-4 mb-3"><i class="fa fa-keyboard mr-2"></i>Field Details (ক্ষেত্রসমূহ)</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Field Name</th>
                                        <th>উদ্দেশ্য</th>
                                        <th>Required</th>
                                        <th>উদাহরণ</th>
                                        <th>ভুল দিলে সমস্যা</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Cost Center Code</strong></td>
                                        <td>অনন্য কোড নম্বর</td>
                                        <td><span class="badge badge-danger">Required</span></td>
                                        <td><code>CC-DHAKA-01</code></td>
                                        <td>ডুপ্লিকেট কোড সাবমিট হবে না</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Cost Center Name</strong></td>
                                        <td>ব্যয় কেন্দ্রের নাম</td>
                                        <td><span class="badge badge-danger">Required</span></td>
                                        <td><code>Cutting Dept</code>, <code>Factory – Hatiabo</code></td>
                                        <td>রিপোর্টে ভুল নাম দেখাবে</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Cost Center Type</strong></td>
                                        <td>ক্যাটাগরি (Department / Branch / Project / Style)</td>
                                        <td><span class="badge badge-danger">Required</span></td>
                                        <td><code>Department</code></td>
                                        <td>ফিল্টার ও গ্রুপিং ভুল হবে</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>

            <!-- SECTION: Tax & Financial Periods -->
            <div id="section-period" class="doc-section">
                <div class="card card-custom">
                    <div class="card-custom-header d-flex justify-content-between align-items-center">
                        <h2 class="h4 mb-0 text-primary font-weight-bold">৬. Tax Rates & Financial Periods (ভ্যাট ও পোস্টিং সময়সীমা)</h2>
                        <span class="badge badge-success badge-custom">Config</span>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-6">
                                <h4 class="h6 font-weight-bold text-dark">Tax / VAT Configuration:</h4>
                                <p class="text-muted small">
                                    কোম্পানির ট্যাক্স ও ভ্যাট পারসেন্টেজ আগে থেকে ম্যাপ করে রাখা হয় এবং ট্যাক্স কর্তনকারী লেজারকে লিংক করা হয়।
                                </p>
                                <ul class="small pl-3 text-muted">
                                    <li><strong>Tax Name:</strong> ভ্যাট বা সোর্স ট্যাক্সের নাম (যেমন: <code>VAT 5%</code>)।</li>
                                    <li><strong>Percentage:</strong> হার (যেমন: <code>5.00</code>)।</li>
                                    <li><strong>Ledger Account:</strong> এই ট্যাক্সের টাকা জমা হওয়ার লায়াবিলিটি লেজার (যেমন: <code>VAT Payable A/C</code>)।</li>
                                </ul>
                            </div>
                            <div class="col-lg-6">
                                <h4 class="h6 font-weight-bold text-dark">Financial Period Setup:</h4>
                                <p class="text-muted small">
                                    অ্যাকাউন্টিং মাস শুরু ও বন্ধ করার নিয়ন্ত্রণ এটি। যখন কোনো মাস অডিট বা সম্পূর্ণ ক্লোজ হয়ে যায়, তার সুইচটি অফ (Closed) করে দিলে কেউ আর সেখানে ব্যাক-ডেটেড পোস্টিং দিতে পারবে না।
                                </p>
                                <ul class="small pl-3 text-muted">
                                    <li><strong>Period Name:</strong> যেমন: <code>June 2026</code>।</li>
                                    <li><strong>Start & End Date:</strong> ১লা জুন ২০২৬ থেকে ৩০শে জুন ২০২৬।</li>
                                    <li><strong>Status Switch (Open/Closed):</strong> অন করলে খোলা থাকবে, অফ করলে লকড।</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION: Universal Journal Voucher -->
            <div id="section-jv" class="doc-section">
                <div class="card card-custom">
                    <div class="card-custom-header d-flex justify-content-between align-items-center">
                        <h2 class="h4 mb-0 text-primary font-weight-bold">৭. Universal Journal Voucher (জার্নাল এন্ট্রি স্ক্রিন)</h2>
                        <span class="badge badge-success badge-custom">Daily Operations</span>
                    </div>
                    <div class="card-body">
                        <h4 class="h6 font-weight-bold text-dark">মডিউলের কাজ ও উদ্দেশ্য:</h4>
                        <p class="text-muted">
                            জার্নাল ভাউচার (JV) হলো সাধারণ ডাবল-এন্ট্রি বুক-কিপিং ফর্ম। সমন্বয় দাখিলা (Adjustment), প্রিপেইড খরচ ট্রান্সফার, অবচয় হিসাবভুক্তকরণ এবং বাকিতে ক্যাশবিহীন কোনো ট্রানজ্যাকশন পোস্টিংয়ের জন্য এটি ব্যবহার করা হয়।
                        </p>

                        <div class="alert alert-warning-light my-3">
                            <strong>কন্ট্রোল রুলস:</strong> ডেবিট কলামের মোট যোগফল এবং ক্রেডিট কলামের মোট যোগফল অবশ্যই সমান হতে হবে। নতুবা ভাউচার সাবমিট বাটন অ্যাক্টিভ হবে না।
                        </div>

                        <h4 class="h6 font-weight-bold text-dark mt-4">Field Details (ইনপুট ডাটা বিবরণ):</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered table-custom">
                                <thead>
                                    <tr>
                                        <th>Field Name</th>
                                        <th>উদ্দেশ্য</th>
                                        <th>বাধ্যতামূলক?</th>
                                        <th>ডাটার উৎস</th>
                                        <th>উদাহরণ</th>
                                        <th>ভুল ডাটা দিলে কী হবে</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Journal Date</strong></td>
                                        <td>লেনদেন সংঘটিত হওয়ার তারিখ।</td>
                                        <td>Required</td>
                                        <td>ইউজার ইনপুট (তারিখ পিকার)</td>
                                        <td><code>2026-06-13</code></td>
                                        <td>ভুল তারিখ দিলে ভুল আর্থিক বছরে বা ক্লোজড পিরিয়ডে পোস্টিং হয়ে ইরর দেখাবে।</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Narration</strong></td>
                                        <td>পুরো ট্রানজ্যাকশনের একটি সংক্ষিপ্ত ব্যাখ্যা।</td>
                                        <td>Optional (পছন্দনীয়)</td>
                                        <td>ইউজার টাইপ করবেন।</td>
                                        <td><code>Monthly Depreciation Entry for June</code></td>
                                        <td>খুঁজে পেতে ও অডিটের সময় সমস্যা হবে।</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Row: Account</strong></td>
                                        <td>নির্দিষ্ট রো এর জন্য ডেবিট বা ক্রেডিট লেজার।</td>
                                        <td>Required</td>
                                        <td>ড্রপডাউন (বিদ্যমান COA লেজার লিস্ট)</td>
                                        <td><code>Office Rent Expense</code></td>
                                        <td>ভুল অ্যাকাউন্ট সিলেক্ট করলে ভুল লেজারে ব্যালেন্স চলে যাবে।</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Row: Cost Center</strong></td>
                                        <td>খরচটি কোন কস্ট সেন্টারের অধীনে তা চিহ্নিত করা।</td>
                                        <td>অটো-লক (অ্যাকাউন্ট টাইপ Expense বা Revenue হলে আনলক হবে)</td>
                                        <td>ড্রপডাউন (সচল কস্ট সেন্টার লিস্ট)</td>
                                        <td><code>Dhaka Branch</code></td>
                                        <td>কস্ট সেন্টার ছাড়া খরচ সেভ করলে ডিপার্টমেন্টাল লাভ-ক্ষতি ভুল দেখাবে।</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Row: Party Type & Party Ledger</strong></td>
                                        <td>কোনো ক্রেতা, বিক্রেতা বা কর্মচারীর বকেয়া অ্যাকাউন্টে ট্রানজ্যাকশন চিহ্নিত করা।</td>
                                        <td>অটো-লক (অ্যাকাউন্ট নামে receivable বা payable থাকলে আনলক হবে)</td>
                                        <td>ড্রপডাউন (সাপ্লায়ার, কাস্টমার বা কর্মচারীর তালিকা)</td>
                                        <td><code>Rahim Traders</code></td>
                                        <td>পার্টি সিলেক্ট না করলে কার কাছে কত টাকা পাওনা বা দেনা তা ব্যালেন্স শিটে ডিরেক্টরি অনুযায়ী মিলবে না।</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Row: Debit / Credit</strong></td>
                                        <td>টাকার পরিমাণ। একটি রোতে ডেবিট দিলে ক্রেডিট শূন্য থাকবে, অথবা ক্রেডিট দিলে ডেবিট শূন্য থাকবে।</td>
                                        <td>Required (০ এর বেশি হতে হবে)</td>
                                        <td>ইউজার টাইপ করবেন।</td>
                                        <td><code>15000.00</code></td>
                                        <td>ভুল এন্ট্রি ব্যালেন্সিং ভেঙে ফেলবে ও এন্ট্রি সেভ করতে বাধা দিবে।</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Buttons Breakdown -->
                        <h4 class="h6 font-weight-bold text-dark mt-4">বাটন সমূহের ভূমিকা:</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered table-custom">
                                <thead>
                                    <tr>
                                        <th>বাটন</th>
                                        <th>উদ্দেশ্য</th>
                                        <th>ক্লিক করলে কী হবে</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Add New Row</strong></td>
                                        <td>ভাউচারে অতিরিক্ত লাইন যুক্ত করা (মাল্টিপল ডেবিট/ক্রেডিট এর জন্য)।</td>
                                        <td>টেবিলে ডাইনামিকালি নতুন ইনপুট রো যুক্ত হবে।</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Submit Voucher</strong></td>
                                        <td>ভাউচারটি পাকাপাকিভাবে ডাটাবেজে রেকর্ড করা।</td>
                                        <td>ডেবিট-ক্রেডিট ব্যালেন্স চেক করবে এবং ভাউচারটি পোস্টিং করে জার্নাল নাম্বার তৈরি করবে।</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Remove Row (লাল ট্রাশ আইকন)</strong></td>
                                        <td>ভুল তৈরি হওয়া রো মুছে ফেলা।</td>
                                        <td>নির্দিষ্ট রো টি মুছে দিবে এবং টোটাল ডেবিট-ক্রেডিট নতুন করে যোগ করবে।</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION: Cash & Bank Vouchers -->
            <div id="section-cashbank" class="doc-section">
                <div class="card card-custom">
                    <div class="card-custom-header d-flex justify-content-between align-items-center">
                        <h2 class="h4 mb-0 text-primary font-weight-bold">৮. Cash & Bank Vouchers (ক্যাশ ও ব্যাংক ভাউচার পোস্টিং)</h2>
                        <span class="badge badge-success badge-custom">Daily Operations</span>
                    </div>
                    <div class="card-body">
                        <h4 class="h6 font-weight-bold text-dark">এই মডিউলের কাজ:</h4>
                        <p class="text-muted">
                            টাকা নগদ বা ব্যাংকের মাধ্যমে লেনদেন হলে এই মডিউল ব্যবহার করা হয়। এর দুইটি সাব-ট্যাব আছে:
                            <br>১. <strong>Receipt Voucher (RV):</strong> টাকা ব্যাংক বা ক্যাশে ঢুকলে (ক্যাশ/ব্যাংক ডেবিট হলে)।
                            <br>২. <strong>Payment Voucher (PV):</strong> ক্যাশ বা ব্যাংক থেকে টাকা চলে গেলে (ক্যাশ/ব্যাংক ক্রেডিট হলে)।
                        </p>

                        <div class="alert alert-info-light my-3">
                            <strong>সুবিধা:</strong> এখানে প্রতি লাইনে ক্যাশ/ব্যাংক সিলেক্ট করতে হয় না। উপরে শুধু ১টি Main Cash/Bank Account সিলেক্ট করতে হয় এবং নিচে ট্রানজ্যাকশনের বিপরীতে থাকা Against Accounts সমূহ যোগ করতে হয়। সিস্টেম অটোমেটিক ব্যালেন্স তৈরি করে নেয়।
                        </div>

                        <h4 class="h6 font-weight-bold text-dark mt-4">Field Details (ইনপুট বিবরণ):</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered table-custom">
                                <thead>
                                    <tr>
                                        <th>Field Name</th>
                                        <th>উদ্দেশ্য</th>
                                        <th>বাধ্যতামূলক?</th>
                                        <th>ডাটার উৎস</th>
                                        <th>উদাহরণ</th>
                                        <th>ভুল ডাটা দিলে সমস্যা</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Voucher Date</strong></td>
                                        <td>পেমেন্ট বা রশিদের তারিখ।</td>
                                        <td>Required</td>
                                        <td>ইউজার সিলেক্ট করবেন।</td>
                                        <td><code>2026-06-13</code></td>
                                        <td>ভুল ডেটে পোস্টিং হবে।</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Main Cash/Bank Account</strong></td>
                                        <td>কোম্পানির কোন ব্যাংক অ্যাকাউন্ট বা ক্যাশ বক্স থেকে টাকা লেনদেন হচ্ছে।</td>
                                        <td>Required</td>
                                        <td>ড্রপডাউন (Asset টাইপের ক্যাশ ও ব্যাংক লেজারসমূহ)</td>
                                        <td><code>1120-001 - Petty Cash Account</code></td>
                                        <td>ভুল ব্যাংক থেকে টাকা কম বা বেশি হয়ে বুক ক্যাশ ব্যালেন্স ও ব্যাংক স্টেটমেন্ট অমিল হবে।</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Against Account (রো ফিল্ড)</strong></td>
                                        <td>টাকাটা কার বিরুদ্ধে বা কোন খরচের বিপরীতে পেমেন্ট হচ্ছে বা রিসিভ হচ্ছে।</td>
                                        <td>Required</td>
                                        <td>ড্রপডাউন (COA এর সমস্ত সাধারণ লেজারসমূহ)</td>
                                        <td><code>Accounts Payable</code> (পেমেন্টের ক্ষেত্রে) বা <code>Revenue</code> (রশিদের ক্ষেত্রে)</td>
                                        <td>ভুল অ্যাকাউন্ট দিলে ব্যালেন্স শিটের লেজার অ্যাডজাস্টমেন্ট অমিল হবে।</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Amount (রো ফিল্ড)</strong></td>
                                        <td>পরিশোধিত বা প্রাপ্ত টাকার অংক।</td>
                                        <td>Required</td>
                                        <td>ইউজার টাইপ করবেন।</td>
                                        <td><code>12500.00</code></td>
                                        <td>ভুল অংকের এন্ট্রি সেভ হলে ফাইন্যান্সিয়াল ব্যালেন্স ভুল দেখাবে।</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION: Voucher Register -->
            <div id="section-register" class="doc-section">
                <div class="card card-custom">
                    <div class="card-custom-header d-flex justify-content-between align-items-center">
                        <h2 class="h4 mb-0 text-primary font-weight-bold">৯. Voucher Register (ভাউচার খাতা ও বাতিলকরণ)</h2>
                        <span class="badge badge-success badge-custom">Daily Operations</span>
                    </div>
                    <div class="card-body">
                        <h4 class="h6 font-weight-bold text-dark">এই মডিউলের কাজ:</h4>
                        <p class="text-muted">
                            পোস্ট করা সমস্ত ভাউচারের তালিকা এটি। এখানে সার্চ ফিল্টার ব্যবহার করে নির্দিষ্ট ভাউচার খোঁজা যায় এবং প্রয়োজনে ভুল করা ভাউচারটিকে বাতিল (Void) করা যায়।
                        </p>

                        <div class="alert alert-danger-light my-3">
                            <strong>ভয়েড করার নিয়ম (Void Controls):</strong> কোনো ভাউচার ডিলিট করা যায় না। আপনাকে অবশ্যই <code>Void</code> বাটনে ক্লিক করে বাতিল করার একটি যৌক্তিক কারণ (Reason) দিতে হবে। বাতিল ভাউচারের ডেবিট-ক্রেডিট ইফেক্ট ট্রায়াল ব্যালেন্স থেকে স্বয়ংক্রিয়ভাবে জিরো হয়ে যায়, কিন্তু ভাউচারের অস্তিত্ব ও নাম ডাটাবেজে থেকে যায়।
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION: Bank Reconciliation -->
            <div id="section-recon" class="doc-section">
                <div class="card card-custom">
                    <div class="card-custom-header d-flex justify-content-between align-items-center">
                        <h2 class="h4 mb-0 text-primary font-weight-bold">১০. Bank Reconciliation (ব্যাংক হিসাব মেলানো)</h2>
                        <span class="badge badge-success badge-custom">Daily Operations</span>
                    </div>
                    <div class="card-body">

                        <div class="alert-callout alert-callout-important mb-4">
                            <strong>গুরুত্বপূর্ণ:</strong> ব্যাংক রিকনসিলিয়েশন স্ক্রিনটি <strong>নতুন কোনো জার্নাল এন্ট্রি তৈরি করে না।</strong>
                            প্রথমে Cash &amp; Bank Voucher স্ক্রিন থেকে পোস্টিং দিন, তারপর এখানে ব্যাংক স্টেটমেন্টের সাথে মেলান।
                            COA-তে <code>bank</code> কীওয়ার্ড যুক্ত Asset অ্যাকাউন্ট না থাকলে এই মডিউল সক্রিয় হবে না।
                        </div>

                        <h5 class="font-weight-bold text-success mt-4 mb-3"><i class="fa fa-keyboard mr-2"></i>ডেটা এন্ট্রি ফিল্ড (Data Entry Steps)</h5>
                        <div class="table-responsive mb-4">
                            <table class="table table-sm table-bordered">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Field Name</th>
                                        <th>বাংলা নাম</th>
                                        <th>Required</th>
                                        <th>নির্দেশনা</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td>1</td><td>Bank Account</td><td>ব্যাংক অ্যাকাউন্ট</td><td><span class="badge badge-danger">Required</span></td><td>COA থেকে <em>bank</em> কীওয়ার্ড যুক্ত Asset লেজার সিলেক্ট করুন</td></tr>
                                    <tr><td>2</td><td>Statement Period (From)</td><td>স্টেটমেন্ট শুরুর তারিখ</td><td><span class="badge badge-danger">Required</span></td><td>ব্যাংক স্টেটমেন্টের প্রথম তারিখ</td></tr>
                                    <tr><td>3</td><td>Statement Period (To)</td><td>স্টেটমেন্ট শেষ তারিখ</td><td><span class="badge badge-danger">Required</span></td><td>ব্যাংক স্টেটমেন্টের শেষ তারিখ</td></tr>
                                    <tr><td>4</td><td>Bank Statement (Upload / Manual)</td><td>ব্যাংক স্টেটমেন্ট</td><td><span class="badge badge-danger">Required</span></td><td>CSV আপলোড বা ম্যানুয়াল এন্ট্রি — প্রতি লাইনে তারিখ, বিবরণ, পরিমাণ</td></tr>
                                    <tr><td>5</td><td>Reference / Cheque No.</td><td>রেফারেন্স নম্বর</td><td><span class="badge badge-warning">Recommended</span></td><td>চেক নম্বর বা ট্রান্সফার রেফ — ম্যাচিং নির্ভুলতা বাড়ায়</td></tr>
                                    <tr><td>6</td><td>Manual Override</td><td>ম্যানুয়াল ম্যাচ</td><td><span class="badge badge-secondary">Optional</span></td><td>অটো-ম্যাচ না হলে বাম ও ডান পাশ থেকে ম্যানুয়ালি টিক দিয়ে মেলানো যাবে</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <h5 class="font-weight-bold text-success mt-4 mb-3"><i class="fa fa-cogs mr-2"></i>অটো-ম্যাচিং লজিক (System Matching Rules)</h5>
                        <div class="table-responsive mb-4">
                            <table class="table table-sm table-bordered">
                                <thead style="background:#1e3a5f; color:#fff;">
                                    <tr>
                                        <th>Matching Criterion</th>
                                        <th>Tolerance / Rule</th>
                                        <th>Priority</th>
                                        <th>বিবরণ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td>Amount Match</td><td>± ০.০১ টাকা</td><td><span class="badge badge-danger">High</span></td><td>বই ও ব্যাংকের পরিমাণ অবশ্যই মিলতে হবে (রাউন্ডিং ০.০১ পর্যন্ত গ্রহণযোগ্য)</td></tr>
                                    <tr><td>Date Proximity</td><td>± ৩ দিন</td><td><span class="badge badge-warning">Medium</span></td><td>চেক ইস্যু ও ক্লিয়ারিং তারিখের ব্যবধান ৩ দিনের মধ্যে হলে ম্যাচ হবে</td></tr>
                                    <tr><td>Reference Code</td><td>Exact or Partial</td><td><span class="badge badge-success">Boost</span></td><td>চেক নম্বর / ট্রান্সফার রেফ মিললে ম্যাচিং স্কোর বৃদ্ধি পাবে</td></tr>
                                    <tr><td>Manual Override</td><td>User Confirmed</td><td><span class="badge badge-secondary">Fallback</span></td><td>অটো-ম্যাচ ব্যর্থ হলে ইউজার নিজে দুটি এন্ট্রি টিক দিয়ে ম্যাচ করবেন</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <h5 class="font-weight-bold text-success mt-4 mb-3"><i class="fa fa-calculator mr-2"></i>রিকনসিলিয়েশন সামারি ফর্মুলা</h5>
                        <div class="recon-formula mb-4">
                            <div><strong>Book Balance (লেজার ব্যালেন্স)</strong></div>
                            <div class="formula-line">+ Deposits in Transit (ট্রানজিটে জমা)</div>
                            <div class="formula-line">− Outstanding Cheques (অনাদায়ী চেক)</div>
                            <div class="formula-sep">══════════════════════════════════</div>
                            <div class="formula-result"><strong>= Adjusted Book Balance</strong></div>
                            <div class="mt-2"><strong>Bank Statement Balance</strong></div>
                            <div class="formula-line">± Bank Errors / Bank Charges</div>
                            <div class="formula-sep">══════════════════════════════════</div>
                            <div class="formula-result"><strong>= Adjusted Bank Balance</strong></div>
                            <div class="formula-sep mt-2">──────────────────────────────────</div>
                            <div class="formula-result text-success"><strong>Difference = 0 ✔ (Reconciled)</strong></div>
                        </div>

                        <h5 class="font-weight-bold text-danger mt-4 mb-3"><i class="fa fa-exclamation-triangle mr-2"></i>বাধ্যতামূলক COA রেকর্ড (Bank Tracker)</h5>
                        <div class="table-responsive">
                            <table class="coa-mandatory-table">
                                <thead>
                                    <tr>
                                        <th>Account Purpose</th>
                                        <th>Mandatory Keyword / Pattern</th>
                                        <th>System Account Type</th>
                                        <th>Production Example Name</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>ব্যাংক লেজার (Bank Ledger)</td>
                                        <td><span class="keyword-cell">bank</span></td>
                                        <td>Asset</td>
                                        <td>Dutch Bangla Bank – Current A/C</td>
                                    </tr>
                                    <tr>
                                        <td>নগদ হাতে (Cash In Hand)</td>
                                        <td><span class="keyword-cell">cash</span></td>
                                        <td>Asset</td>
                                        <td>Cash In Hand</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h4 class="h6 font-weight-bold text-dark mt-4">রিকনসিলিয়েশনের ধাপসমূহ (Step-by-Step SOP):</h4>
                        <div class="flow-container py-4">
                            <div class="flow-node">ধাপ ১: ব্যাংক লেনদেনের ভাউচার পোস্ট করুন (PV/RV)</div>
                            <div class="flow-arrow"><i class="fa fa-arrow-down"></i></div>
                            <div class="flow-node">ধাপ ২: ব্যাংক স্টেটমেন্ট আপলোড বা এন্ট্রি দিন (Upload)</div>
                            <div class="flow-arrow"><i class="fa fa-arrow-down"></i></div>
                            <div class="flow-node">ধাপ ৩: বাম পাশ থেকে ইন্টারনাল বুক এন্ট্রি সিলেক্ট করুন (টিক দিন)</div>
                            <div class="flow-arrow"><i class="fa fa-arrow-down"></i></div>
                            <div class="flow-node">ধাপ ৪: ডান পাশ থেকে ব্যাংক স্টেটমেন্ট এন্ট্রি সিলেক্ট করুন (টিক দিন)</div>
                            <div class="flow-arrow"><i class="fa fa-arrow-down"></i></div>
                            <div class="flow-node text-white" style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); border-color: #0f172a;">ধাপ ৫: সিস্টেম স্বয়ংক্রিয়ভাবে রিকনসিল বা ম্যাচ সম্পন্ন করবে</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION: Payroll Management -->
            <div id="section-payroll" class="doc-section">
                <div class="card card-custom">
                    <div class="card-custom-header d-flex justify-content-between align-items-center">
                        <h2 class="h4 mb-0 text-primary font-weight-bold">১১. Payroll Management — Manual Integration (পেরোল ম্যানুয়াল পোস্টিং)</h2>
                        <span class="badge badge-warning badge-custom">Payroll Module</span>
                    </div>
                    <div class="card-body">

                        <div class="alert-callout alert-callout-important mb-4">
                            <strong>গুরুত্বপূর্ণ:</strong> পেরোল মডিউল থেকে Accounts-এ পোস্টিং <strong>ম্যানুয়ালি</strong> করতে হয়।
                            সিস্টেম স্বয়ংক্রিয়ভাবে পোস্ট করে না — প্রতি মাসে Accounts টিমকে নিজে থেকে পোস্টিং দিতে হবে।
                        </div>

                        <h5 class="font-weight-bold text-warning mt-3 mb-3"><i class="fa fa-clipboard-check mr-2"></i>Pre-Conditions — পোস্টিং দেওয়ার আগে যা নিশ্চিত করতে হবে</h5>
                        <div class="table-responsive mb-4">
                            <table class="table table-sm table-bordered">
                                <thead class="thead-dark"><tr><th>#</th><th>শর্ত</th><th>কোথায় চেক করবেন</th><th>না হলে কী হবে</th></tr></thead>
                                <tbody>
                                    <tr><td>1</td><td>Salary Sheet <strong>Approved</strong> অবস্থায় আছে</td><td>Payroll → Monthly Salary Sheet → Status</td><td>পোস্টিং ফর্মে ডেটা লোড হবে না</td></tr>
                                    <tr><td>2</td><td>COA-তে ৪টি mandatory লেজার Active আছে</td><td>Accounts → Chart of Accounts</td><td><code>Required payroll ledger accounts were not found</code> error</td></tr>
                                    <tr><td>3</td><td>Financial Period সেই মাসের জন্য Open আছে</td><td>Accounts → Tax Rates &amp; Financial Periods</td><td><code>Voucher submission blocked (period closed)</code> error</td></tr>
                                    <tr><td>4</td><td>Cost Center (Department) তৈরি আছে</td><td>Accounts → Cost Centers</td><td>Department সিলেক্ট করা যাবে না</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <h5 class="font-weight-bold text-warning mt-4 mb-3"><i class="fa fa-list-ol mr-2"></i>ধাপে ধাপে পোস্টিং প্রক্রিয়া (Full SOP)</h5>
                        <div class="flow-container py-3 mb-4">
                            <div class="flow-node"><strong>ধাপ ১:</strong> Payroll → Payroll Posting ফর্মে যান</div>
                            <div class="flow-arrow"><i class="fa fa-arrow-down"></i></div>
                            <div class="flow-node"><strong>ধাপ ২:</strong> Voucher Date = মাসের শেষ তারিখ দিন (যেমন: <code>2024-06-30</code>)</div>
                            <div class="flow-arrow"><i class="fa fa-arrow-down"></i></div>
                            <div class="flow-node"><strong>ধাপ ৩:</strong> Salary Month সিলেক্ট করুন → সিস্টেম Salary Sheet থেকে ডেটা লোড করবে</div>
                            <div class="flow-arrow"><i class="fa fa-arrow-down"></i></div>
                            <div class="flow-node"><strong>ধাপ ৪:</strong> Department / Cost Center সিলেক্ট করুন (Factory / Admin / Commercial আলাদাভাবে)</div>
                            <div class="flow-arrow"><i class="fa fa-arrow-down"></i></div>
                            <div class="flow-node"><strong>ধাপ ৫:</strong> COA থেকে ৪টি লেজার ম্যাপ করুন (Salary Expense, PF Liability, Advance, Salary Payable)</div>
                            <div class="flow-arrow"><i class="fa fa-arrow-down"></i></div>
                            <div class="flow-node"><strong>ধাপ ৬:</strong> System-generated Journal Preview চেক করুন — Dr = Cr হওয়া বাধ্যতামূলক</div>
                            <div class="flow-arrow"><i class="fa fa-arrow-down"></i></div>
                            <div class="flow-node text-white" style="background:linear-gradient(135deg,#0d9488,#0f766e)"><strong>ধাপ ৭:</strong> Submit করুন → Voucher Register-এ Entry দেখা যাবে</div>
                        </div>

                        <h5 class="font-weight-bold text-warning mt-4 mb-3"><i class="fa fa-book-open mr-2"></i>জার্নাল এন্ট্রি প্রিভিউ</h5>
                        <div class="journal-preview mb-4">
                            <span class="arr">★ Payroll Journal — June 2024 (Factory Dept)  [Journal Voucher]</span><br>
                            <span class="dr">  Dr.  Salary Expense – Factory          120,000.00  [Cost Center: Factory Dept]</span><br>
                            <span class="cr">    Cr.  PF Liability – Staff                 4,800.00</span><br>
                            <span class="cr">    Cr.  Advance Salary – Workers             8,500.00</span><br>
                            <span class="cr">    Cr.  Salary Payable – Current Month     106,700.00</span><br>
                            <span class="arr">  ─────────────────────────────────────────────────────</span><br>
                            <span class="arr">  Total Dr = Total Cr = 120,000.00  ✔ Balanced</span>
                        </div>

                        <h5 class="font-weight-bold text-info mt-4 mb-3"><i class="fa fa-tags mr-2"></i>Cost Center ও Party — পেরোলে কোথায় লাগে?</h5>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="border-left border-warning pl-3 py-2">
                                    <strong class="text-warning d-block mb-1"><i class="fa fa-tags mr-1"></i>Cost Center</strong>
                                    <p class="small text-muted mb-0"><strong>লাগবে:</strong> Salary Expense লেজারের বিপরীতে (Account Type = Expense)।<br>
                                    Factory, Admin, Commercial — প্রতিটি ডিপার্টমেন্টের জন্য আলাদা পোস্টিং দিলে আলাদা Cost Center সিলেক্ট করুন।<br>
                                    <strong>রিপোর্ট প্রভাব:</strong> Department-wise Salary Report এ প্রতিটি বিভাগের বেতন আলাদাভাবে দেখা যাবে।</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border-left border-secondary pl-3 py-2">
                                    <strong class="text-secondary d-block mb-1"><i class="fa fa-users mr-1"></i>Party</strong>
                                    <p class="small text-muted mb-0"><strong>লাগবে না:</strong> পেরোল পোস্টিংয়ে Party (Buyer/Supplier) সিলেক্টের প্রয়োজন নেই।<br>
                                    Salary Payable একটি সাধারণ Liability লেজার — এখানে individual employee tracking হয় না।<br>
                                    <strong>ব্যক্তিগত বকেয়া ট্র্যাক করতে হলে:</strong> Employee-wise Advance অ্যাকাউন্ট তৈরি করুন।</p>
                                </div>
                            </div>
                        </div>

                        <h5 class="font-weight-bold text-danger mt-4 mb-3"><i class="fa fa-exclamation-triangle mr-2"></i>সম্ভাব্য Errors ও সমাধান</h5>
                        <div class="table-responsive mb-4">
                            <table class="table table-sm table-bordered">
                                <thead style="background:#7f1d1d; color:#fff;"><tr><th>Error Message</th><th>কারণ</th><th>সমাধান</th></tr></thead>
                                <tbody>
                                    <tr><td><code>Required payroll ledger accounts were not found</code></td><td>COA-তে keyword-matched লেজার নেই বা inactive</td><td>COA-তে নিচের ৪টি লেজার তৈরি/active করুন</td></tr>
                                    <tr><td><code>Salary sheet not approved</code></td><td>Payroll মডিউলে শিট Approved করা হয়নি</td><td>Payroll → Monthly Salary Sheet → Approve করুন</td></tr>
                                    <tr><td><code>Period is closed</code></td><td>পোস্টিং তারিখের মাস Closed</td><td>Tax Rates &amp; Financial Periods → মাসটি Open করুন</td></tr>
                                    <tr><td>Dr ≠ Cr (Imbalanced)</td><td>PF বা Advance লেজার ভুল ধরনের (Asset/Liability মিশ্রণ)</td><td>COA-তে লেজার টাইপ পুনরায় চেক করুন</td></tr>
                                    <tr><td>Cost Center dropdown নিষ্ক্রিয়</td><td>Salary Expense-এর Account Type Expense নয়</td><td>COA-তে ঐ লেজারের type = Expense নিশ্চিত করুন</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <h5 class="font-weight-bold text-danger mt-4 mb-3"><i class="fa fa-exclamation-triangle mr-2"></i>বাধ্যতামূলক COA রেকর্ড (৪টি লেজার অবশ্যই থাকতে হবে)</h5>
                        <div class="table-responsive">
                            <table class="coa-mandatory-table">
                                <thead>
                                    <tr><th>Account Purpose</th><th>Mandatory Keyword</th><th>Account Type</th><th>Example Name</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td>বেতন ব্যয়</td><td><span class="keyword-cell">salary expense</span></td><td>Expense</td><td>Salary Expense – Factory</td></tr>
                                    <tr><td>প্রভিডেন্ট ফান্ড দায়</td><td><span class="keyword-cell">provident fund</span> বা <span class="keyword-cell">pf liability</span></td><td>Liability</td><td>PF Liability – Staff</td></tr>
                                    <tr><td>অগ্রিম বেতন</td><td><span class="keyword-cell">advance salary</span></td><td>Asset</td><td>Advance Salary – Workers</td></tr>
                                    <tr><td>প্রদেয় বেতন</td><td><span class="keyword-cell">salary payable</span></td><td>Liability</td><td>Salary Payable – Current Month</td></tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>

            <!-- SECTION: Inventory Financial Journal -->
            <div id="section-inventory" class="doc-section">
                <div class="card card-custom">
                    <div class="card-custom-header d-flex justify-content-between align-items-center">
                        <h2 class="h4 mb-0 text-primary font-weight-bold">১২. Inventory Financial Journal — Manual Integration (ইনভেন্টরি আর্থিক জার্নাল)</h2>
                        <span class="badge badge-info badge-custom">Inventory Module</span>
                    </div>
                    <div class="card-body">

                        <div class="alert-callout alert-callout-note mb-4">
                            <strong>নোট:</strong> ইনভেন্টরি জার্নাল পোস্টিং <strong>৩টি Transaction Type</strong>-এ বিভক্ত — প্রতিটির জন্য আলাদা COA লেজার ও আলাদা SOP।
                            Transaction Type সিলেক্ট করলে ফর্মের ফিল্ড স্বয়ংক্রিয়ভাবে পরিবর্তন হয়।
                        </div>

                        <h5 class="font-weight-bold text-info mt-3 mb-3"><i class="fa fa-clipboard-check mr-2"></i>Pre-Conditions — পোস্টিং দেওয়ার আগে</h5>
                        <div class="table-responsive mb-4">
                            <table class="table table-sm table-bordered">
                                <thead class="thead-dark"><tr><th>#</th><th>শর্ত</th><th>কোথায় চেক করবেন</th><th>না হলে কী হবে</th></tr></thead>
                                <tbody>
                                    <tr><td>1</td><td>Inventory Item তৈরি আছে</td><td>Inventory → Item List</td><td>Item dropdown-এ দেখা যাবে না</td></tr>
                                    <tr><td>2</td><td>COA-তে mandatory লেজার Active</td><td>Accounts → Chart of Accounts</td><td><code>Required inventory ledger accounts were not found</code></td></tr>
                                    <tr><td>3</td><td>Supplier Master তৈরি আছে (Purchase এর জন্য)</td><td>Suppliers → Supplier List</td><td>Party সিলেক্ট করা যাবে না</td></tr>
                                    <tr><td>4</td><td>Financial Period Open আছে</td><td>Accounts → Tax Rates &amp; Financial Periods</td><td>Period closed error</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <h5 class="font-weight-bold text-info mt-4 mb-3"><i class="fa fa-th-large mr-2"></i>Transaction Type — ৩টি ভিন্ন প্রক্রিয়া</h5>
                        <div class="row mb-4">
                            <div class="col-md-4 mb-3">
                                <div class="trans-type-card">
                                    <h6 class="font-weight-bold text-success mb-1"><i class="fa fa-shopping-cart mr-1"></i> Material Purchase</h6>
                                    <p class="small text-muted mb-1">কাঁচামাল ক্রয় — ইনভেন্টরি ডেবিট, সাপ্লায়ার ক্রেডিট</p>
                                    <p class="small mb-0"><strong>Party:</strong> <span class="badge badge-danger">Required</span> (Supplier)</p>
                                    <p class="small mb-0"><strong>Cost Center:</strong> Optional</p>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="trans-type-card">
                                    <h6 class="font-weight-bold text-warning mb-1"><i class="fa fa-industry mr-1"></i> Issue to Production</h6>
                                    <p class="small text-muted mb-1">উৎপাদনে পাঠানো — WIP ডেবিট, ইনভেন্টরি ক্রেডিট</p>
                                    <p class="small mb-0"><strong>Party:</strong> Not Required</p>
                                    <p class="small mb-0"><strong>Cost Center:</strong> <span class="badge badge-danger">Required</span> (Style/Order)</p>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="trans-type-card">
                                    <h6 class="font-weight-bold text-danger mb-1"><i class="fa fa-balance-scale mr-1"></i> Inventory Adjustment</h6>
                                    <p class="small text-muted mb-1">স্টক এডজাস্টমেন্ট — Loss/Gain রেকর্ড</p>
                                    <p class="small mb-0"><strong>Party:</strong> Not Required</p>
                                    <p class="small mb-0"><strong>Cost Center:</strong> Optional</p>
                                </div>
                            </div>
                        </div>

                        <h5 class="font-weight-bold text-info mt-4 mb-3"><i class="fa fa-list-ol mr-2"></i>SOP — Material Purchase (কাঁচামাল ক্রয়)</h5>
                        <div class="flow-container py-2 mb-3">
                            <div class="flow-node"><strong>ধাপ ১:</strong> Inventory → Financial Journal → Transaction Type = <strong>Material Purchase</strong> সিলেক্ট</div>
                            <div class="flow-arrow"><i class="fa fa-arrow-down"></i></div>
                            <div class="flow-node"><strong>ধাপ ২:</strong> Voucher Date = ক্রয়ের প্রকৃত তারিখ দিন</div>
                            <div class="flow-arrow"><i class="fa fa-arrow-down"></i></div>
                            <div class="flow-node"><strong>ধাপ ৩:</strong> Item সিলেক্ট → Quantity ও Unit Cost দিন</div>
                            <div class="flow-arrow"><i class="fa fa-arrow-down"></i></div>
                            <div class="flow-node"><strong>ধাপ ৪:</strong> Party Type = Supplier → Supplier সিলেক্ট করুন (বাধ্যতামূলক)</div>
                            <div class="flow-arrow"><i class="fa fa-arrow-down"></i></div>
                            <div class="flow-node"><strong>ধাপ ৫:</strong> Inventory Account (Dr) ও Supplier Payable Account (Cr) COA থেকে সিলেক্ট</div>
                            <div class="flow-arrow"><i class="fa fa-arrow-down"></i></div>
                            <div class="flow-node text-white" style="background:linear-gradient(135deg,#0d9488,#0f766e)"><strong>ধাপ ৬:</strong> Submit → Voucher Register + Supplier Outstanding-এ এন্ট্রি দেখা যাবে</div>
                        </div>

                        <h5 class="font-weight-bold text-info mt-4 mb-3"><i class="fa fa-book-open mr-2"></i>জার্নাল প্রিভিউ — ৩টি ট্রান্জ্যাকশন টাইপ</h5>
                        <div class="row mb-4">
                            <div class="col-md-4 mb-3">
                                <div class="journal-preview" style="font-size:0.75rem;">
                                    <span class="arr">★ Material Purchase</span><br>
                                    <span class="dr">  Dr.  Raw Material Inventory   50,000</span><br>
                                    <span class="cr">    Cr.  Supplier Payable – Local 50,000</span><br>
                                    <span class="arr">  Party: Supplier ABC  ✔</span>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="journal-preview" style="font-size:0.75rem;">
                                    <span class="arr">★ Issue to Production</span><br>
                                    <span class="dr">  Dr.  WIP – Cutting Section     30,000</span><br>
                                    <span class="cr">    Cr.  Raw Material Inventory   30,000</span><br>
                                    <span class="arr">  Cost Center: Style H&amp;M-001  ✔</span>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="journal-preview" style="font-size:0.75rem;">
                                    <span class="arr">★ Inventory Adjustment (Loss)</span><br>
                                    <span class="dr">  Dr.  Inventory Adjustment Loss  2,000</span><br>
                                    <span class="cr">    Cr.  Raw Material Inventory    2,000</span><br>
                                    <span class="arr">  No Party required  ✔</span>
                                </div>
                            </div>
                        </div>

                        <h5 class="font-weight-bold text-info mt-4 mb-3"><i class="fa fa-users mr-2"></i>Party কোথা থেকে আসে ও কেন লাগে?</h5>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="border-left border-info pl-3 py-2">
                                    <strong class="d-block mb-1 text-dark">Party-র উৎস:</strong>
                                    <p class="small text-muted mb-0">Party (Supplier) ডেটা আসে <strong>Supplier Master</strong> থেকে।
                                    Inventory Purchase-এ যে Supplier-এর কাছ থেকে কাঁচামাল কেনা হয়েছে তাকে সিলেক্ট করতে হয়।<br>
                                    <strong>পথ:</strong> Suppliers → Supplier List → Supplier তৈরি করুন, তারপর Inventory Journal-এ সেই Supplier দেখা যাবে।</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border-left border-success pl-3 py-2">
                                    <strong class="d-block mb-1 text-dark">Party কেন ব্যবহার হচ্ছে?</strong>
                                    <p class="small text-muted mb-0">Party সিলেক্ট করলে সিস্টেম ঐ Supplier-এর <strong>Supplier Outstanding / Aging Report</strong> তৈরি করতে পারে।
                                    অর্থাৎ ঐ Supplier-এর কাছে কত টাকা বাকি আছে, কত দিন ধরে বাকি — এই তথ্য Party-র মাধ্যমে রিপোর্টে আসে।</p>
                                </div>
                            </div>
                        </div>

                        <h5 class="font-weight-bold text-danger mt-4 mb-3"><i class="fa fa-exclamation-triangle mr-2"></i>সম্ভাব্য Errors ও সমাধান</h5>
                        <div class="table-responsive mb-4">
                            <table class="table table-sm table-bordered">
                                <thead style="background:#7f1d1d; color:#fff;"><tr><th>Error</th><th>কারণ</th><th>সমাধান</th></tr></thead>
                                <tbody>
                                    <tr><td><code>Required inventory ledger accounts were not found</code></td><td>COA-তে keyword-matched লেজার নেই</td><td>COA-তে নিচের ৪টি লেজার তৈরি করুন</td></tr>
                                    <tr><td>Supplier Payable লেজার সিলেক্ট করা যাচ্ছে না</td><td>COA-তে account_name-এ <code>supplier payable</code> নেই</td><td>COA-তে লেজার নামে <code>supplier payable</code> keyword রাখুন</td></tr>
                                    <tr><td>WIP লেজার দেখা যাচ্ছে না</td><td>COA-তে <code>wip</code> বা <code>work in progress</code> keyword নেই</td><td>COA-তে WIP লেজার তৈরি করুন</td></tr>
                                    <tr><td>Cost Center dropdown নিষ্ক্রিয় (Issue to Production)</td><td>WIP Account-এর type Asset — Cost Center Asset-এ unlock হয় না</td><td>WIP Cost Center input এ ইউজার-সাইডে manually enter করুন</td></tr>
                                    <tr><td>Dr ≠ Cr</td><td>Quantity × Unit Cost হিসাব ভুল</td><td>পরিমাণ ও একক মূল্য পুনরায় চেক করুন</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <h5 class="font-weight-bold text-danger mt-4 mb-3"><i class="fa fa-exclamation-triangle mr-2"></i>বাধ্যতামূলক COA রেকর্ড</h5>
                        <div class="table-responsive">
                            <table class="coa-mandatory-table">
                                <thead><tr><th>Account Purpose</th><th>Mandatory Keyword</th><th>Account Type</th><th>Example Name</th></tr></thead>
                                <tbody>
                                    <tr><td>কাঁচামাল ইনভেন্টরি</td><td><span class="keyword-cell">raw material</span> বা <span class="keyword-cell">inventory</span></td><td>Asset</td><td>Raw Material Inventory – Fabric</td></tr>
                                    <tr><td>সাপ্লায়ার দায়</td><td><span class="keyword-cell">supplier payable</span></td><td>Liability</td><td>Supplier Payable – Local</td></tr>
                                    <tr><td>প্রক্রিয়াধীন উৎপাদন</td><td><span class="keyword-cell">work in progress</span> বা <span class="keyword-cell">wip</span></td><td>Asset</td><td>WIP – Cutting Section</td></tr>
                                    <tr><td>ইনভেন্টরি এডজাস্টমেন্ট লস</td><td><span class="keyword-cell">inventory adjustment</span></td><td>Expense</td><td>Inventory Adjustment Loss</td></tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>

            <!-- SECTION: Commercial LC Tracker -->
            <div id="section-lc" class="doc-section">
                <div class="card card-custom">
                    <div class="card-custom-header d-flex justify-content-between align-items-center">
                        <h2 class="h4 mb-0 text-primary font-weight-bold">১৩. Commercial Bank / LC Tracker — Manual Integration (কমার্শিয়াল ব্যাংক ট্র্যাকার)</h2>
                        <span class="badge badge-success badge-custom">LC Module</span>
                    </div>
                    <div class="card-body">

                        <div class="alert-callout alert-callout-tip mb-4">
                            <strong>টিপস:</strong> LC Tracker মূলত আমদানি LC-র ব্যাংক চার্জ, মার্জিন ব্যালেন্স ও ক্লিয়ারিং খরচ Accounts-এ ম্যানুয়ালি পোস্ট করার সুবিধা দেয়।
                            LC নম্বর সিলেক্ট করলে Commercial মডিউল থেকে ঐ LC-র ডেটা স্বয়ংক্রিয়ভাবে লোড হয়।
                        </div>

                        <h5 class="font-weight-bold mt-3 mb-3"><i class="fa fa-clipboard-check mr-2"></i>Pre-Conditions</h5>
                        <div class="table-responsive mb-4">
                            <table class="table table-sm table-bordered">
                                <thead class="thead-dark"><tr><th>#</th><th>শর্ত</th><th>কোথায় চেক করবেন</th><th>না হলে কী হবে</th></tr></thead>
                                <tbody>
                                    <tr><td>1</td><td>LC Commercial মডিউলে Open করা আছে</td><td>Commercial → LC Register</td><td>LC নম্বর dropdown-এ দেখা যাবে না</td></tr>
                                    <tr><td>2</td><td>COA-তে Bank Account (<code>bank</code> keyword) আছে</td><td>Accounts → Chart of Accounts</td><td>ব্যাংক অ্যাকাউন্ট সিলেক্ট করা যাবে না</td></tr>
                                    <tr><td>3</td><td>COA-তে LC charge type লেজার আছে</td><td>Accounts → Chart of Accounts</td><td>LC Commission / Customs Duty সিলেক্ট যাবে না</td></tr>
                                    <tr><td>4</td><td>Financial Period Open আছে</td><td>Accounts → Tax Rates &amp; Financial Periods</td><td>Period closed error</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <h5 class="font-weight-bold mt-4 mb-3"><i class="fa fa-list-ol mr-2"></i>ধাপে ধাপে পোস্টিং SOP</h5>
                        <div class="flow-container py-2 mb-4">
                            <div class="flow-node"><strong>ধাপ ১:</strong> Accounts → LC Tracker → New Entry</div>
                            <div class="flow-arrow"><i class="fa fa-arrow-down"></i></div>
                            <div class="flow-node"><strong>ধাপ ২:</strong> LC Number সিলেক্ট করুন → Commercial মডিউল থেকে LC বিবরণ লোড হবে</div>
                            <div class="flow-arrow"><i class="fa fa-arrow-down"></i></div>
                            <div class="flow-node"><strong>ধাপ ৩:</strong> Charge Type সিলেক্ট করুন (Bank Margin / LC Commission / Customs Duty / Clearing Charge)</div>
                            <div class="flow-arrow"><i class="fa fa-arrow-down"></i></div>
                            <div class="flow-node"><strong>ধাপ ৪:</strong> Amount দিন → COA থেকে Expense/Asset লেজার ম্যাপ করুন</div>
                            <div class="flow-arrow"><i class="fa fa-arrow-down"></i></div>
                            <div class="flow-node"><strong>ধাপ ৫:</strong> Bank Account (Cr) সিলেক্ট করুন</div>
                            <div class="flow-arrow"><i class="fa fa-arrow-down"></i></div>
                            <div class="flow-node text-white" style="background:linear-gradient(135deg,#0d9488,#0f766e)"><strong>ধাপ ৬:</strong> Submit → Voucher Register-এ LC চার্জের এন্ট্রি দেখা যাবে</div>
                        </div>

                        <h5 class="font-weight-bold mt-4 mb-3"><i class="fa fa-book-open mr-2"></i>জার্নাল প্রিভিউ — LC Commission পোস্টিং</h5>
                        <div class="journal-preview mb-4">
                            <span class="arr">★ LC Commission — LC No. IMP-2024-001 (Payment Voucher)</span><br>
                            <span class="dr">  Dr.  LC Commission Expense          5,200.00  [Cost Center: Import Dept]</span><br>
                            <span class="cr">    Cr.  Dutch Bangla Bank – Current A/C   5,200.00</span><br>
                            <span class="arr">  ─────────────────────────────────────────────────</span><br>
                            <span class="arr">  No Party required (ব্যাংক থেকে সরাসরি কাটা হয়েছে)  ✔</span>
                        </div>

                        <h5 class="font-weight-bold text-info mt-4 mb-3"><i class="fa fa-tags mr-2"></i>Cost Center ও Party — LC Tracker-এ কোথায় লাগে?</h5>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="border-left border-warning pl-3 py-2">
                                    <strong class="text-warning d-block mb-1"><i class="fa fa-tags mr-1"></i>Cost Center</strong>
                                    <p class="small text-muted mb-0"><strong>লাগবে:</strong> LC Commission / Customs Duty / Clearing Charge — এগুলো Expense লেজার, তাই Cost Center আনলক হবে।<br>
                                    Import Department বা নির্দিষ্ট LC-র Cost Center ব্যবহার করুন।<br>
                                    <strong>রিপোর্ট প্রভাব:</strong> LC-wise আমদানি খরচের breakdown দেখা যাবে।</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border-left border-secondary pl-3 py-2">
                                    <strong class="text-secondary d-block mb-1"><i class="fa fa-users mr-1"></i>Party</strong>
                                    <p class="small text-muted mb-0"><strong>সাধারণত লাগে না:</strong> LC চার্জ সরাসরি ব্যাংক থেকে কাটা হয়, তাই Supplier Party ব্যবহার হয় না।<br>
                                    তবে যদি আলাদা C&amp;F Agent বা Clearing Agent-কে পেমেন্ট করা হয় এবং তার কাছে বাকি থাকে, তখন সেই Supplier-কে Party হিসেবে সিলেক্ট করুন।</p>
                                </div>
                            </div>
                        </div>

                        <h5 class="font-weight-bold text-danger mt-4 mb-3"><i class="fa fa-exclamation-triangle mr-2"></i>সম্ভাব্য Errors ও সমাধান</h5>
                        <div class="table-responsive mb-4">
                            <table class="table table-sm table-bordered">
                                <thead style="background:#7f1d1d; color:#fff;"><tr><th>Error</th><th>কারণ</th><th>সমাধান</th></tr></thead>
                                <tbody>
                                    <tr><td>LC Number dropdown ফাঁকা</td><td>Commercial মডিউলে LC তৈরি হয়নি</td><td>Commercial → LC Register → LC তৈরি করুন</td></tr>
                                    <tr><td>Bank Account সিলেক্ট যাচ্ছে না</td><td>COA-তে <code>bank</code> keyword-এর Asset লেজার নেই</td><td>COA-তে Bank লেজার তৈরি করুন</td></tr>
                                    <tr><td>LC Module নিষ্ক্রিয়</td><td>COA-তে <code>bank</code> keyword-এর কোনো লেজার নেই বা inactive</td><td>COA-তে Bank লেজার active করুন</td></tr>
                                    <tr><td>Cost Center dropdown নিষ্ক্রিয়</td><td>সিলেক্ট করা লেজার Expense টাইপ নয়</td><td>COA-তে LC Commission লেজারের type = Expense নিশ্চিত করুন</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <h5 class="font-weight-bold mt-3 mb-3"><i class="fa fa-ship mr-2"></i>বাধ্যতামূলক COA রেকর্ড — LC Charge Types</h5>
                        <div class="table-responsive">
                            <table class="coa-mandatory-table">
                                <thead><tr><th>LC Charge Type</th><th>Mandatory Keyword</th><th>Account Type</th><th>Example Name</th></tr></thead>
                                <tbody>
                                    <tr><td>ব্যাংক মার্জিন</td><td><span class="keyword-cell">bank margin</span> বা <span class="keyword-cell">lc margin</span></td><td>Asset</td><td>LC Margin – Dutch Bangla Bank</td></tr>
                                    <tr><td>ব্যাংক কমিশন</td><td><span class="keyword-cell">lc commission</span> বা <span class="keyword-cell">bank commission</span></td><td>Expense</td><td>LC Commission Expense</td></tr>
                                    <tr><td>কাস্টমস ডিউটি</td><td><span class="keyword-cell">customs duty</span> বা <span class="keyword-cell">import duty</span></td><td>Expense</td><td>Customs Duty – Import</td></tr>
                                    <tr><td>ক্লিয়ারিং চার্জ</td><td><span class="keyword-cell">clearing charge</span> বা <span class="keyword-cell">clearing expense</span></td><td>Expense</td><td>Port Clearing Charges</td></tr>
                                    <tr><td>ব্যাংক অ্যাকাউন্ট (Cr)</td><td><span class="keyword-cell">bank</span></td><td>Asset</td><td>Dutch Bangla Bank – Current A/C</td></tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>

            <!-- SECTION: Interactive Decision Tree Widget -->
            <div id="section-decision" class="doc-section">
                <div class="card card-custom">
                    <div class="card-custom-header d-flex justify-content-between align-items-center">
                        <h2 class="h4 mb-0 text-primary font-weight-bold">১২. ইন্টারেক্টিভ লেজার সিলেকশন গাইড (Decision Tree)</h2>
                        <span class="badge badge-success badge-custom">Interactive Tool</span>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-4">
                            কোন ট্রানজ্যাকশনের জন্য কোন লেজার অ্যাকাউন্ট সিলেক্ট করবেন এবং কোন ভাউচার ব্যবহার করবেন তা জানতে নিচের ট্রানজ্যাকশন অপশনটি সিলেক্ট করুন:
                        </p>
                        
                        <div class="decision-card">
                            <div class="form-group">
                                <label class="font-weight-bold text-dark" for="transactionSelector"><i class="fa fa-question-circle text-info mr-1"></i> আপনার লেনদেনের বিবরণটি সিলেক্ট করুন:</label>
                                <select id="transactionSelector" class="form-control form-control-lg border-teal">
                                    <option value="">-- লেনদেন সিলেক্ট করুন --</option>
                                    <option value="rent">১. অফিসের ঘর ভাড়া ক্যাশে প্রদান করা হলো</option>
                                    <option value="customer_collect">২. কাস্টমারের কাছ থেকে ব্যাংকে বকেয়া টাকা পাওয়া গেল</option>
                                    <option value="supplier_pay">৩. ব্যাংকের মাধ্যমে সাপ্লায়ারের বকেয়া বিল পরিশোধ</option>
                                    <option value="purchase_raw">৪. সাপ্লায়ারের কাছ থেকে বাকিতে সুতা বা কাঁচামাল ক্রয়</option>
                                    <option value="depreciation">৫. কারখানার মেশিনারিজের উপর অবচয় ধার্যকরণ</option>
                                    <option value="salary_accrual">৬. মাস শেষে অফিসের কর্মকর্তা ও কর্মচারীদের বেতন বকেয়া ধার্যকরণ</option>
                                    <option value="bank_transfer">৭. ব্যবসার চলতি হিসাবের ক্যাশ থেকে ১০,০০০ টাকা ব্যাংক অ্যাকাউন্টে জমা</option>
                                    <option value="sales_credit">৮. বায়ারের কাছে ফিনিশড গার্মেন্টস বাকিতে বিক্রয়</option>
                                </select>
                            </div>

                            <!-- Dynamic Decision Output Grid -->
                            <div id="decisionOutput" class="mt-4 d-none">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="p-3 border rounded bg-white h-100">
                                            <h5 class="h6 font-weight-bold text-teal mb-3"><i class="fa fa-cogs"></i> ভাউচার ও পোস্টিং গাইড</h5>
                                            <ul class="list-unstyled mb-0 small text-muted">
                                                <li class="mb-2"><strong>ভাউচার টাইপ:</strong> <span class="badge badge-info px-2" id="outVoucher"></span></li>
                                                <li class="mb-2"><strong>ডেবিট লেজার (Dr):</strong> <span class="font-weight-bold text-dark" id="outDebit"></span></li>
                                                <li class="mb-2"><strong>ক্রেডিট লেজার (Cr):</strong> <span class="font-weight-bold text-dark" id="outCredit"></span></li>
                                                <li class="mb-2"><strong>পার্টি আবশ্যক (Party Required)?</strong> <span class="font-weight-bold" id="outParty"></span></li>
                                                <li class="mb-2"><strong>কস্ট সেন্টার আবশ্যক (Cost Center)?</strong> <span class="font-weight-bold" id="outCostCenter"></span></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="p-3 border rounded bg-white h-100">
                                            <h5 class="h6 font-weight-bold text-teal mb-3"><i class="fa fa-calculator"></i> অ্যাকাউন্টিং লজিক ও প্রভাব</h5>
                                            <p class="small text-muted mb-2"><strong>সহজ ভাষায় লজিক:</strong> <span id="outLogic"></span></p>
                                            <p class="small text-muted mb-2"><strong>ব্যবসায়িক প্রভাব (Business Impact):</strong> <span id="outImpact"></span></p>
                                            <p class="small text-danger mb-0"><strong>সাধারণ ভুল (Mistakes):</strong> <span id="outMistake"></span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION: Logic Guides -->
            <div id="section-logic-guides" class="doc-section">
                <div class="card card-custom">
                    <div class="card-custom-header d-flex justify-content-between align-items-center">
                        <h2 class="h4 mb-0 text-primary font-weight-bold">১৩. ভাউচার, কস্ট সেন্টার ও পার্টি গাইড</h2>
                        <span class="badge badge-success badge-custom">Guides</span>
                    </div>
                    <div class="card-body">
                        <!-- Voucher Guide Grid -->
                        <h3 class="h5 mb-3 text-dark font-weight-bold border-bottom pb-2">ভাউচার টাইপ নির্দেশিকা (Voucher Guide)</h3>
                        <div class="row">
                            <div class="col-lg-6 mb-3">
                                <div class="border rounded p-3 bg-light">
                                    <h5 class="h6 font-weight-bold text-primary">Payment Voucher (পেমেন্ট ভাউচার)</h5>
                                    <p class="small text-muted mb-1"><strong>ব্যবহার:</strong> ক্যাশ বা ব্যাংক থেকে যেকোনো পেমেন্ট (যেমন: ভাড়া পরিশোধ, বেতন প্রদান, সাপ্লায়ার বিল পরিশোধ)।</p>
                                    <p class="small text-muted mb-1"><strong>ডাটা আবশ্যক:</strong> ব্যাংক/ক্যাশ লেজার, ক্রেডিট লেজার, পার্টি (সাপ্লায়ার হলে)।</p>
                                </div>
                            </div>
                            <div class="col-lg-6 mb-3">
                                <div class="border rounded p-3 bg-light">
                                    <h5 class="h6 font-weight-bold text-success">Receipt Voucher (রশিদ ভাউচার)</h5>
                                    <p class="small text-muted mb-1"><strong>ব্যবহার:</strong> নগদ বা ব্যাংকে যেকোনো উপায়ে টাকা ঢুকলে (যেমন: কাস্টমারের বকেয়া আদায়, অফিশিয়াল ইকুইপমেন্ট বিক্রি)।</p>
                                    <p class="small text-muted mb-1"><strong>ডাটা আবশ্যক:</strong> ব্যাংক/ক্যাশ লেজার, ডেবিট লেজার, পার্টি (বায়ার হলে)।</p>
                                </div>
                            </div>
                            <div class="col-lg-6 mb-3">
                                <div class="border rounded p-3 bg-light">
                                    <h5 class="h6 font-weight-bold text-info">Journal Voucher (জার্নাল ভাউচার)</h5>
                                    <p class="small text-muted mb-1"><strong>ব্যবহার:</strong> যেখানে নগদ বা ব্যাংকিং লেনদেন নেই (যেমন: অবচয় ধার্যকরণ, বকেয়া বেতন এন্ট্রি, সমন্বয় দাখিলা)।</p>
                                    <p class="small text-muted mb-1"><strong>ডাটা আবশ্যক:</strong> ন্যূনতম দুটি লেজার যাদের ডেবিট-ক্রেডিট যোগফল সমান হবে।</p>
                                </div>
                            </div>
                            <div class="col-lg-6 mb-3">
                                <div class="border rounded p-3 bg-light">
                                    <h5 class="h6 font-weight-bold text-warning">Contra Voucher (কন্ট্রা ভাউচার)</h5>
                                    <p class="small text-muted mb-1"><strong>ব্যবহার:</strong> যখন নগদ টাকা ক্যাশ থেকে ব্যাংকে জমা দেওয়া হয় বা ব্যাংক থেকে ক্যাশে তোলা হয় (শুধু ইন্টারনাল ফান্ড ট্রান্সফার)।</p>
                                    <p class="small text-muted mb-1"><strong>ডাটা আবশ্যক:</strong> ডেবিট ও ক্রেডিট উভয় লেজারই ক্যাশ বা ব্যাংক লেজার হতে হবে।</p>
                                </div>
                            </div>
                        </div>

                        <!-- Cost Center & Party Guides (Enhanced) -->
                        <div class="row mt-4">
                            <div class="col-lg-6 mb-3">
                                <div class="border border-warning rounded p-3 bg-white h-100">
                                    <h4 class="h6 font-weight-bold text-warning"><i class="fa fa-tags mr-1"></i> Cost Center Guide</h4>
                                    <p class="small text-muted mb-2">Cost Center = খরচ বা আয়ের <strong>উৎস চিহ্নিত করার ট্যাগ</strong>।</p>
                                    <ul class="small pl-3 text-muted mb-0">
                                        <li class="mb-1"><strong>Unlock:</strong> Account Type = <code>Expense</code> বা <code>Revenue</code> হলে</li>
                                        <li class="mb-1"><strong>Locked:</strong> Asset / Liability / Equity লেজারে নিষ্ক্রিয়</li>
                                        <li class="mb-1"><strong>Type:</strong> Department / Branch / Project / Style</li>
                                        <li class="mb-1"><strong>Report Effect:</strong> Dept-wise P&amp;L, Style Costing, Branch Comparison</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-lg-6 mb-3">
                                <div class="border border-info rounded p-3 bg-white h-100">
                                    <h4 class="h6 font-weight-bold text-info"><i class="fa fa-users mr-1"></i> Party Guide</h4>
                                    <p class="small text-muted mb-1"><strong>Party কী?</strong> Buyer / Supplier / Employee</p>
                                    <ul class="small pl-3 text-muted mb-0">
                                        <li class="mb-1"><strong>উৎস:</strong> Buyer Master / Supplier Master / Employee Master</li>
                                        <li class="mb-1"><strong>Unlock:</strong> Account Name-এ <code>receivable</code> বা <code>payable</code> থাকলে</li>
                                        <li class="mb-1"><strong>কেন লাগে:</strong> প্রতিটি Buyer/Supplier-এর বকেয়া আলাদাভাবে ট্র্যাক করতে</li>
                                        <li class="mb-1"><strong>Reports:</strong> Party Outstanding, Aging (Receivable/Payable), Party Ledger</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <h5 class="font-weight-bold text-info mt-4 mb-3"><i class="fa fa-chart-line mr-2"></i>Party Report — কীভাবে তৈরি হচ্ছে?</h5>
                        <div class="table-responsive mb-4">
                            <table class="table table-sm table-bordered">
                                <thead style="background:#1e3a5f; color:#fff;"><tr><th>Report Name</th><th>Party Type</th><th>কী দেখা যায়</th><th>Route</th></tr></thead>
                                <tbody>
                                    <tr><td>Aging Report (Receivable)</td><td>Buyer</td><td>Buyer-এর 30/60/90 days বকেয়া breakdown</td><td>Reports → Management &amp; Audit</td></tr>
                                    <tr><td>Aging Report (Payable)</td><td>Supplier</td><td>Supplier-কে কত টাকা দিতে হবে, কত পুরনো</td><td>Reports → Management &amp; Audit</td></tr>
                                    <tr><td>Party Ledger Balance</td><td>All</td><td>নির্দিষ্ট Buyer/Supplier-এর সব লেনদেন</td><td>Reports → Account Balance</td></tr>
                                    <tr><td>Party Outstanding</td><td>All</td><td>মোট বকেয়া দেনা-পাওনার তালিকা</td><td>Reports → Management &amp; Audit</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Relation between COA Names and Voucher Fields -->
                        <div class="border border-teal rounded p-4 bg-white mt-4">
                            <h4 class="h6 font-weight-bold text-teal mb-3"><i class="fa fa-exchange-alt"></i> Chart of Accounts (COA) এর সাথে ভাউচার ফিল্ডের সম্পর্ক:</h4>
                            <p class="small text-muted leading-relaxed">
                                ভাউচার এন্ট্রির সময় (Journal Voucher এবং Cash & Bank Vouchers) কোন লাইনে <strong>Cost Center</strong> এবং <strong>Party Type/Ledger</strong> এর অতিরিক্ত বক্সগুলো আনলক হবে কি না, তা সম্পূর্ণ নির্ভর করে Chart of Accounts (COA)-এ ঐ লেজার অ্যাকাউন্টের <code>Type</code> এবং <code>Name</code> (নাম)-এর কিওয়ার্ডের ওপর। এটি ডাটার গুণগত মান বজায় রাখতে সিস্টেমের একটি স্বয়ংক্রিয় ফিল্টারিং লজিক:
                            </p>
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <div class="border-left border-info pl-3 py-1">
                                        <strong class="small text-dark d-block mb-1">১. কস্ট সেন্টার (Cost Center) আনলক হওয়ার শর্ত:</strong>
                                        <p class="small text-muted mb-0">
                                            যদি সিলেক্ট করা লেজারের <strong>Account Type</strong> হয় <code>Expense</code> (ব্যয়) অথবা <code>Revenue</code> (আয়), তাহলে কস্ট সেন্টার ড্রপডাউনটি আনলক হবে। কারণ লাভ-ক্ষতির সাথে জড়িত প্রতিটি খরচ বা আয়ের বিপরীতে ডিপার্টমেন্ট বা প্রজেক্ট ট্র্যাকিং বাধ্যতামূলক।
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="border-left border-info pl-3 py-1">
                                        <strong class="small text-dark d-block mb-1">২. পার্টি (Party/Buyer/Supplier) আনলক হওয়ার শর্ত:</strong>
                                        <p class="small text-muted mb-0">
                                            যদি সিলেক্ট করা লেজারের <strong>Account Name</strong>-এর মধ্যে ইংরেজি শব্দ <code>receivable</code> (পাওনা) অথবা <code>payable</code> (দেনা) কিওয়ার্ড থাকে, তবে পার্টি টাইপ ও পার্টি লেজার ইনপুট বক্সটি আনলক হবে। এর ফলে কোম্পানি সহজেই পার্টি-ভিত্তিক দেনা-পাওনার খতিয়ান মেলাতে পারে।
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION: Workflows -->
            <div id="section-workflows" class="doc-section">
                <div class="card card-custom">
                    <div class="card-custom-header d-flex justify-content-between align-items-center">
                        <h2 class="h4 mb-0 text-primary font-weight-bold">১৪. ব্যবসায়িক SOP ও ডাবল-এন্ট্রি ওয়ার্কফ্লো</h2>
                        <span class="badge badge-success badge-custom">Workflows</span>
                    </div>
                    <div class="card-body">
                        <h4 class="h6 font-weight-bold text-dark mb-3">পণ্য ক্রয় ও পেমেন্ট দেওয়ার এন্ড-টু-এন্ড প্রসেস (Procure-to-Pay SOP):</h4>
                        <div class="flow-container py-3">
                            <div class="flow-node">১. রিকুইজিশন তৈরি (Requisition)</div>
                            <div class="flow-arrow"><i class="fa fa-arrow-down"></i></div>
                            <div class="flow-node">২. পারচেজ অর্ডার বা পিও (Purchase Order)</div>
                            <div class="flow-arrow"><i class="fa fa-arrow-down"></i></div>
                            <div class="flow-node">৩. পণ্য গুদামে গ্রহণ বা জিআরএন (Goods Receive Note)</div>
                            <div class="flow-arrow"><i class="fa fa-arrow-down"></i></div>
                            <div class="flow-node">৪. পারচেজ বিল পোস্টিং (Purchase Voucher)<br><span class="small text-muted">Debit: Raw Material Inventory, Credit: Supplier AP</span></div>
                            <div class="flow-arrow"><i class="fa fa-arrow-down"></i></div>
                            <div class="flow-node">৫. সরবরাহকারীকে ব্যাংক চেক প্রদান (Payment Voucher)<br><span class="small text-muted">Debit: Supplier AP, Credit: Bank Account</span></div>
                            <div class="flow-arrow"><i class="fa fa-arrow-down"></i></div>
                            <div class="flow-node text-white" style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);">৬. ব্যাংক স্টেটমেন্ট মেলালে ফাইনাল ম্যাচিং সম্পন্ন হবে (Reconciliation)</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION: Demo Data -->
            <div id="section-demodata" class="doc-section">
                <div class="card card-custom">
                    <div class="card-custom-header d-flex justify-content-between align-items-center">
                        <h2 class="h4 mb-0 text-primary font-weight-bold">১৪ (ক). ১০ সেট রিয়েল-লাইফ টেস্ট ডেমো ডাটা (Demo Data & Setup Grid)</h2>
                        <span class="badge badge-success badge-custom">Demo Data</span>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-4">
                            অ্যাকাউন্টস সিস্টেমটি সঠিকভাবে কাজ করছে কি না বা নতুন কোনো ডাটা এন্ট্রি টেস্ট করতে চান? নিচে ১০টি বাস্তবভিত্তিক ব্যবসায়িক লেনদেনের ডেমো ডাটা দেওয়া হলো। এর মাধ্যমে আপনি চার্ট অফ অ্যাকাউন্টস (COA)-এ প্রয়োজনীয় লেজার খতিয়ান তৈরি করা থেকে শুরু করে ভাউচার পোস্টিংয়ের সম্পূর্ণ পরীক্ষা করতে পারবেন।
                        </p>

                        <!-- Accordion or Tables of 10 Data Sets -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-custom">
                                <thead>
                                    <tr>
                                        <th style="width: 5%;">ক্র.নং</th>
                                        <th style="width: 25%;">ব্যবসায়িক ঘটনা (Scenario)</th>
                                        <th style="width: 35%;">ধাপ ১: Chart of Accounts (COA) সেটআপ</th>
                                        <th style="width: 35%;">ধাপ ২: ভাউচার এন্ট্রি (Manual Entry) পোস্টিং</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- 1. Rent Security Deposit -->
                                    <tr>
                                        <td class="text-center font-weight-bold">১</td>
                                        <td><strong>অফিস জামানত প্রদান:</strong> নতুন ডেকোরেশনের জন্য অফিসের মালিককে ক্যাশ সিকিউরিটি ডিপোজিট বাবদ ১,০০,০০০ টাকা অগ্রিম প্রদান।</td>
                                        <td>
                                            <ul class="pl-3 mb-0 small text-muted">
                                                <li><strong>কোড:</strong> <code>1220-001</code></li>
                                                <li><strong>নাম:</strong> <code>Office Security Deposit</code></li>
                                                <li><strong>টাইপ:</strong> <code>Asset</code></li>
                                                <li><strong>প্যারেন্ট:</strong> <code>Non-Current Assets</code></li>
                                                <li><strong>Reconcilable:</strong> না</li>
                                            </ul>
                                        </td>
                                        <td>
                                            <ul class="pl-3 mb-0 small text-muted">
                                                <li><strong>ভাউচার টাইপ:</strong> Payment Voucher (PV)</li>
                                                <li><strong>Main Cash/Bank:</strong> <code>DBBL Bank A/C</code></li>
                                                <li><strong>Against Account:</strong> <code>Office Security Deposit</code></li>
                                                <li><strong>কস্ট সেন্টার:</strong> N/A (প্রয়োজন নেই)</li>
                                                <li><strong>পার্টি:</strong> N/A (প্রয়োজন নেই)</li>
                                                <li><strong>টাকা:</strong> <code>100,000</code> BDT</li>
                                                <li><strong>Narration:</strong> <code>Security deposit paid for new office building</code></li>
                                            </ul>
                                        </td>
                                    </tr>
                                    <!-- 2. Share Capital Initial Capital -->
                                    <tr>
                                        <td class="text-center font-weight-bold">২</td>
                                        <td><strong>প্রারম্ভিক মূলধন বিনিয়োগ:</strong> কোম্পানির অংশীদারদের শেয়ার মূলধন বাবদ ৫০,০০,০০০ টাকা ডাচ-বাংলা ব্যাংক অ্যাকাউন্টে জমা করা হলো।</td>
                                        <td>
                                            <ul class="pl-3 mb-0 small text-muted">
                                                <li><strong>কোড:</strong> <code>3110-001</code></li>
                                                <li><strong>নাম:</strong> <code>Share Capital</code></li>
                                                <li><strong>টাইপ:</strong> <code>Equity</code></li>
                                                <li><strong>প্যারেন্ট:</strong> <code>Capital/Equity</code></li>
                                                <li><strong>Reconcilable:</strong> না</li>
                                            </ul>
                                        </td>
                                        <td>
                                            <ul class="pl-3 mb-0 small text-muted">
                                                <li><strong>ভাউচার টাইপ:</strong> Receipt Voucher (RV)</li>
                                                <li><strong>Main Cash/Bank:</strong> <code>DBBL Bank A/C</code></li>
                                                <li><strong>Against Account:</strong> <code>Share Capital</code></li>
                                                <li><strong>কস্ট সেন্টার:</strong> N/A</li>
                                                <li><strong>পার্টি:</strong> N/A</li>
                                                <li><strong>টাকা:</strong> <code>5,000,000</code> BDT</li>
                                                <li><strong>Narration:</strong> <code>Initial capital invested by directors in DBBL A/C</code></li>
                                            </ul>
                                        </td>
                                    </tr>
                                    <!-- 3. Printing Expense -->
                                    <tr>
                                        <td class="text-center font-weight-bold">৩</td>
                                        <td><strong>স্টেশনারি ক্রয়:</strong> অফিসের ব্যবহারের জন্য মেমোপ্যাড ও ফাইল কভার তৈরিতে ক্যাশ বক্স থেকে ৪,৫০০ টাকা খরচ করা হলো।</td>
                                        <td>
                                            <ul class="pl-3 mb-0 small text-muted">
                                                <li><strong>কোড:</strong> <code>5110-003</code></li>
                                                <li><strong>নাম:</strong> <code>Printing and Stationery</code></li>
                                                <li><strong>টাইপ:</strong> <code>Expense</code></li>
                                                <li><strong>প্যারেন্ট:</strong> <code>Administrative Expenses</code></li>
                                                <li><strong>Reconcilable:</strong> না</li>
                                            </ul>
                                        </td>
                                        <td>
                                            <ul class="pl-3 mb-0 small text-muted">
                                                <li><strong>ভাউচার টাইপ:</strong> Payment Voucher (PV)</li>
                                                <li><strong>Main Cash/Bank:</strong> <code>Petty Cash Account</code></li>
                                                <li><strong>Against Account:</strong> <code>Printing and Stationery</code></li>
                                                <li><strong>কস্ট সেন্টার:</strong> <code>Dhaka Head Office</code> (বাধ্যতামূলক)</li>
                                                <li><strong>পার্টি:</strong> N/A</li>
                                                <li><strong>টাকা:</strong> <code>4,500</code> BDT</li>
                                                <li><strong>Narration:</strong> <code>Memo pad and file folders printing cost</code></li>
                                            </ul>
                                        </td>
                                    </tr>
                                    <!-- 4. Credit Sales to Buyer -->
                                    <tr>
                                        <td class="text-center font-weight-bold">৪</td>
                                        <td><strong>বাকিতে পোশাক বিক্রয়:</strong> বায়ার করিম ফ্যাশনস লিমিটেডের নিকট ১৫,০০,০০০ টাকার তৈরি পোশাক বাকিতে রপ্তানি সম্পন্ন হলো।</td>
                                        <td>
                                            <ul class="pl-3 mb-0 small text-muted">
                                                <li><strong>১. কাস্টমার লেজার:</strong> কোড <code>1130-001</code>, নাম <code>Accounts Receivable - Buyers</code>, টাইপ <code>Asset</code> (প্যারেন্ট: Current Assets).</li>
                                                <li><strong>২. রেভিনিউ লেজার:</strong> কোড <code>4110-001</code>, নাম <code>Export Sales Revenue</code>, টাইপ <code>Revenue</code> (প্যারেন্ট: Sales).</li>
                                            </ul>
                                        </td>
                                        <td>
                                            <ul class="pl-3 mb-0 small text-muted">
                                                <li><strong>ভাউচার টাইপ:</strong> Journal Voucher (JV)</li>
                                                <li><strong>রো ১:</strong> <code>Accounts Receivable - Buyers</code> (Dr) | টাকা: <code>1,500,000</code> | পার্টি: <code>Karim Fashion</code> (Buyer) | কস্ট সেন্টার: N/A</li>
                                                <li><strong>রো ২:</strong> <code>Export Sales Revenue</code> (Cr) | টাকা: <code>1,500,000</code> | পার্টি: N/A | কস্ট সেন্টার: <code>Style Order ST-2026</code></li>
                                                <li><strong>Narration:</strong> <code>Sales invoice #INV-001 for Style ST-2026 to Karim Fashion</code></li>
                                            </ul>
                                        </td>
                                    </tr>
                                    <!-- 5. Cheque Collection from Buyer -->
                                    <tr>
                                        <td class="text-center font-weight-bold">৫</td>
                                        <td><strong>বায়ার থেকে পেমেন্ট আদায়:</strong> বায়ার করিম ফ্যাশনস আগের বাকিতে কেনা চালানের বিপরীতে ১৫,০০,০০০ টাকার চেক পরিশোধ করলো যা ডাচ বাংলা ব্যাংকে জমা হলো।</td>
                                        <td>
                                            <span class="text-muted small">৪ নং চালানের তৈরি করা <code>Accounts Receivable - Buyers</code> লেজারটিই এখানে ব্যবহৃত হবে। নতুন সেটআপের প্রয়োজন নেই।</span>
                                        </td>
                                        <td>
                                            <ul class="pl-3 mb-0 small text-muted">
                                                <li><strong>ভাউচার টাইপ:</strong> Receipt Voucher (RV)</li>
                                                <li><strong>Main Cash/Bank:</strong> <code>DBBL Bank A/C</code></li>
                                                <li><strong>Against Account:</strong> <code>Accounts Receivable - Buyers</code></li>
                                                <li><strong>কস্ট সেন্টার:</strong> N/A</li>
                                                <li><strong>পার্টি:</strong> <code>Karim Fashion</code> (Buyer) (বাধ্যতামূলক)</li>
                                                <li><strong>টাকা:</strong> <code>1,500,000</code> BDT</li>
                                                <li><strong>Narration:</strong> <code>Cheque collection against invoice #INV-001</code></li>
                                            </ul>
                                        </td>
                                    </tr>
                                    <!-- 6. Raw Material Purchase from Supplier -->
                                    <tr>
                                        <td class="text-center font-weight-bold">৬</td>
                                        <td><strong>বাকিতে সুতা ক্রয়:</strong> সরবরাহকারী রহিম ট্রেডার্সের নিকট থেকে ৮,০০,০০০ টাকার সুতা বাকিতে ফ্যাক্টরি গুদামে গ্রহণ করা হলো।</td>
                                        <td>
                                            <ul class="pl-3 mb-0 small text-muted">
                                                <li><strong>১. ইনভেন্টরি লেজার:</strong> কোড <code>1210-001</code>, নাম <code>Raw Material Inventory</code>, টাইপ <code>Asset</code> (প্যারেন্ট: Current Assets).</li>
                                                <li><strong>২. সাপ্লায়ার লেজার:</strong> কোড <code>2110-001</code>, নাম <code>Accounts Payable - Suppliers</code>, টাইপ <code>Liability</code> (প্যারেন্ট: Current Liabilities).</li>
                                            </ul>
                                        </td>
                                        <td>
                                            <ul class="pl-3 mb-0 small text-muted">
                                                <li><strong>ভাউচার টাইপ:</strong> Journal Voucher (JV)</li>
                                                <li><strong>রো ১:</strong> <code>Raw Material Inventory</code> (Dr) | টাকা: <code>800,000</code> | পার্টি: N/A | কস্ট সেন্টার: N/A</li>
                                                <li><strong>রো ২:</strong> <code>Accounts Payable - Suppliers</code> (Cr) | টাকা: <code>800,000</code> | পার্টি: <code>Rahim Traders</code> (Supplier) | কস্ট সেন্টার: N/A</li>
                                                <li><strong>Narration:</strong> <code>Cotton yarn purchase on credit from Rahim Traders, Challan #CH-8820</code></li>
                                            </ul>
                                        </td>
                                    </tr>
                                    <!-- 7. Payment to Supplier -->
                                    <tr>
                                        <td class="text-center font-weight-bold">৭</td>
                                        <td><strong>সাপ্লায়ার বিল পরিশোধ:</strong> সুতা সরবরাহকারী রহিম ট্রেডার্সের পূর্বের ৮,০০,০০০ টাকা বকেয়া বিল ব্যাংক একাউন্টের মাধ্যমে পরিশোধ করা হলো।</td>
                                        <td>
                                            <span class="text-muted small">৬ নং চালানে তৈরি করা <code>Accounts Payable - Suppliers</code> লেজারটিই এখানে ব্যবহৃত হবে।</span>
                                        </td>
                                        <td>
                                            <ul class="pl-3 mb-0 small text-muted">
                                                <li><strong>ভাউচার টাইপ:</strong> Payment Voucher (PV)</li>
                                                <li><strong>Main Cash/Bank:</strong> <code>DBBL Bank A/C</code></li>
                                                <li><strong>Against Account:</strong> <code>Accounts Payable - Suppliers</code></li>
                                                <li><strong>কস্ট সেন্টার:</strong> N/A</li>
                                                <li><strong>পার্টি:</strong> <code>Rahim Traders</code> (Supplier) (বাধ্যতামূলক)</li>
                                                <li><strong>টাকা:</strong> <code>800,000</code> BDT</li>
                                                <li><strong>Narration:</strong> <code>Settlement payment to Rahim Traders, Challan #CH-8820</code></li>
                                            </ul>
                                        </td>
                                    </tr>
                                    <!-- 8. Salaries Integration Setup -->
                                    <tr>
                                        <td class="text-center font-weight-bold">৮</td>
                                        <td><strong>মাসিক পেরোল পোস্টিং:</strong> জুন ২০২৬ মাসের কর্মকর্তা ও কর্মচারীদের মোট প্রদেয় বেতন ৫,২০,০০০ টাকা সমন্বয় করা হলো।</td>
                                        <td>
                                            <ul class="pl-3 mb-0 small text-muted">
                                                <li><strong>১.</strong> কোড <code>5110-001</code>, নাম <code>Salary Expense</code>, টাইপ <code>Expense</code></li>
                                                <li><strong>২.</strong> কোড <code>2120-001</code>, নাম <code>PF Liability</code>, টাইপ <code>Liability</code></li>
                                                <li><strong>৩.</strong> কোড <code>1140-001</code>, নাম <code>Advance Salary</code>, টাইপ <code>Asset</code></li>
                                                <li><strong>৪.</strong> কোড <code>2130-001</code>, নাম <code>Salary Payable</code>, টাইপ <code>Liability</code></li>
                                            </ul>
                                        </td>
                                        <td>
                                            <p class="small text-muted mb-1"><code>Manual Payroll Entry Screen</code>-এ গিয়ে নিচের ফর্ম ডাটা সাবমিট দিন:</p>
                                            <ul class="pl-3 mb-0 small text-muted">
                                                <li><strong>Gross Salary:</strong> <code>500000</code> | <strong>Allowances:</strong> <code>50000</code> | <strong>Bonus:</strong> <code>30000</code></li>
                                                <li><strong>PF Deduction:</strong> <code>40000</code> | <strong>Advance Adjusted:</strong> <code>20000</code> | <strong>Net Payable:</strong> <code>520000</code></li>
                                                <li>(মোট ডেবিট: ৫,৮০,০০০ BDT = মোট ক্রেডিট: ৫,৮০,০০০ BDT)</li>
                                            </ul>
                                        </td>
                                    </tr>
                                    <!-- 9. Depreciation JV -->
                                    <tr>
                                        <td class="text-center font-weight-bold">৯</td>
                                        <td><strong>মেশিনের মাসিক অবচয়:</strong> কারখানার ভারী সুইং মেশিনারিজের জুন মাসের নির্ধারিত অবচয় বাবদ ২৫,০০০ টাকা হিসাবভুক্তকরণ।</td>
                                        <td>
                                            <ul class="pl-3 mb-0 small text-muted">
                                                <li><strong>১. অবচয় খরচ লেজার:</strong> কোড <code>5120-001</code>, নাম <code>Depreciation Expense</code>, টাইপ <code>Expense</code> (প্যারেন্ট: Operating Expenses).</li>
                                                <li><strong>২. অবচয় সঞ্চিতি লেজার:</strong> কোড <code>1310-002</code>, নাম <code>Accumulated Depreciation - Machinery</code>, টাইপ <code>Asset</code> (প্যারেন্ট: Fixed Assets).</li>
                                            </ul>
                                        </td>
                                        <td>
                                            <ul class="pl-3 mb-0 small text-muted">
                                                <li><strong>ভাউচার টাইপ:</strong> Journal Voucher (JV)</li>
                                                <li><strong>রো ১:</strong> <code>Depreciation Expense</code> (Dr) | টাকা: <code>25000</code> | পার্টি: N/A | কস্ট সেন্টার: <code>Factory Production Unit</code></li>
                                                <li><strong>রো ২:</strong> <code>Accumulated Depreciation - Machinery</code> (Cr) | টাকা: <code>25000</code> | পার্টি: N/A | কস্ট সেন্টার: N/A</li>
                                                <li><strong>Narration:</strong> <code>Monthly machinery depreciation expense for factory sewing units</code></li>
                                            </ul>
                                        </td>
                                    </tr>
                                    <!-- 10. Cash to Bank Contra -->
                                    <tr>
                                        <td class="text-center font-weight-bold">১০</td>
                                        <td><strong>ব্যাংক থেকে ক্যাশ উত্তোলন:</strong> খুচরা খরচ নির্বাহের জন্য কোম্পানির ব্যাংক অ্যাকাউন্ট থেকে ৫০,০০০ টাকা নগদ উত্তোলন করে পেটি ক্যাশে রাখা হলো।</td>
                                        <td>
                                            <ul class="pl-3 mb-0 small text-muted">
                                                <li><strong>১. পেটি ক্যাশ লেজার:</strong> কোড <code>1110-001</code>, নাম <code>Petty Cash Account</code>, টাইপ <code>Asset</code></li>
                                                <li><strong>২. ব্যাংক লেজার:</strong> কোড <code>1120-005</code>, নাম <code>DBBL Bank A/C</code>, টাইপ <code>Asset</code></li>
                                            </ul>
                                        </td>
                                        <td>
                                            <ul class="pl-3 mb-0 small text-muted">
                                                <li><strong>ভাউচার টাইপ:</strong> Journal Voucher (JV) or Contra Voucher</li>
                                                <li><strong>রো ১:</strong> <code>Petty Cash Account</code> (Dr) | টাকা: <code>50000</code> | পার্টি: N/A | কস্ট সেন্টার: N/A</li>
                                                <li><strong>রো ২:</strong> <code>DBBL Bank A/C</code> (Cr) | টাকা: <code>50000</code> | পার্টি: N/A | কস্ট সেন্টার: N/A</li>
                                                <li><strong>Narration:</strong> <code>Petty cash replenishment - cash withdrawn from DBBL</code></li>
                                            </ul>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION: Troubleshoot & FAQ -->
            <div id="section-troubleshoot" class="doc-section">
                <div class="card card-custom">
                    <div class="card-custom-header d-flex justify-content-between align-items-center">
                        <h2 class="h4 mb-0 text-primary font-weight-bold">১৫. সাধারণ ভুলসমূহ (Common Mistakes) ও FAQ</h2>
                        <span class="badge badge-danger badge-custom">FAQ & Help</span>
                    </div>
                    <div class="card-body">

                        <h5 class="font-weight-bold text-danger mb-3"><i class="fa fa-exclamation-triangle mr-2"></i>সচরাচর সমস্যা ও সমাধান (Error Reference Table)</h5>
                        <div class="table-responsive mb-5">
                            <table class="table table-sm table-bordered">
                                <thead style="background:#7f1d1d; color:#fff;">
                                    <tr>
                                        <th>#</th>
                                        <th>Error / সমস্যা</th>
                                        <th>Root Cause</th>
                                        <th>সমাধান</th>
                                        <th>Affected Module</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td><code>Voucher submission blocked (period closed)</code></td>
                                        <td>ভাউচারের তারিখ বন্ধ অর্থবছর বা মাসে পড়েছে</td>
                                        <td><strong>Tax Rates &amp; Financial Periods</strong> স্ক্রিনে গিয়ে ঐ মাসের স্ট্যাটাস <em>Open</em> করুন</td>
                                        <td>All Vouchers</td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td>Cost Center dropdown আসছে না</td>
                                        <td>সিলেক্ট করা লেজারের টাইপ <code>Expense</code> নয়</td>
                                        <td>COA-তে লেজারের Account Type পুনরায় <code>Expense</code> নির্ধারণ করুন</td>
                                        <td>Payment Voucher</td>
                                    </tr>
                                    <tr>
                                        <td>3</td>
                                        <td>Bank Reconciliation-এ বুক এন্ট্রি পাওয়া যাচ্ছে না</td>
                                        <td>Cash &amp; Bank Voucher থেকে পোস্টিং দেওয়া হয়নি</td>
                                        <td>Cash &amp; Bank Voucher ফর্মে গিয়ে আগে পোস্টিং দিন, তারপর রিকনসিলিয়েশনে আসুন</td>
                                        <td>Bank Reconciliation</td>
                                    </tr>
                                    <tr>
                                        <td>4</td>
                                        <td><code>Required payroll ledger accounts were not found.</code></td>
                                        <td>COA-তে Payroll-এর ৪টি mandatory লেজার নেই বা নাম/টাইপ ভুল</td>
                                        <td>নিচের COA Seeder রান করুন বা ম্যানুয়ালি ৪টি লেজার তৈরি করুন (keyword + type অবশ্যই মিলতে হবে)</td>
                                        <td>Payroll Posting</td>
                                    </tr>
                                    <tr>
                                        <td>5</td>
                                        <td><code>Required inventory ledger accounts were not found.</code></td>
                                        <td>COA-তে Inventory-এর mandatory লেজার (raw material / supplier payable / wip) অনুপস্থিত</td>
                                        <td>নিচের COA Seeder রান করুন বা Inventory COA table অনুযায়ী লেজার তৈরি করুন</td>
                                        <td>Inventory Journal</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h5 class="font-weight-bold text-info mb-3"><i class="fa fa-database mr-2"></i>Laravel COA Seeder — সকল Mandatory রেকর্ড</h5>
                        <div class="alert-callout alert-callout-note mb-3">
                            <strong>নোট:</strong> নিচের Seeder টি Production এ একবার রান করলে সকল mandatory COA রেকর্ড <code>updateOrInsert</code> দিয়ে নিরাপদে তৈরি হবে।
                            বিদ্যমান রেকর্ড থাকলে আপডেট হবে, না থাকলে তৈরি হবে।
                        </div>
                        <div class="seeder-block">
&lt;?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ErpAccountMandatoryCOASeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            // ── PAYROLL ──────────────────────────────────────────────────
            [
                'account_name' =&gt; 'Salary Expense - Factory',
                'account_type' =&gt; 'Expense',
                'account_code' =&gt; '5001001',
                'is_active'    =&gt; 1,
                'description'  =&gt; 'Mandatory: keyword "salary expense" (Payroll Dr)',
            ],
            [
                'account_name' =&gt; 'PF Liability - Staff',
                'account_type' =&gt; 'Liability',
                'account_code' =&gt; '2003001',
                'is_active'    =&gt; 1,
                'description'  =&gt; 'Mandatory: keyword "pf liability" (Payroll Cr)',
            ],
            [
                'account_name' =&gt; 'Advance Salary - Workers',
                'account_type' =&gt; 'Asset',
                'account_code' =&gt; '1004001',
                'is_active'    =&gt; 1,
                'description'  =&gt; 'Mandatory: keyword "advance salary" (Payroll Cr)',
            ],
            [
                'account_name' =&gt; 'Salary Payable - Current Month',
                'account_type' =&gt; 'Liability',
                'account_code' =&gt; '2001001',
                'is_active'    =&gt; 1,
                'description'  =&gt; 'Mandatory: keyword "salary payable" (Payroll Cr)',
            ],

            // ── INVENTORY ────────────────────────────────────────────────
            [
                'account_name' =&gt; 'Raw Material Inventory - Fabric',
                'account_type' =&gt; 'Asset',
                'account_code' =&gt; '1002001',
                'is_active'    =&gt; 1,
                'description'  =&gt; 'Mandatory: keyword "raw material" (Inventory Dr/Cr)',
            ],
            [
                'account_name' =&gt; 'Supplier Payable - Local',
                'account_type' =&gt; 'Liability',
                'account_code' =&gt; '2002001',
                'is_active'    =&gt; 1,
                'description'  =&gt; 'Mandatory: keyword "supplier payable" (Purchase Cr)',
            ],
            [
                'account_name' =&gt; 'WIP - Cutting Section',
                'account_type' =&gt; 'Asset',
                'account_code' =&gt; '1003001',
                'is_active'    =&gt; 1,
                'description'  =&gt; 'Mandatory: keyword "wip" (Issue to Production Dr)',
            ],
            [
                'account_name' =&gt; 'Inventory Adjustment Loss',
                'account_type' =&gt; 'Expense',
                'account_code' =&gt; '5002001',
                'is_active'    =&gt; 1,
                'description'  =&gt; 'Mandatory: keyword "inventory adjustment" (Adj Dr)',
            ],

            // ── BANK / RECONCILIATION ────────────────────────────────────
            [
                'account_name' =&gt; 'Dutch Bangla Bank - Current A/C',
                'account_type' =&gt; 'Asset',
                'account_code' =&gt; '1001001',
                'is_active'    =&gt; 1,
                'description'  =&gt; 'Mandatory: keyword "bank" (Bank Reconciliation)',
            ],
            [
                'account_name' =&gt; 'Cash In Hand',
                'account_type' =&gt; 'Asset',
                'account_code' =&gt; '1001002',
                'is_active'    =&gt; 1,
                'description'  =&gt; 'Mandatory: keyword "cash" (Petty Cash / Cash Voucher)',
            ],
        ];

        foreach ($accounts as $account) {
            DB::table('chart_of_accounts')-&gt;updateOrInsert(
                ['account_name' =&gt; $account['account_name']],
                array_merge($account, [
                    'created_at' =&gt; now(),
                    'updated_at' =&gt; now(),
                ])
            );
        }

        $this-&gt;command-&gt;info('ErpAccount mandatory COA records seeded successfully.');
    }
}
                        </div>

                        <!-- FAQ -->
                        <h5 class="font-weight-bold text-dark mt-5 mb-3"><i class="fa fa-question-circle text-primary mr-2"></i>সচরাচর জিজ্ঞাসিত প্রশ্নাবলী (FAQ)</h5>
                        <div class="accordion" id="faqAccordion">
                            <div class="card border-0 mb-2">
                                <div class="bg-light p-2 font-weight-bold small" style="cursor:pointer;" data-toggle="collapse" data-target="#faq1">
                                    প্রশ্ন: কোনো ভাউচার ভুল পোস্টিং হলে তা এডিট বা ডিলিট করার কোনো উপায় নেই কেন?
                                </div>
                                <div id="faq1" class="collapse show p-2 border-top small text-muted">
                                    উত্তর: এটি একটি স্ট্রিক্ট ফিনান্সিয়াল স্ট্যান্ডার্ড। হিসাববিজ্ঞান নিয়ম অনুযায়ী একবার অডিট ট্রেইল রেকর্ড হলে তা সরাসরি ডিলিট করা যায় না। ভুল পোস্টিং হলে আপনাকে ভাউচারটি <code>Voucher Register</code> থেকে <code>Void</code> (বাতিল) করতে হবে এবং নতুন ভাউচার রি-পোস্ট করতে হবে।
                                </div>
                            </div>
                            <div class="card border-0 mb-2">
                                <div class="bg-light p-2 font-weight-bold small" style="cursor:pointer;" data-toggle="collapse" data-target="#faq2">
                                    প্রশ্ন: কস্ট সেন্টার সিলেক্ট করা কখন বাধ্যতামূলক হয়?
                                </div>
                                <div id="faq2" class="collapse p-2 border-top small text-muted">
                                    উত্তর: যখনই কোনো লেজারের টাইপ <code>Expense</code> (ব্যয়) বা <code>Revenue</code> (আয়) হবে, তখনই প্রজেক্ট বা ডিপার্টমেন্ট ট্র্যাকিংয়ের স্বার্থে কস্ট সেন্টার কলামটি আনলক হবে এবং ডাটা সিলেক্ট করা বাধ্যতামূলক হবে।
                                </div>
                            </div>
                            <div class="card border-0 mb-2">
                                <div class="bg-light p-2 font-weight-bold small" style="cursor:pointer;" data-toggle="collapse" data-target="#faq3">
                                    Q: বিদ্যুৎ বিল (Electricity Bill) মাসের ১ তারিখে entry করলাম, কিন্তু payment করব ১০ তারিখে — তাহলে কি ১০ তারিখে আলাদা entry করতে পারব?
                                </div>
                                <div id="faq3" class="collapse show p-3 border-top small text-muted">
                                    <strong class="text-success d-block mb-2">হ্যাঁ, অবশ্যই পারবেন — এটাই সঠিক Accrual Accounting পদ্ধতি।</strong>
                                    <p class="mb-2">এটি দুই ধাপে করতে হয়:</p>

                                    <strong class="text-dark">ধাপ ১ — ১ তারিখে Accrual Entry (Journal Voucher)</strong>
                                    <div class="journal-preview my-2" style="font-size:0.78rem;">
                                        <span class="arr">★ Electricity Bill Accrual — 01/06/2024  [Journal Voucher]</span><br>
                                        <span class="dr">  Dr.  Electricity Bill (Expense)   5,000.00  [Cost Center: Factory Dept]</span><br>
                                        <span class="cr">    Cr.  Electricity Bill Payable (Liability)  5,000.00</span><br>
                                        <span class="arr">  কারণ: বিল তৈরি হয়েছে কিন্তু এখনো টাকা দেওয়া হয়নি</span>
                                    </div>

                                    <strong class="text-dark">ধাপ ২ — ১০ তারিখে Payment Entry (Payment Voucher)</strong>
                                    <div class="journal-preview my-2" style="font-size:0.78rem;">
                                        <span class="arr">★ Electricity Bill Payment — 10/06/2024  [Payment Voucher]</span><br>
                                        <span class="dr">  Dr.  Electricity Bill Payable (Liability)  5,000.00</span><br>
                                        <span class="cr">    Cr.  Dutch Bangla Bank / Cash in Hand       5,000.00</span><br>
                                        <span class="arr">  কারণ: Liability clear হচ্ছে, ব্যাংক/ক্যাশ থেকে payment গেছে</span>
                                    </div>

                                    <div class="alert-callout alert-callout-tip mt-2 mb-0">
                                        <strong>COA দরকার:</strong> (১) <code>Electricity Bill</code> — Expense type, (২) <code>Electricity Bill Payable</code> — Liability type।
                                        Payable লেজারের নামে <code>payable</code> keyword থাকলে Party সিলেক্ট করা যাবে (যদি specific Supplier-এর কাছে দেনা থাকে)।
                                        <br><strong>সরল পথ:</strong> একই দিনে bill ও payment হলে সরাসরি Payment Voucher-এ Dr. Electricity Bill (Expense) → Cr. Bank দিলেই চলবে।
                                    </div>
                                </div>
                            </div>
                            <div class="card border-0 mb-2">
                                <div class="bg-light p-2 font-weight-bold small" style="cursor:pointer;" data-toggle="collapse" data-target="#faq4">
                                    Q: Journal Voucher বনাম Payment Voucher — কোনটি কখন ব্যবহার করব?
                                </div>
                                <div id="faq4" class="collapse p-2 border-top small text-muted">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="thead-dark"><tr><th>পরিস্থিতি</th><th>সঠিক Voucher</th><th>কারণ</th></tr></thead>
                                        <tbody>
                                            <tr><td>বিদ্যুৎ বিল তৈরি হয়েছে, payment পরে হবে</td><td>Journal Voucher</td><td>Cash/Bank movement নেই, শুধু Expense ও Payable</td></tr>
                                            <tr><td>বিদ্যুৎ বিল সরাসরি ব্যাংক থেকে পরিশোধ</td><td>Payment Voucher</td><td>ব্যাংক থেকে টাকা বের হচ্ছে</td></tr>
                                            <tr><td>বেতন Accrual (মাস শেষে)</td><td>Journal Voucher</td><td>Cash/Bank movement নেই</td></tr>
                                            <tr><td>বেতন Payment (ব্যাংক/ক্যাশে)</td><td>Payment Voucher</td><td>Bank/Cash থেকে টাকা বের হচ্ছে</td></tr>
                                            <tr><td>Buyer থেকে টাকা ব্যাংকে জমা</td><td>Receipt Voucher</td><td>Bank-এ টাকা ঢুকছে</td></tr>
                                            <tr><td>ক্যাশ থেকে ব্যাংকে জমা</td><td>Contra Voucher</td><td>দুটিই Cash/Bank — Internal Transfer</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
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
            
            // Transaction rules meta for interactive Decision Tree
            const decisionRules = {
                'rent': {
                    voucher: 'Payment Voucher',
                    debit: 'Office Rent Expense Account (খরচ লেজার)',
                    credit: 'Cash in Hand (Petty Cash Account)',
                    party: 'না (প্রয়োজন নেই)',
                    costCenter: 'হ্যাঁ (বাধ্যতামূলক, খরচ বিভাগ বা ব্রাঞ্চ ট্র্যাকিংয়ের জন্য)',
                    logic: 'ভাড়া প্রদান ব্যবসার একটি পরিচালন ব্যয়। হিসাববিজ্ঞানের গোল্ডেন রুল অনুযায়ী, খরচ বাড়লে ডেবিট (Dr) হয়, এবং ক্যাশ এসেট হ্রাস পাওয়ায় ক্রেডিট (Cr) হয়।',
                    impact: 'লাভ-ক্ষতি (P&L) রিপোর্টে খরচের পরিমাণ ১০,০০০ টাকা বৃদ্ধি পাবে যা কোম্পানির নেট প্রফিট কমিয়ে দিবে। ব্যালেন্স শিটের ক্যাশ ব্যালেন্স হ্রাস পাবে।',
                    mistake: 'ভুল করে ডাইরেক্ট জার্নাল (JV) না দিয়ে এটি ক্যাশ পেমেন্ট ভাউচারে এন্ট্রি দিবেন। কস্ট সেন্টার ভুল দিলে নির্দিষ্ট ব্রাঞ্চের খরচ ভুল হিসাব হবে।'
                },
                'customer_collect': {
                    voucher: 'Receipt Voucher',
                    debit: 'Bank Account (লেজার ব্যাংক হিসাব)',
                    credit: 'Accounts Receivable (কাস্টমার বকেয়া লেজার)',
                    party: 'হ্যাঁ (বাধ্যতামূলক, নির্দিষ্ট ক্রেতা বা Buyer লেজার সিলেক্ট করতে হবে)',
                    costCenter: 'না (প্রয়োজন নেই)',
                    logic: 'টাকা ব্যাংকে আসায় ব্যাংক নামক সম্পদ (Asset) বৃদ্ধি পেয়েছে (Debit) এবং কাস্টমারের কাছে দেনা বা পাওনা কমায় Accounts Receivable সম্পদ হ্রাস পেয়েছে (Credit)।',
                    impact: 'ব্যালেন্স শিটে ক্যাশ/ব্যাংক ব্যালেন্স বৃদ্ধি পাবে এবং একই সাথে বায়ার বকেয়া পাওনার পরিমাণ কমে ব্যালেন্স শিটের মোট সম্পদ অপরিবর্তিত থাকবে। লাভ-ক্ষতির ওপর তাৎক্ষণিক কোনো প্রভাব পড়বে না।',
                    mistake: 'পার্টি সিলেক্ট করতে ভুলে গেলে ক্রেতার পার্সোনাল খতিয়ান বা পার্টি লেজার ব্যালেন্সে টাকাটি জমা হবে না, ফলে তার স্টেটমেন্টে বকেয়া বেশি দেখাবে।'
                },
                'supplier_pay': {
                    voucher: 'Payment Voucher',
                    debit: 'Accounts Payable (সাপ্লায়ার বকেয়া লেজার)',
                    credit: 'Bank Account (ব্যাংক লেজার হিসাব)',
                    party: 'হ্যাঁ (বাধ্যতামূলক, নির্দিষ্ট সরবরাহকারী বা Supplier সিলেক্ট করতে হবে)',
                    costCenter: 'না (প্রয়োজন নেই)',
                    logic: 'সরবরাহকারীকে টাকা পরিশোধ করায় আমাদের ব্যবসার দায় (Liability) হ্রাস পেয়েছে (Debit) এবং ব্যাংক সম্পদ (Asset) হ্রাস পেয়েছে (Credit)।',
                    impact: 'ব্যালেন্স শিটে দায় কমবে এবং একই সাথে ব্যাংক ব্যালেন্স কমবে। ব্যবসার দায়বদ্ধতা পরিশোধের মাধ্যমে দায় ও সম্পদ উভয়ই হ্রাস পাবে।',
                    mistake: 'ভুল পার্টি সিলেক্ট করলে এক সরবরাহকারীর টাকা অন্য সরবরাহকারীর খাতায় পরিশোধ দেখাবে, যা পরবর্তীতে অডিট ও বিল ভেরিফিকেশনে বড় জটিলতা তৈরি করবে।'
                },
                'purchase_raw': {
                    voucher: 'Journal Voucher (JV) অথবা Purchase Bill',
                    debit: 'Raw Material Inventory Account (মজুদ সম্পদ লেজার)',
                    credit: 'Accounts Payable (সাপ্লায়ার বকেয়া খতিয়ান)',
                    party: 'হ্যাঁ (বাধ্যতামূলক, Supplier সিলেক্ট করুন)',
                    costCenter: 'না (প্রয়োজন নেই)',
                    logic: 'কাঁচামাল কেনায় কোম্পানির গুদামে ইনভেন্টরি নামক সম্পদ বৃদ্ধি পেয়েছে (Debit) এবং বাকিতে কেনায় সরবরাহকারীর কাছে দায় বা দেনা বৃদ্ধি পেয়েছে (Credit)।',
                    impact: 'ব্যালেন্স শিটে ইনভেন্টরি বা কারেন্ট এসেট বাড়বে এবং বিপরীতে কারেন্ট লায়াবিলিটি বা দেনার পরিমাণ বৃদ্ধি পাবে।',
                    mistake: 'কাঁচামাল ক্রয়ের সময় কস্ট সেন্টার দেওয়ার চেষ্টা করা। কস্ট সেন্টার সাধারণত উৎপাদন বা বিক্রয়ের সময় ব্যবহৃত হয়, সরাসরি ক্রয় অ্যাকাউন্টে প্রয়োজন হয় না।'
                },
                'depreciation': {
                    voucher: 'Journal Voucher',
                    debit: 'Depreciation Expense Account (অবচয় খরচ লেজার)',
                    credit: 'Accumulated Depreciation Account (পুঞ্জীভূত অবচয় সঞ্চিতি)',
                    party: 'না (প্রয়োজন নেই)',
                    costCenter: 'হ্যাঁ (বাধ্যতামূলক, কোন ডিপার্টমেন্টের মেশিন তার কস্ট সেন্টার সিলেক্ট করতে হবে)',
                    logic: 'স্থায়ী সম্পদের মূল্য হ্রাস পাওয়া এক ধরনের ব্যয়, তাই অবচয় ডেবিট। এবং সম্পদের নেট বুক ভ্যালু সরাসরি না কমিয়ে অবচয় সঞ্চিতি নামক বিপরীত সম্পদ (Contra-Asset) ক্রেডিট করা হয়।',
                    impact: 'লাভ-ক্ষতি (P&L) রিপোর্টে অবচয় খরচ বাড়বে ও প্রফিট কমবে এবং ব্যালেন্স শিটে স্থায়ী সম্পদের বুক ভ্যালু হ্রাস পাবে।',
                    mistake: 'এটি ক্যাশ ট্রানজ্যাকশন না হওয়ায় ভুল করে পেমেন্ট ভাউচারে পোস্টিং দেওয়া। এটি সবসময় জার্নাল ভাউচার (JV)-এ পোস্টিং দিতে হবে।'
                },
                'salary_accrual': {
                    voucher: 'Journal Voucher',
                    debit: 'Salaries & Wages Expense Account (বেতন খরচ লেজার)',
                    credit: 'Salaries Payable Account (প্রদেয় বেতন দায় লেজার)',
                    party: 'না (প্রয়োজন নেই, তবে পেরোল শিট সংযুক্ত করতে হবে)',
                    costCenter: 'হ্যাঁ (বাধ্যতামূলক, খরচটি কোন ডিপার্টমেন্ট বা প্রজেক্টের কর্মীর তা ট্র্যাক করার জন্য)',
                    logic: 'কাজ করার পর বেতন প্রদেয় হওয়ায় খরচটি বকেয়া ভিত্তিক হিসাব অনুযায়ী ধার্য করা হয়েছে, তাই খরচ ডেবিট এবং পরিশোধ না করায় Salaries Payable দায় বৃদ্ধি পেয়ে ক্রেডিট হয়েছে।',
                    impact: 'চলতি মাসের লাভ-ক্ষতি রিপোর্টে খরচ অন্তর্ভুক্ত হবে এবং ব্যালেন্স শিটে প্রদেয় বেতনের দায় তৈরি হবে।',
                    mistake: 'নগদ পরিশোধের আগেই ক্যাশ বা ব্যাংক লেজার ক্রেডিট করে দেওয়া। প্রথমে জার্নাল ভাউচার দিয়ে বকেয়া এন্ট্রি দিতে হবে এবং ব্যাংক থেকে বেতন পরিশোধের সময় পেমেন্ট ভাউচার দিতে হবে।'
                },
                'bank_transfer': {
                    voucher: 'Contra Voucher (or Cash/Bank Transfer)',
                    debit: 'Bank Account (যে ব্যাংকে জমা হলো)',
                    credit: 'Cash in Hand (ক্যাশ বক্স বা লেজার)',
                    party: 'না (প্রয়োজন নেই)',
                    costCenter: 'না (প্রয়োজন নেই)',
                    logic: 'ক্যাশ বক্স থেকে ক্যাশ নামক এসেট চলে যাওয়ায় ক্রেডিট এবং ব্যাংকে ব্যালেন্স নামক এসেট বৃদ্ধি পাওয়ায় ডেবিট হয়েছে। এটি ইন্টারনাল এসেট অদল-বদল।',
                    impact: 'ব্যালেন্স শিটের মোট সম্পদে কোনো পরিবর্তন হবে না। শুধু ক্যাশ ব্যালেন্স হ্রাস পাবে এবং ব্যাংক ব্যালেন্স সমপরিমাণ বৃদ্ধি পাবে।',
                    mistake: 'ভুল করে এটিকে সাধারণ পেমেন্ট বা রিসিভ ভাউচার দিয়ে এন্ট্রি দিলে ট্রায়াল ব্যালেন্সের অডিট ট্রেইল অগোছালো দেখাবে এবং রিকনসিলিয়েশন মিলবে না।'
                },
                'sales_credit': {
                    voucher: 'Journal Voucher / Sales Bill',
                    debit: 'Accounts Receivable (বায়ার বা ক্রেতা লেজার)',
                    credit: 'Sales Revenue Account (বিক্রয় আয় লেজার)',
                    party: 'হ্যাঁ (বাধ্যতামূলক, Buyer সিলেক্ট করুন)',
                    costCenter: 'হ্যাঁ (বাধ্যতামূলক, কোন স্টাইল বা প্রজেক্ট অর্ডারের জন্য বিক্রয় হয়েছে)',
                    logic: 'পণ্য হস্তান্তরের মাধ্যমে আয় অর্জিত হয়েছে তাই রেভিনিউ ক্রেডিট (Cr), এবং ক্রেতার কাছ থেকে টাকা পাওয়ার অধিকার বা সম্পদ তৈরি হওয়ায় বায়ার লেজার ডেবিট (Dr)।',
                    impact: 'কোম্পানির রেভিনিউ বাড়বে যা প্রফিট মার্জিন বাড়াবে এবং ব্যালেন্স শিটে কারেন্ট এসেট (Receivable) বৃদ্ধি পাবে।',
                    mistake: 'বায়ার লেজার সিলেক্ট না করে সরাসরি ক্যাশ/ব্যাংক সিলেক্ট করা। বাকিতে বিক্রির ক্ষেত্রে প্রথমে বায়ারের লেজারেই বুক করতে হবে।'
                }
            };

            $(function() {
                // Handle Chapter Navigation Click
                $('#docNavList .doc-nav-item, .quick-link-btn').on('click', function() {
                    const targetId = $(this).data('target');
                    
                    // Update active nav state
                    $('#docNavList .doc-nav-item').removeClass('active');
                    $(`#docNavList .doc-nav-item[data-target="${targetId}"]`).addClass('active');
                    
                    // Show target section with animation
                    $('.doc-section').removeClass('active');
                    $('#' + targetId).addClass('active');
                    
                    // Scroll to top of content view on mobile
                    if ($(window).width() < 768) {
                        $('html, body').animate({
                            scrollTop: $('#' + targetId).offset().top - 20
                        }, 300);
                    }
                });

                // Handle Interactive Decision Tree Select change
                $('#transactionSelector').on('change', function() {
                    const val = $(this).val();
                    const $output = $('#decisionOutput');
                    
                    if (!val) {
                        $output.addClass('d-none');
                        return;
                    }
                    
                    const rule = decisionRules[val];
                    if (rule) {
                        $('#outVoucher').text(rule.voucher);
                        $('#outDebit').text(rule.debit);
                        $('#outCredit').text(rule.credit);
                        $('#outParty').text(rule.party);
                        $('#outCostCenter').text(rule.costCenter);
                        $('#outLogic').text(rule.logic);
                        $('#outImpact').text(rule.impact);
                        $('#outMistake').text(rule.mistake);
                        
                        $output.removeClass('d-none');
                    } else {
                        $output.addClass('d-none');
                    }
                });
            });
        })(jQuery);
    </script>
@endpush
