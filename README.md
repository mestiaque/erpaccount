# Erpaccount Package

> Laravel accounts module for garment/ERP operations: chart of accounts, journal vouchers, cash/bank, bank reconciliation, financial reports, and executive dashboard.

---

## Table of Contents

1. [Installation](#installation)
2. [Permissions](#permissions)
3. [Routes](#routes)
4. [Delivery Checklist](#delivery-checklist)
5. [Features](#features)
6. [Module Tutorials](#module-tutorials)
   - [Module 1 — Payroll Management](#module-1--payroll-management)
   - [Module 2 — Inventory Financial Journal](#module-2--inventory-financial-journal)
   - [Module 3 — Commercial Bank Tracker (Bank Reconciliation)](#module-3--commercial-bank-tracker-bank-reconciliation)
7. [Troubleshooting & Developer Seed](#troubleshooting--developer-seed)

---

## Installation

```bash
composer require mestiaque/erpaccount
php artisan migrate
```

### Seed Chart of Accounts (first deploy)

Run the SQL seed against your database after migrations:

```bash
mysql -u USER -p DATABASE < vendor/mestiaque/erpaccount/helpers/chart_of_accounts.sql
```

Or copy `helpers/chart_of_accounts.sql` into your project and execute via your DB tool.

### Publish config (optional)

```bash
php artisan vendor:publish --tag=erpaccount-config
```

Adjust middleware in `config/erpaccount.php` if your app uses different auth guards:

```php
'route_middleware' => ['web', 'auth'],
'api_route_middleware' => ['api', 'auth:sanctum'],
```

---

## Permissions

Register keys from `config/erpaccount.permissions` (or package `src/Config/permission.php`) in your host app's role system:

| Key | Purpose |
|-----|---------|
| `erpaccount.view` | Browse vouchers and registers |
| `erpaccount.post` | Post journals and cash/bank vouchers |
| `erpaccount.void` | Void vouchers |
| `erpaccount.reports` | Dashboard and financial reports |
| `erpaccount.config` | COA, bank accounts, tax, periods |

---

## Routes

| Area | Web Prefix |
|------|------------|
| Dashboard (home) | `/erpaccount/executive-dashboard` |
| Chart of Accounts | `/erpaccount/chart-of-accounts` |
| Journal Vouchers | `/erpaccount/journal-vouchers` |
| Voucher Register | `/erpaccount/voucher-register` |
| Cash & Bank | `/erpaccount/cash-bank-vouchers` |
| Bank Reconciliation | `/erpaccount/bank-reconciliation` |
| Reports Center (83 reports) | `/erpaccount/reports` |
| Single report + PDF/Excel | `/erpaccount/reports/{slug}` |
| Party Ledger | `/erpaccount/party-ledger` |

API base: `/api/erpaccount/v1/...`

---

## Delivery Checklist

1. Run migrations (includes `is_voided` on journal masters).
2. Seed COA from `helpers/chart_of_accounts.sql`.
3. Create at least one **open** financial period covering today's date.
4. Map bank accounts under **Bank & Cash Accounts**.
5. Assign `erpaccount.*` permissions to finance users.
6. Post a test JV, confirm it appears in **Voucher Register** and **Trial Balance**.
7. Close a past period and verify new postings on that date are blocked.

---

## Features

- Double-entry journal, receipt, and payment vouchers
- Closed financial period enforcement on all posting dates
- Voucher register with search, detail view, and void (excluded from reports)
- **83 Garments Reports** — Core financial, VAT/TAX, Purchase, Export, LC, Production, Payroll, Inventory, Bank/Cash, Management (PDF + Excel on every report)
- Trial Balance, P&L, Balance Sheet, Party Ledger, Executive Dashboard
- Bank reconciliation worksheet
- Sidebar permissions wired to `erpaccount.*` keys
- Manual Inventory, Manual Payroll, and Commercial LC Tracker bridge screens listed in the sidebar

---

# Module Tutorials

> [!IMPORTANT]
> এই তিনটি মডিউল ব্যবহার করার আগে অবশ্যই **Chart of Accounts (COA)** সঠিকভাবে সেটআপ করতে হবে। নামের মধ্যে নির্দিষ্ট কীওয়ার্ড না থাকলে সিস্টেম **"Required ledger accounts were not found"** এরর দেখাবে।

---

## Module 1 — Payroll Management

### ধারণা (Overview)

Payroll module টি কর্মীদের বেতন প্রক্রিয়াকরণের সম্পূর্ণ জার্নাল এন্ট্রি স্বয়ংক্রিয়ভাবে তৈরি করে। Gross Salary থেকে PF কাটা, Advance Salary সমন্বয়, এবং নেট বেতন প্রদান — সবই এই মডিউলের মাধ্যমে দ্বৈত-এন্ট্রি (Double Entry) নিয়মে পোস্ট হয়।

---

### 1.1 Data Entry System — কিভাবে ডাটা এন্ট্রি করবেন

**ধাপ ১ — Payroll Entry ফর্ম খুলুন**

Sidebar থেকে **Payroll Management** মেনুতে ক্লিক করুন। নতুন একটি Entry ফর্ম দেখা যাবে।

**ধাপ ২ — নিচের Fields গুলো পূরণ করুন:**

| Field নাম | কী দিতে হবে | উদাহরণ |
|-----------|-------------|---------|
| **Payroll Period / Date** | যে মাসের বেতন দিচ্ছেন সেই তারিখ | `2024-06-30` |
| **Employee / Department** | কর্মী বা বিভাগের নাম সিলেক্ট করুন | `Sewing Section` |
| **Gross Salary** | মোট বেতনের পরিমাণ (টাকায়) | `150000.00` |
| **PF Deduction (%)** | Provident Fund কর্তনের হার | `10` (অর্থাৎ ১০%) |
| **Advance Salary Adjustment** | আগে নেওয়া অগ্রিম থাকলে পরিমাণ লিখুন | `5000.00` |
| **Payment Method** | Cash / Bank ট্রান্সফার | `Bank Transfer` |
| **Narration / Remarks** | সংক্ষিপ্ত বিবরণ (Optional) | `June 2024 Salary` |

> [!NOTE]
> **Advance Salary Adjustment** ফিল্ডটি শুধুমাত্র তখনই পূরণ করুন যখন কর্মী আগে থেকে অগ্রিম বেতন নিয়ে থাকেন। না থাকলে `0` দিন বা ফাঁকা রাখুন।

**ধাপ ৩ — Post বাটনে ক্লিক করুন**

সিস্টেম স্বয়ংক্রিয়ভাবে নিচের জার্নাল এন্ট্রি তৈরি করবে:

```
Dr.  Salary Expense A/C          →  Gross Salary পরিমাণ
    Cr.  PF Liability A/C         →  PF Deduction পরিমাণ
    Cr.  Advance Salary A/C       →  Advance Adjustment পরিমাণ
    Cr.  Salary Payable A/C       →  নেট বেতন (বাকি অংশ)
```

---

### 1.2 Mandatory Chart of Accounts — বাধ্যতামূলক অ্যাকাউন্ট সেটআপ

> [!IMPORTANT]
> সিস্টেম `LOWER(account_name) LIKE '%keyword%'` দিয়ে অ্যাকাউন্ট খোঁজে। নিচের টেবিলে দেওয়া কীওয়ার্ড অ্যাকাউন্টের নামে না থাকলে **Payroll Post হবে না** এবং Validation Error দেখাবে।

| Account Purpose (কাজের বিবরণ) | Mandatory Keyword/Pattern (নামে যা থাকা বাধ্যতামূলক) | System Account Type (সিস্টেম ক্যাটাগরি) | Production Example Name (বাস্তব উদাহরণ) |
|-------------------------------|------------------------------------------------------|------------------------------------------|------------------------------------------|
| কর্মীর মোট বেতন খরচ | `salary expense` **অথবা** `wages expense` **অথবা** `payroll expense` | **Expense** | `Salary Expense - Factory` |
| Provident Fund দায় | `provident fund liability` **অথবা** `pf liability` **অথবা** `provident fund payable` | **Liability** | `PF Liability - Staff` |
| অগ্রিম বেতনের সম্পদ | `advance salary` **অথবা** `salary advance` **অথবা** `advance salary asset` | **Asset** | `Advance Salary - Workers` |
| নেট বেতন পরিশোধযোগ্য দায় | `salary payable` **অথবা** `wages payable` **অথবা** `payable` | **Liability** | `Salary Payable - June 2024` |

> [!TIP]
> COA সেটআপ করতে যান: **Chart of Accounts → New Account**। উপরের টেবিলের `Production Example Name` হুবহু ব্যবহার করতে পারেন অথবা নিজের নাম দিন — শুধু নিশ্চিত করুন `Mandatory Keyword` নামের মধ্যে আছে।

---

## Module 2 — Inventory Financial Journal

### ধারণা (Overview)

Inventory module টি কাঁচামাল ক্রয়, উৎপাদনে ইস্যু, এবং মজুদ সমন্বয়ের জার্নাল এন্ট্রি পরিচালনা করে। **Transaction Type** সিলেক্ট করার উপর ভিত্তি করে সিস্টেম ভিন্ন ভিন্ন অ্যাকাউন্ট ব্যবহার করে।

---

### 2.1 Data Entry System — কিভাবে ডাটা এন্ট্রি করবেন

**ধাপ ১ — Inventory Journal Entry ফর্ম খুলুন**

Sidebar থেকে **Inventory Financial Journal** মেনুতে ক্লিক করুন।

**ধাপ ২ — Transaction Type (Trans Type) সিলেক্ট করুন**

এটি সবচেয়ে গুরুত্বপূর্ণ ধাপ। Trans Type অনুযায়ী পরবর্তী ফিল্ডগুলো পরিবর্তন হবে:

| Trans Type | কখন ব্যবহার করবেন |
|------------|------------------|
| `Material Purchase` | সরবরাহকারীর কাছ থেকে কাঁচামাল কিনলে |
| `Issue to Production` | গুদাম থেকে উৎপাদনে কাঁচামাল দিলে |
| `Inventory Adjustment` | মজুদ ক্ষতি, পার্থক্য বা সমন্বয় করলে |

**ধাপ ৩ — নিচের Fields গুলো পূরণ করুন:**

| Field নাম | কী দিতে হবে | উদাহরণ |
|-----------|-------------|---------|
| **Transaction Date** | লেনদেনের তারিখ | `2024-06-15` |
| **Trans Type** | লেনদেনের ধরন (উপরের টেবিল দেখুন) | `Material Purchase` |
| **Item / Material Name** | কাঁচামালের নাম | `Grey Fabric` |
| **Quantity** | পরিমাণ | `500` (মিটার/কেজি) |
| **Unit Price** | প্রতি একক মূল্য | `120.00` |
| **Total Amount** | স্বয়ংক্রিয় হিসাব হবে | `60,000.00` |
| **Account Selector** | কোন অ্যাকাউন্টে পোস্ট হবে (Auto-resolve হয়) | সিস্টেম নিজে বেছে নেয় |
| **Supplier (Purchase-এ)** | সরবরাহকারীর নাম | `Textile BD Ltd.` |
| **Reference / Challan No.** | চালান বা রেফারেন্স নম্বর | `INV-2024-0615` |
| **Narration** | সংক্ষিপ্ত বিবরণ | `Grey Fabric Purchase` |

> [!NOTE]
> **Account Selector** ফিল্ডটি সাধারণত সিস্টেম নিজেই Trans Type অনুযায়ী পূরণ করে। তবে যদি একাধিক অ্যাকাউন্ট পাওয়া যায়, তাহলে ড্রপডাউন থেকে সঠিকটি বেছে নিন।

**ধাপ ৪ — Post বাটনে ক্লিক করুন**

Trans Type অনুযায়ী নিচের জার্নাল এন্ট্রি তৈরি হবে:

**Material Purchase:**
```
Dr.  Raw Material Inventory A/C   →  মোট ক্রয় মূল্য
    Cr.  Supplier Payable A/C     →  মোট ক্রয় মূল্য
```

**Issue to Production:**
```
Dr.  WIP (Work In Progress) A/C   →  ইস্যু করা পরিমাণের মূল্য
    Cr.  Raw Material Inventory    →  ইস্যু করা পরিমাণের মূল্য
```

**Inventory Adjustment / Loss:**
```
Dr.  Inventory Adjustment/Loss A/C →  ক্ষতির পরিমাণ
    Cr.  Raw Material Inventory     →  ক্ষতির পরিমাণ
```

---

### 2.2 Mandatory Chart of Accounts — বাধ্যতামূলক অ্যাকাউন্ট সেটআপ

> [!IMPORTANT]
> প্রতিটি Trans Type-এর জন্য আলাদা অ্যাকাউন্ট প্রয়োজন। নিচের তিনটি টেবিলে প্রতিটি ধরনের COA প্রয়োজনীয়তা দেওয়া আছে।

#### Trans Type: `Material Purchase`

| Account Purpose (কাজের বিবরণ) | Mandatory Keyword/Pattern | System Account Type | Production Example Name |
|-------------------------------|--------------------------|---------------------|------------------------|
| কাঁচামালের মজুদ সম্পদ | `raw material` **অথবা** `inventory` | **Asset** | `Raw Material Inventory - Fabric` |
| সরবরাহকারীকে দেয় দায় | `supplier` **অথবা** `payable` | **Liability** | `Supplier Payable - Local` |

#### Trans Type: `Issue to Production`

| Account Purpose (কাজের বিবরণ) | Mandatory Keyword/Pattern | System Account Type | Production Example Name |
|-------------------------------|--------------------------|---------------------|------------------------|
| চলমান উৎপাদন সম্পদ | `wip` **অথবা** `work in progress` | **Asset** | `WIP - Cutting Section` |
| কাঁচামালের মজুদ সম্পদ | `raw material` **অথবা** `inventory` | **Asset** | `Raw Material Inventory - Fabric` |

#### Trans Type: `Inventory Adjustment / Loss`

| Account Purpose (কাজের বিবরণ) | Mandatory Keyword/Pattern | System Account Type | Production Example Name |
|-------------------------------|--------------------------|---------------------|------------------------|
| মজুদ ক্ষতি / সমন্বয় খরচ | `adjustment` **অথবা** `inventory loss` | **Expense** | `Inventory Adjustment Loss` |
| কাঁচামালের মজুদ সম্পদ | `raw material` **অথবা** `inventory` | **Asset** | `Raw Material Inventory - Fabric` |

> [!TIP]
> একটি অ্যাকাউন্ট একাধিক Trans Type-এ ব্যবহার হতে পারে। যেমন `Raw Material Inventory - Fabric` তিনটি Trans Type-েই কাজ করবে — শুধু একবার তৈরি করলেই হবে।

---

## Module 3 — Commercial Bank Tracker (Bank Reconciliation)

### ধারণা (Overview)

Bank Reconciliation module টি ব্যাংক স্টেটমেন্টের প্রতিটি এন্ট্রিকে সিস্টেমের ভেতরে পোস্ট করা Journal/Voucher এর সাথে মিলিয়ে দেখে। **Cleared** এবং **Uncleared** লেনদেন আলাদা করে ব্যাংক ব্যালেন্স নিশ্চিত করা এই মডিউলের মূল কাজ।

---

### 3.1 Data Entry System — কিভাবে ডাটা এন্ট্রি করবেন

**ধাপ ১ — Bank Reconciliation মেনু খুলুন**

Sidebar থেকে **Bank Reconciliation** মেনুতে ক্লিক করুন।

**ধাপ ২ — Bank Account সিলেক্ট করুন**

ড্রপডাউন থেকে যে ব্যাংক অ্যাকাউন্টের Reconciliation করতে চান তা বেছে নিন।

> [!NOTE]
> ড্রপডাউনে ব্যাংক অ্যাকাউন্ট না দেখালে প্রথমে **Bank & Cash Accounts** মেনু থেকে অ্যাকাউন্ট তৈরি করুন এবং COA-তে সঠিক Bank Ledger ম্যাপ করুন।

**ধাপ ৩ — Statement Period নির্ধারণ করুন**

| Field নাম | কী দিতে হবে | উদাহরণ |
|-----------|-------------|---------|
| **Statement From Date** | ব্যাংক স্টেটমেন্টের শুরুর তারিখ | `2024-06-01` |
| **Statement To Date** | ব্যাংক স্টেটমেন্টের শেষ তারিখ | `2024-06-30` |
| **Closing Balance (Bank Statement)** | ব্যাংক স্টেটমেন্টে দেখানো শেষ ব্যালেন্স | `1,250,000.00` |

**ধাপ ৪ — Bank Statement আপলোড বা ম্যানুয়াল এন্ট্রি**

প্রতিটি ব্যাংক ট্রানজেকশনের জন্য নিচের তথ্য দিন:

| Field নাম | কী দিতে হবে | উদাহরণ |
|-----------|-------------|---------|
| **Transaction Date** | ব্যাংক স্টেটমেন্টের তারিখ | `2024-06-05` |
| **Description / Narration** | ব্যাংক কর্তৃক প্রদত্ত বিবরণ | `NEFT-Supplier Payment` |
| **Debit / Credit Amount** | টাকার পরিমাণ এবং দিকনির্দেশনা | Debit: `50,000.00` |
| **Reference Code** | ব্যাংক রেফারেন্স নম্বর (Cheque/NEFT/RTGS) | `CHQ-001234` |

**ধাপ ৫ — System Matching (Auto/Manual)**

সিস্টেম প্রতিটি ব্যাংক এন্ট্রিকে Internal Journal Master-এর সাথে মেলানোর চেষ্টা করে:

```
Matching Logic (সিস্টেমের মিলানো পদ্ধতি):
  1. Amount Match      →  ব্যাংক ডেবিট/ক্রেডিট = Journal-এর পরিমাণ
  2. Date Proximity    →  তারিখ ±3 দিনের মধ্যে
  3. Reference Match   →  Cheque নম্বর বা Reference Code মিলে গেলে
```

- **Auto-Matched** এন্ট্রিগুলো সবুজ রঙে দেখাবে → **Cleared** হিসেবে চিহ্নিত হবে।
- **Unmatched** এন্ট্রিগুলো লাল রঙে দেখাবে → ম্যানুয়ালি জার্নালের সাথে লিংক করুন।

**ধাপ ৬ — Reconcile বাটনে ক্লিক করুন**

সিস্টেম Reconciliation Summary দেখাবে:

```
Bank Statement Closing Balance      :  1,250,000.00
Less: Outstanding Deposits           :    (75,000.00)
Add:  Outstanding Payments           :     35,000.00
                                      ─────────────
Adjusted Book Balance               :  1,210,000.00
System Book Balance (COA)           :  1,210,000.00
                                      ─────────────
Difference (should be ZERO ✓)       :          0.00
```

> [!IMPORTANT]
> **Difference যদি শূন্য না হয়** তাহলে Reconciliation সম্পন্ন হয়নি। Uncleared লেনদেনগুলো পুনরায় যাচাই করুন বা Missing Journal Entry পোস্ট করুন।

---

### 3.2 Mandatory Chart of Accounts — বাধ্যতামূলক অ্যাকাউন্ট সেটআপ

> [!IMPORTANT]
> Bank Reconciliation-এর জন্য COA-তে সঠিক Bank Ledger থাকতে হবে এবং Bank & Cash Accounts-এ সেটি ম্যাপ করা থাকতে হবে। নামে `bank` কীওয়ার্ড না থাকলে সিস্টেম অ্যাকাউন্টটিকে ব্যাংক লেজার হিসেবে চিনবে না।

| Account Purpose (কাজের বিবরণ) | Mandatory Keyword/Pattern | System Account Type | Production Example Name |
|-------------------------------|--------------------------|---------------------|------------------------|
| ব্যাংক ব্যালেন্স সম্পদ | `bank` (অথবা ব্যাংকের নাম যা সিস্টেমে ম্যাপ করা আছে) | **Asset** (Bank/Cash Sub-type) | `Dutch Bangla Bank - Current A/C` |
| নগদ অর্থ সম্পদ (যদি প্রযোজ্য) | `cash` | **Asset** (Bank/Cash Sub-type) | `Cash In Hand` |

#### Journal Cleared State — কিভাবে সিস্টেম Matching করে

সিস্টেম একটি Journal Master-কে `cleared` চিহ্নিত করে যখন:

| শর্ত | বিস্তারিত |
|------|-----------|
| **Amount Match** | Bank Statement-এর পরিমাণ = Journal Entry-র পরিমাণ (পার্থক্য ০.০১-এর বেশি হলে match হবে না) |
| **Date Match** | Journal পোস্টিং তারিখ এবং Bank Value Date-এর পার্থক্য ৩ কার্যদিবসের মধ্যে |
| **Reference Code Match** | Journal-এর Cheque/Reference নম্বর Bank Statement-এর Description-এ থাকলে priority match |
| **Manual Override** | উপরের কোনো শর্ত না মিললে accountant ম্যানুয়ালি দুটো এন্ট্রি লিংক করতে পারবেন |

> [!NOTE]
> একটি Journal Entry শুধুমাত্র একটি Bank Statement Line-এর সাথে লিংক হতে পারে। একই Journal দুইবার Cleared করা যাবে না — সিস্টেম Duplicate Clearing Error দেখাবে।

---

# Troubleshooting & Developer Seed

## সাধারণ সমস্যা ও সমাধান

| Error Message | কারণ | সমাধান |
|---------------|------|--------|
| `Required ledger accounts were not found` | COA-তে প্রয়োজনীয় কীওয়ার্ড সহ অ্যাকাউন্ট নেই | উপরের Module-নির্দিষ্ট COA টেবিল দেখে অ্যাকাউন্ট তৈরি করুন |
| `No open financial period found` | পোস্টিং তারিখে কোনো Open Period নেই | **Financial Periods** মেনু থেকে Period তৈরি করুন |
| `Bank account not mapped` | Bank & Cash Accounts-এ ব্যাংক ম্যাপ করা নেই | **Bank & Cash Accounts** মেনু থেকে অ্যাকাউন্ট যোগ করুন |
| `Duplicate clearing detected` | একই Journal দুইবার Cleared করার চেষ্টা | Reconciliation ওয়ার্কশিটে আগের Cleared এন্ট্রি খুঁজুন |
| `Debit/Credit not balanced` | Journal-এর Dr ≠ Cr | পরিমাণ পুনরায় যাচাই করুন |

---

## Developer Seed — সম্পূর্ণ COA Laravel Seeder

নিচের Seeder টি সব তিনটি Module-এর বাধ্যতামূলক Chart of Accounts রেকর্ড তৈরি করবে। Development বা Test environment-এ দ্রুত সেটআপের জন্য ব্যবহার করুন।

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ErpAccountMandatoryCOASeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $accounts = [

            // ─────────────────────────────────────────────────
            // MODULE 1: PAYROLL — বাধ্যতামূলক অ্যাকাউন্ট
            // ─────────────────────────────────────────────────
            [
                'account_code' => 'EXP-5001',
                'account_name' => 'Salary Expense - Factory',
                // Keyword match: 'salary expense' ✓
                'account_type' => 'Expense',
                'description'  => 'Payroll: Gross salary debit posting',
                'is_active'    => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'account_code' => 'LIA-2001',
                'account_name' => 'PF Liability - Staff',
                // Keyword match: 'pf liability' ✓
                'account_type' => 'Liability',
                'description'  => 'Payroll: Provident fund deduction credit posting',
                'is_active'    => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'account_code' => 'AST-1101',
                'account_name' => 'Advance Salary - Workers',
                // Keyword match: 'advance salary' ✓
                'account_type' => 'Asset',
                'description'  => 'Payroll: Salary advance adjustment credit posting',
                'is_active'    => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'account_code' => 'LIA-2002',
                'account_name' => 'Salary Payable - Current Month',
                // Keyword match: 'salary payable' ✓
                'account_type' => 'Liability',
                'description'  => 'Payroll: Net salary payable credit posting',
                'is_active'    => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],

            // ─────────────────────────────────────────────────
            // MODULE 2: INVENTORY — বাধ্যতামূলক অ্যাকাউন্ট
            // ─────────────────────────────────────────────────
            [
                'account_code' => 'AST-1201',
                'account_name' => 'Raw Material Inventory - Fabric',
                // Keyword match: 'raw material' ✓ | Used in: Purchase, Issue, Adjustment
                'account_type' => 'Asset',
                'description'  => 'Inventory: Raw material stock asset account',
                'is_active'    => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'account_code' => 'LIA-2101',
                'account_name' => 'Supplier Payable - Local',
                // Keyword match: 'supplier' ✓ | Used in: Material Purchase
                'account_type' => 'Liability',
                'description'  => 'Inventory: Amount payable to local suppliers',
                'is_active'    => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'account_code' => 'AST-1301',
                'account_name' => 'WIP - Cutting Section',
                // Keyword match: 'wip' ✓ | Used in: Issue to Production
                'account_type' => 'Asset',
                'description'  => 'Inventory: Work in progress for cutting section',
                'is_active'    => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'account_code' => 'EXP-5101',
                'account_name' => 'Inventory Adjustment Loss',
                // Keyword match: 'adjustment' ✓ | Used in: Inventory Adjustment/Loss
                'account_type' => 'Expense',
                'description'  => 'Inventory: Loss or variance on physical stock count',
                'is_active'    => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],

            // ─────────────────────────────────────────────────
            // MODULE 3: BANK TRACKER — বাধ্যতামূলক অ্যাকাউন্ট
            // ─────────────────────────────────────────────────
            [
                'account_code' => 'AST-1001',
                'account_name' => 'Dutch Bangla Bank - Current A/C',
                // Keyword match: 'bank' ✓ | Must also be mapped in bank_accounts table
                'account_type' => 'Asset',
                'description'  => 'Bank: DBBL current account for reconciliation',
                'is_active'    => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'account_code' => 'AST-1002',
                'account_name' => 'Cash In Hand',
                // Keyword match: 'cash' ✓
                'account_type' => 'Asset',
                'description'  => 'Bank: Petty cash and physical cash balance',
                'is_active'    => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
        ];

        // Upsert to avoid duplicate key errors on re-seed
        foreach ($accounts as $account) {
            DB::table('chart_of_accounts')->updateOrInsert(
                ['account_code' => $account['account_code']],
                $account
            );
        }

        $this->command->info('✓ All mandatory COA records seeded for Payroll, Inventory, and Bank Tracker modules.');
    }
}
```

**Seeder চালানোর নির্দেশনা:**

```bash
# শুধু এই Seeder চালাতে
php artisan db:seed --class=ErpAccountMandatoryCOASeeder

# অথবা DatabaseSeeder-এ যোগ করে সব একসাথে চালান
# database/seeders/DatabaseSeeder.php এ যোগ করুন:
# $this->call(ErpAccountMandatoryCOASeeder::class);
```

> [!IMPORTANT]
> Seeder চালানোর পরে প্রতিটি Module-এর Validation পরীক্ষা করুন। **Chart of Accounts** মেনুতে গিয়ে উপরের অ্যাকাউন্টগুলো দেখা যাচ্ছে কিনা নিশ্চিত করুন। ব্যাংক অ্যাকাউন্টের ক্ষেত্রে `Dutch Bangla Bank - Current A/C` টি **Bank & Cash Accounts** মেনু থেকে আলাদাভাবে ম্যাপ করতে হবে।

---

> **Package:** `mestiaque/erpaccount` | **Support:** mrm.khan.1298@gmail.com
