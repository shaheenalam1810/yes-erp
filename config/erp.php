<?php

declare(strict_types=1);

return [
    'default_currency' => env('ERP_DEFAULT_CURRENCY', 'USD'),
    'fiscal_year_start' => env('ERP_FISCAL_YEAR_START', '01-01'),
    'strict_tenancy' => (bool) env('ERP_STRICT_TENANCY', true),
    'audit_enabled' => (bool) env('ERP_AUDIT_ENABLED', true),

    'modules' => [
        'core' => true,
        'accounting' => true,
        'crm' => true,
        'inventory' => true,
        'pos' => true,
        'reports' => true,
        'hr' => true,
        'courier' => true,
        'ecommerce' => true,
        'documents' => true,
    ],

    'tenant_header' => 'X-Company-Id',
    'branch_header' => 'X-Branch-Id',
    'warehouse_header' => 'X-Warehouse-Id',
];
