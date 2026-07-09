<?php

declare(strict_types=1);

namespace Modules\Inventory\Models;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Inventory\Database\Factories\StockMovementFactory;

final class StockMovement extends Model
{
    use BelongsToCompany;

    /** @use HasFactory<StockMovementFactory> */
    use HasFactory;

    protected $fillable = [
        'company_id',
        'warehouse_id',
        'item_id',
        'movement_type',
        'quantity',
        'unit_cost',
        'source_type',
        'source_id',
        'moved_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'unit_cost' => 'decimal:4',
            'moved_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Warehouse, $this>
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * @return BelongsTo<Item, $this>
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * @return Factory<StockMovement>
     */
    protected static function newFactory(): Factory
    {
        return StockMovementFactory::new();
    }
}
