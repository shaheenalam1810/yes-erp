<?php

declare(strict_types=1);

namespace Modules\Inventory\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class Item extends Model
{
    use BelongsToCompany;
    use HasFactory;

    protected $fillable = [
        'company_id',
        'sku',
        'name',
        'type',
        'unit',
        'purchase_price',
        'sale_price',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:4',
            'sale_price' => 'decimal:4',
            'is_active' => 'boolean',
        ];
    }
}
