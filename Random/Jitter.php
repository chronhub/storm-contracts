<?php

declare(strict_types=1);

namespace Storm\Contracts\Random;

use Random\RandomException;

/**
 * The draw a backoff spreads its retries with, so the range a caller asks for and the end it gets
 * stay observable, where an inline call to the random source would leave them to chance.
 */
interface Jitter
{
    /**
     * Draw an integer between the two bounds, both included, `$min` never above `$max`.
     *
     * @throws RandomException when no source of randomness is available
     */
    public function between(int $min, int $max): int;
}
