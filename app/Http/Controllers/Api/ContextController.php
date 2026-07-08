<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ContextController extends Controller
{
    public function __invoke(Request $request, CurrentCompany $currentCompany): JsonResponse
    {
        return response()->json([
            'user' => $request->user(),
            'company' => $currentCompany->company(),
            'branch' => $currentCompany->branch(),
            'warehouse' => $currentCompany->warehouse(),
            'modules' => config('erp.modules'),
        ]);
    }
}
