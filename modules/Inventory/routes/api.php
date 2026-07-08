<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('inventory')->name('inventory.')->group(function (): void {
    Route::get('/', static fn () => response()->json([
        'module' => 'inventory',
        'resources' => ['items', 'stock_movements', 'transfers', 'adjustments', 'purchase_receipts'],
    ]))->name('index');
});
