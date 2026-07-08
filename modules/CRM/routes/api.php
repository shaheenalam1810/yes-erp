<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('crm')->name('crm.')->group(function (): void {
    Route::get('/', static fn () => response()->json([
        'module' => 'crm',
        'resources' => ['leads', 'customers', 'opportunities', 'activities'],
    ]))->name('index');
});
