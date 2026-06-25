<?php

$reportsCatalog = file_exists(__DIR__ . '/reports.php')
    ? require __DIR__ . '/reports.php'
    : ['categories' => []];

$reportCategoryMenus = [];
foreach ($reportsCatalog['categories'] as $category) {
    $reportItems = [];
    foreach ($category['reports'] as $report) {
        $reportItems[] = [
            'title'      => $report['title'],
            'icon'       => 'fa-solid fa-file-export',
            'route'      => '/erpaccount/reports/' . $report['slug'],
            'icon_color' => 'text-warning',
            'permission' => 'garments_reports',
        ];
    }

    $reportCategoryMenus[] = [
        'title'      => $category['title'],
        'icon'       => $category['icon'] ?? 'fa-solid fa-chart-bar',
        'icon_color' => 'text-info',
        'permission' => 'garments_reports',
        'children'   => $reportItems,
    ];
}

return [
    [
        'group_title' => '',
        [
            'title'       => 'Accounts Center',
            'icon'        => 'fa-solid fa-file-invoice-dollar',
            'icon_color'  => 'text-primary',
            'permission'  => '',
            'order'       => 11,
            'children'    => [
                [
                    'title'      => 'Dashboard',
                    'icon'       => 'fa-solid fa-arrow-right',
                    'route'      => '/erpaccount/executive-dashboard',
                    'icon_color' => 'text-warning',
                    'permission' => 'executive_financial_dashboard',
                ],
                [
                    'title'      => 'Accounts Tutorial',
                    'icon'       => 'fa-solid fa-book-open-reader',
                    'route'      => '/erpaccount/accounts-tutorial',
                    'icon_color' => 'text-warning',
                    'permission' => 'executive_financial_dashboard',
                ],
                [
                    'title'      => 'Configurations',
                    'icon'       => 'fa-solid fa-screwdriver-wrench',
                    'icon_color' => 'text-info',
                    'permission' => '',
                    'children'   => [
                        [
                            'title'      => 'Chart of Accounts',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/erpaccount/chart-of-accounts',
                            'icon_color' => 'text-warning',
                            'permission' => 'chart_of_accounts',
                        ],
                        [
                            'title'      => 'Bank & Cash Accounts',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/erpaccount/bank-accounts',
                            'icon_color' => 'text-warning',
                            'permission' => 'bank_and_cash_accounts',
                        ],
                        [
                            'title'      => 'Cost Centers',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/erpaccount/cost-centers',
                            'icon_color' => 'text-warning',
                            'permission' => 'cost_centers',
                        ],
                        [
                            'title'      => 'Tax Rates & Financial Periods',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/erpaccount/tax-rates',
                            'icon_color' => 'text-warning',
                            'permission' => 'tax_rates_financial_periods',
                        ],
                        [
                            'title'      => 'Creditors (Supplier)',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/erpaccount/creditors',
                            'icon_color' => 'text-warning',
                            'permission' => 'creditors',
                        ],
                        [
                            'title'      => 'Debitors (Buyer)',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/erpaccount/debitors',
                            'icon_color' => 'text-warning',
                            'permission' => 'debitors',
                        ],
                    ],
                ],
                [
                    'title'      => 'Daily Operations',
                    'icon'       => 'fa-solid fa-book-open',
                    'icon_color' => 'text-info',
                    'permission' => 'daily_operations',
                    'children'   => [
                        [
                            'title'      => 'Universal Voucher Entry',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/erpaccount/journal-vouchers',
                            'icon_color' => 'text-warning',
                            'permission' => 'universal_voucher_entry',
                        ],
                        [
                            'title'      => 'Cash & Bank Vouchers',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/erpaccount/cash-bank-vouchers',
                            'icon_color' => 'text-warning',
                            'permission' => 'cash_bank_vouchers',
                        ],
                        [
                            'title'      => 'Voucher Register',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/erpaccount/voucher-register',
                            'icon_color' => 'text-warning',
                            'permission' => 'voucher_register',
                        ],
                        [
                            'title'      => 'Bank Reconciliation',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/erpaccount/bank-reconciliation',
                            'icon_color' => 'text-warning',
                            'permission' => 'bank_reconciliation',
                        ],
                        [
                            'title'      => 'IOU / Staff Advance',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/erpaccount/iou',
                            'icon_color' => 'text-warning',
                            'permission' => 'iou',
                        ],
                    ],
                ],
                [
                    'title'      => 'Executive Analytics',
                    'icon'       => 'fa-solid fa-chart-line',
                    'icon_color' => 'text-info',
                    'permission' => 'executive_analytics',
                    'children'   => [
                        [
                            'title'      => 'Style Profitability Monitor',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/erpaccount/style-profitability',
                            'icon_color' => 'text-warning',
                            'permission' => 'style_profitability_monitor',
                        ],
                        [
                            'title'      => 'Party Ledger History',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/erpaccount/party-ledger',
                            'icon_color' => 'text-warning',
                            'permission' => 'party_ledger_history',
                        ],
                    ],
                ],
                [
                    'title'      => 'Integration Bridges',
                    'icon'       => 'fa-solid fa-link',
                    'icon_color' => 'text-info',
                    'permission' => 'integration_bridges',
                    'children'   => [
                        [
                            'title'      => 'Manual Inventory',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/erpaccount/manual-inventory',
                            'icon_color' => 'text-warning',
                            'permission' => 'manual_inventory',
                        ],
                        [
                            'title'      => 'Manual Payroll',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/erpaccount/manual-payroll',
                            'icon_color' => 'text-warning',
                            'permission' => 'manual_payroll',
                        ],
                        [
                            'title'      => 'Commercial LC Tracker',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/erpaccount/commercial-lc-tracker',
                            'icon_color' => 'text-warning',
                            'permission' => 'commercial_lc_tracker',
                        ],
                    ],
                ],
                [
                    'title'      => 'Reports Center (All)',
                    'icon'       => 'fa-solid fa-table',
                    'icon_color' => 'text-success',
                    'permission' => 'reports_center',
                    'route'      => '/erpaccount/reports',
                ],
                [
                    'title'      => 'Garments Reports',
                    'icon'       => 'fa-solid fa-folder-open',
                    'icon_color' => 'text-danger',
                    'permission' => 'garments_reports',
                    'children'   => $reportCategoryMenus,
                ],
            ],
        ],
    ],
];


