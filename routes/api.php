<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->middleware(['auth:sanctum'])->group(function (): void {
    require __DIR__.'/api/v1.php';
});
