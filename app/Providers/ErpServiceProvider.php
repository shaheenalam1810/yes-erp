<?php

declare(strict_types=1);

namespace App\Providers;

use App\Http\Middleware\EnsureCompanyContext;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

final class ErpServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(CurrentCompany::class);
    }

    public function boot(Router $router): void
    {
        $router->aliasMiddleware('erp.company', EnsureCompanyContext::class);
    }
}
