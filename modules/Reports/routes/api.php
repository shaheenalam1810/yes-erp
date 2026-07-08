<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('reports')->name('reports.')->group(function (): void {
    Route::get('/', static fn () => response()->json([
        'module' => 'reports',
        'resources' => ['financial', 'inventory', 'sales', 'hr', 'audit'],
    ]))->name('index');
});
