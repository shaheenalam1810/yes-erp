<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Company;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToCompany
{
    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
