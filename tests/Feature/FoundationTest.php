<?php

declare(strict_types=1);

it('loads the application health endpoint', function (): void {
    $this->get('/up')->assertOk();
});
