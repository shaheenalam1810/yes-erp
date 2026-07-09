<?php

declare(strict_types=1);

namespace Modules\Inventory\Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Inventory\Models\Item;

/**
 * @extends Factory<Item>
 */
final class ItemFactory extends Factory
{
    protected $model = Item::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $purchasePrice = $this->faker->randomFloat(2, 1, 500);

        return [
            'company_id' => Company::factory(),
            'sku' => strtoupper($this->faker->unique()->bothify('SKU-#####')),
            'name' => $this->faker->words(3, true),
            'type' => $this->faker->randomElement(['goods', 'service']),
            'unit' => $this->faker->randomElement(['pcs', 'box', 'kg', 'ltr']),
            'purchase_price' => $purchasePrice,
            'sale_price' => $purchasePrice * 1.25,
            'is_active' => true,
        ];
    }
}
