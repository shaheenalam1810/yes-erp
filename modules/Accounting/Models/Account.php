<?php

declare(strict_types=1);

namespace Modules\Accounting\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Accounting\Database\Factories\AccountFactory;

final class Account extends Model
{
    use BelongsToCompany;

    /** @use HasFactory<AccountFactory> */
    use HasFactory;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'type',
        'parent_id',
        'is_posting',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_posting' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Account, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<Account, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * @return Factory<Account>
     */
    protected static function newFactory(): Factory
    {
        return AccountFactory::new();
    }
}
