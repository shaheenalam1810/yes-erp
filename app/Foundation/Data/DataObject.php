<?php

declare(strict_types=1);

namespace App\Foundation\Data;

abstract readonly class DataObject
{
    /**
     * @return array<string, mixed>
     */
    abstract public function toArray(): array;
}
