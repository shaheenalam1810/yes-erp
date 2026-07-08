<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ContextController;
use Illuminate\Support\Facades\Route;

Route::get('/context', ContextController::class)->name('context');

Route::middleware(['erp.company'])->group(function (): void {
    require base_path('modules/Core/routes/api.php');
    require base_path('modules/Accounting/routes/api.php');
    require base_path('modules/CRM/routes/api.php');
    require base_path('modules/Inventory/routes/api.php');
    require base_path('modules/POS/routes/api.php');
    require base_path('modules/Reports/routes/api.php');
    require base_path('modules/HR/routes/api.php');
    require base_path('modules/Integrations/routes/api.php');
    require base_path('modules/Documents/routes/api.php');
});
