<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('integrations')->name('integrations.')->group(function (): void {
    Route::get('/', static fn () => response()->json([
        'module' => 'integrations',
        'resources' => ['couriers', 'ecommerce_channels', 'webhooks', 'sync_jobs'],
    ]))->name('index');
});
