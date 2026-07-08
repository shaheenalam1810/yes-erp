<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('pos')->name('pos.')->group(function (): void {
    Route::get('/', static fn () => response()->json([
        'module' => 'pos',
        'resources' => ['terminals', 'shifts', 'sales', 'returns', 'receipts'],
    ]))->name('index');
});
