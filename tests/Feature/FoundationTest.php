<?php

declare(strict_types=1);

use function Pest\Laravel\get;

it('loads the application health endpoint', function (): void {
    get('/up')->assertOk();
});
