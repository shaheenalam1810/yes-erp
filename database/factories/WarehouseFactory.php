<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Warehouse>
 */
final class WarehouseFactory extends Factory
{
    protected $model = Warehouse::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'branch_id' => Branch::factory(),
            'name' => $this->faker->city().' Warehouse',
            'code' => strtoupper($this->faker->unique()->lexify('WH-???')),
            'is_default' => false,
            'is_active' => true,
        ];
    }
}
