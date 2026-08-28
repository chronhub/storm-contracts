<?php

declare(strict_types=1);

namespace Storm\Contracts\Clock;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;

/**
 * The clock port: Storm's single source of the current time, guaranteed as a canonical PointInTime.
 * Third-party ClockInterface implementations that do not follow this contract are not Storm's
 * responsibility.
 *
 * The Contracts layer depends on nothing, so this port names its failure only through the
 * contracted ClockExceptionContract type, never the implementation package's exception; a typed
 * docblock annotation would itself re-create that dependency edge. The concrete hierarchy that
 * implements it lives in the Clock module.
 *
 * @template-covariant T of DateTimeImmutable
 *
 * @see ClockExceptionContract
 */
interface Clock extends ClockInterface
{
    /**
     * Returns the current time as a PointInTime, UTC, microsecond precision.
     *
     * @return T
     *
     * @throws ClockExceptionContract when the inner clock yields a value that is not a canonical Storm datetime
     */
    public function now(): DateTimeImmutable;
}
