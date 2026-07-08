<?php

declare(strict_types=1);

namespace App\Support\Tenancy;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Warehouse;

final class CurrentCompany
{
    private ?Company $company = null;

    private ?Branch $branch = null;

    private ?Warehouse $warehouse = null;

    public function setCompany(Company $company): void
    {
        $this->company = $company;
    }

    public function company(): ?Company
    {
        return $this->company;
    }

    public function setBranch(?Branch $branch): void
    {
        $this->branch = $branch;
    }

    public function branch(): ?Branch
    {
        return $this->branch;
    }

    public function setWarehouse(?Warehouse $warehouse): void
    {
        $this->warehouse = $warehouse;
    }

    public function warehouse(): ?Warehouse
    {
        return $this->warehouse;
    }

    public function companyId(): ?int
    {
        return $this->company?->getKey();
    }
}
