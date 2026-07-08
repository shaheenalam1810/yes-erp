<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('hr')->name('hr.')->group(function (): void {
    Route::get('/', static fn () => response()->json([
        'module' => 'hr',
        'resources' => ['employees', 'attendance', 'leave', 'payroll'],
    ]))->name('index');
});
