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
            'permission' => 'erpaccount.reports',
        ];
    }

    $reportCategoryMenus[] = [
        'title'      => $category['title'],
        'icon'       => $category['icon'] ?? 'fa-solid fa-chart-bar',
        'icon_color' => 'text-info',
        'permission' => 'erpaccount.reports',
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
                            'permission' => '',
                        ],
                        [
                            'title'      => 'Bank & Cash Accounts',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/erpaccount/bank-accounts',
                            'icon_color' => 'text-warning',
                            'permission' => '',
                        ],
                        [
                            'title'      => 'Tax Rates & Financial Periods',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/erpaccount/tax-rates',
                            'icon_color' => 'text-warning',
                            'permission' => '',
                        ],
                    ],
                ],
                [
                    'title'      => 'Daily Operations',
                    'icon'       => 'fa-solid fa-book-open',
                    'icon_color' => 'text-info',
                    'permission' => '',
                    'children'   => [
                        [
                            'title'      => 'Universal Voucher Entry',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/erpaccount/journal-vouchers',
                            'icon_color' => 'text-warning',
                            'permission' => '',
                        ],
                        [
                            'title'      => 'Cash & Bank Vouchers',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/erpaccount/cash-bank-vouchers',
                            'icon_color' => 'text-warning',
                            'permission' => '',
                        ],
                        [
                            'title'      => 'Voucher Register',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/erpaccount/voucher-register',
                            'icon_color' => 'text-warning',
                            'permission' => '',
                        ],
                        [
                            'title'      => 'Bank Reconciliation',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/erpaccount/bank-reconciliation',
                            'icon_color' => 'text-warning',
                            'permission' => '',
                        ],
                    ],
                ],
                [
                    'title'      => 'Executive Analytics',
                    'icon'       => 'fa-solid fa-chart-line',
                    'icon_color' => 'text-info',
                    'permission' => '',
                    'children'   => [
                        [
                            'title'      => 'C-Suite Financial Dashboard',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/erpaccount/executive-dashboard',
                            'icon_color' => 'text-warning',
                            'permission' => '',
                        ],
                        [
                            'title'      => 'Style Profitability Monitor',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/erpaccount/style-profitability',
                            'icon_color' => 'text-warning',
                            'permission' => '',
                        ],
                        [
                            'title'      => 'Party Ledger History',
                            'icon'       => 'fa-solid fa-arrow-right',
                            'route'      => '/erpaccount/party-ledger',
                            'icon_color' => 'text-warning',
                            'permission' => '',
                        ],
                    ],
                ],
                [
                    'title'      => 'Reports Center (All)',
                    'icon'       => 'fa-solid fa-table',
                    'icon_color' => 'text-success',
                    'permission' => '',
                    'route'      => '/erpaccount/reports',
                ],
                [
                    'title'      => 'Garments Reports',
                    'icon'       => 'fa-solid fa-folder-open',
                    'icon_color' => 'text-danger',
                    'permission' => '',
                    'children'   => $reportCategoryMenus,
                ],
            ],
        ],
    ],
];



