<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('documents')->name('documents.')->group(function (): void {
    Route::get('/', static fn () => response()->json([
        'module' => 'documents',
        'resources' => ['files', 'folders', 'templates', 'approvals', 'retention_policies'],
    ]))->name('index');
});
