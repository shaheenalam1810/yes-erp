<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('core')->name('core.')->group(function (): void {
    Route::get('/', static fn () => response()->json([
        'module' => 'core',
        'resources' => ['companies', 'branches', 'warehouses', 'settings'],
    ]))->name('index');
});
