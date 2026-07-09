<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Company;
use App\Support\Tenancy\CurrentCompany;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class EnsureCompanyContext
{
    public function __construct(private CurrentCompany $currentCompany) {}

    public function handle(Request $request, Closure $next): Response
    {
        $companyId = $request->header((string) config('erp.tenant_header'));

        if (! $companyId) {
            abort(422, 'Company context header is required.');
        }

        $company = Company::query()
            ->whereKey($companyId)
            ->where('is_active', true)
            ->firstOrFail();

        $this->currentCompany->setCompany($company);

        return $next($request);
    }
}
