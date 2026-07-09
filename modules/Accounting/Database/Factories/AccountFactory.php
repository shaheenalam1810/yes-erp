<?php

declare(strict_types=1);

namespace Modules\Accounting\Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Accounting\Models\Account;

/**
 * @extends Factory<Account>
 */
final class AccountFactory extends Factory
{
    protected $model = Account::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'code' => strtoupper($this->faker->unique()->bothify('ACC-####')),
            'name' => $this->faker->words(2, true),
            'type' => $this->faker->randomElement(['asset', 'liability', 'equity', 'revenue', 'expense']),
            'parent_id' => null,
            'is_posting' => true,
            'is_active' => true,
        ];
    }
}
