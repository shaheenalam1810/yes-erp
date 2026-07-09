<?php

declare(strict_types=1);

namespace Modules\Inventory\Database\Factories;

use App\Models\Company;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Inventory\Models\Item;
use Modules\Inventory\Models\StockMovement;

/**
 * @extends Factory<StockMovement>
 */
final class StockMovementFactory extends Factory
{
    protected $model = StockMovement::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = $this->faker->randomFloat(4, 1, 100);

        return [
            'company_id' => Company::factory(),
            'warehouse_id' => Warehouse::factory(),
            'item_id' => Item::factory(),
            'movement_type' => $this->faker->randomElement(['purchase', 'sale', 'transfer', 'adjustment']),
            'quantity' => $quantity,
            'unit_cost' => $this->faker->randomFloat(4, 1, 500),
            'source_type' => null,
            'source_id' => null,
            'moved_at' => now(),
        ];
    }
}
