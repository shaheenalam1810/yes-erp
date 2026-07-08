<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'core.manage',
            'accounting.manage',
            'crm.manage',
            'inventory.manage',
            'pos.manage',
            'reports.view',
            'hr.manage',
            'integrations.manage',
            'documents.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $adminRole = Role::findOrCreate('company-admin', 'web');
        $adminRole->syncPermissions($permissions);

        $company = Company::query()->firstOrCreate(
            ['name' => 'Demo Company'],
            ['base_currency' => config('erp.default_currency'), 'timezone' => 'UTC']
        );

        $branch = Branch::query()->firstOrCreate(
            ['company_id' => $company->id, 'code' => 'MAIN'],
            ['name' => 'Main Branch', 'is_active' => true]
        );

        Warehouse::query()->firstOrCreate(
            ['company_id' => $company->id, 'code' => 'MAIN'],
            ['branch_id' => $branch->id, 'name' => 'Main Warehouse', 'is_default' => true]
        );

        $user = User::query()->firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'ERP Admin', 'password' => Hash::make('password'), 'is_active' => true]
        );

        $user->assignRole($adminRole);
        $user->companies()->syncWithoutDetaching([
            $company->id => [
                'default_branch_id' => $branch->id,
                'is_owner' => true,
                'is_active' => true,
            ],
        ]);
    }
}
