<?php

return [
    'modules' => [

        'Accounts Center' => [
            'accounts_center' => [
                'label'       => 'Accounts Center',
                'permissions' => [
                    'list' => 'List',
                    'all'  => 'All',
                ],
            ],
            'configurations' => [
                'label'       => 'Configurations',
                'permissions' => [
                    'list' => 'List',
                    'all'  => 'All',
                ],
            ],
            'daily_operations' => [
                'label'       => 'Daily Operations',
                'permissions' => [
                    'list' => 'List',
                    'all'  => 'All',
                ],
            ],
            'executive_analytics' => [
                'label'       => 'Executive Analytics',
                'permissions' => [
                    'list' => 'List',
                    'all'  => 'All',
                ],
            ],
            'integration_bridges' => [
                'label'       => 'Integration Bridges',
                'permissions' => [
                    'list' => 'List',
                    'all'  => 'All',
                ],
            ],

            'chart_of_accounts' => [
                'label'       => 'Chart of Accounts',
                'permissions' => [
                    'list'   => 'List',
                    'add'    => 'Create',
                    'edit'   => 'Edit',
                    'view'   => 'View',
                    'delete' => 'Delete',
                    'all'    => 'All',
                ],
            ],
            'bank_and_cash_accounts' => [
                'label'       => 'Bank and Cash Accounts',
                'permissions' => [
                    'list'    => 'List',
                    'add'     => 'Create',
                    'edit'    => 'Edit',
                    'view'    => 'View',
                    'delete'  => 'Delete',
                    'payment' => 'Payment',
                    'all'     => 'All',
                ],
            ],
            'cost_centers' => [
                'label'       => 'Cost Centers',
                'permissions' => [
                    'list'   => 'List',
                    'add'    => 'Create',
                    'edit'   => 'Edit',
                    'view'   => 'View',
                    'delete' => 'Delete',
                    'all'    => 'All',
                ],
            ],
            'tax_rates_financial_periods' => [
                'label'       => 'Tax Rates & Financial Periods',
                'permissions' => [
                    'list'   => 'List',
                    'add'    => 'Create',
                    'edit'   => 'Edit',
                    'view'   => 'View',
                    'delete' => 'Delete',
                    'all'    => 'All',
                ],
            ],
                        'universal_voucher_entry' => [
                'label'       => 'Universal Voucher Entry',
                'permissions' => [
                    'list'   => 'List',
                    'add'    => 'Create',
                    'edit'   => 'Edit',
                    'view'   => 'View',
                    'delete' => 'Delete',
                    'post'   => 'Post',
                    'all'    => 'All',
                ],
            ],
            'cash_bank_vouchers' => [
                'label'       => 'Cash & Bank Vouchers',
                'permissions' => [
                    'list'   => 'List',
                    'add'    => 'Create',
                    'edit'   => 'Edit',
                    'view'   => 'View',
                    'delete' => 'Delete',
                    'post'   => 'Post',
                    'all'    => 'All',
                ],
            ],
            'voucher_register' => [
                'label'       => 'Voucher Register',
                'permissions' => [
                    'list'   => 'List',
                    'view'   => 'View',
                    'print'  => 'Print',
                    'export' => 'Export',
                    'all'    => 'All',
                ],
            ],
            'bank_reconciliation' => [
                'label'       => 'Bank Reconciliation',
                'permissions' => [
                    'list'   => 'List',
                    'add'    => 'Create',
                    'edit'   => 'Edit',
                    'view'   => 'View',
                    'delete' => 'Delete',
                    'match'  => 'Match',
                    'all'    => 'All',
                ],
            ],
            'manual_inventory' => [
                'label'       => 'Manual Inventory',
                'permissions' => [
                    'list' => 'List',
                    'add'  => 'Create',
                    'view' => 'View',
                    'post' => 'Post',
                    'all'  => 'All',
                ],
            ],
            'manual_payroll' => [
                'label'       => 'Manual Payroll',
                'permissions' => [
                    'list' => 'List',
                    'add'  => 'Create',
                    'view' => 'View',
                    'post' => 'Post',
                    'all'  => 'All',
                ],
            ],
            'commercial_lc_tracker' => [
                'label'       => 'Commercial LC Tracker',
                'permissions' => [
                    'list' => 'List',
                    'add'  => 'Create',
                    'view' => 'View',
                    'sync' => 'Sync',
                    'all'  => 'All',
                ],
            ],
            'executive_financial_dashboard' => [
                'label'       => 'C-Suite Financial Dashboard',
                'permissions' => [
                    'list'   => 'List',
                    'view'   => 'View',
                    'export' => 'Export',
                    'all'    => 'All',
                ],
            ],
            'style_profitability_monitor' => [
                'label'       => 'Style Profitability Monitor',
                'permissions' => [
                    'list'   => 'List',
                    'view'   => 'View',
                    'export' => 'Export',
                    'all'    => 'All',
                ],
            ],
            'party_ledger_history' => [
                'label'       => 'Party Ledger History',
                'permissions' => [
                    'list'   => 'List',
                    'view'   => 'View',
                    'print'  => 'Print',
                    'export' => 'Export',
                    'all'    => 'All',
                ],
            ],
            'reports_center' => [
                'label'       => 'Reports Center (All)',
                'permissions' => [
                    'list'   => 'List',
                    'view'   => 'View',
                    'print'  => 'Print',
                    'export' => 'Export',
                    'all'    => 'All',
                ],
            ],
            'garments_reports' => [
                'label'       => 'Garments Reports',
                'permissions' => [
                    'list'   => 'List',
                    'view'   => 'View',
                    'print'  => 'Print',
                    'export' => 'Export',
                    'all'    => 'All',
                ],
            ],
        ],

    ]
];
