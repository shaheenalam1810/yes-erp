<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
final class CompanyFactory extends Factory
{
    protected $model = Company::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->company(),
            'legal_name' => $this->faker->company().' '.$this->faker->companySuffix(),
            'tax_number' => $this->faker->unique()->numerify('TAX-########'),
            'email' => $this->faker->unique()->companyEmail(),
            'phone' => $this->faker->phoneNumber(),
            'base_currency' => 'USD',
            'timezone' => 'UTC',
            'is_active' => true,
        ];
    }
}
