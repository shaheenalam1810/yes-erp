<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('accounting')->name('accounting.')->group(function (): void {
    Route::get('/', static fn () => response()->json([
        'module' => 'accounting',
        'resources' => ['accounts', 'journal_entries', 'payments', 'taxes', 'financial_statements'],
    ]))->name('index');
});
