<?php

declare(strict_types=1);

namespace Storm\Contracts\Clock;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;

/**
 * Storm's single source of the current time, always a canonical `PointInTime`.
 *
 * A third-party `ClockInterface` implementation that does not follow this contract is outside
 * Storm's responsibility.
 *
 * The Contracts layer depends on nothing, so this port names its failure only through
 * `ClockExceptionContract`, never through the implementation package's exception: even a docblock
 * annotation would recreate that dependency. The concrete hierarchy lives in the Clock module.
 *
 * @template-covariant T of DateTimeImmutable
 *
 * @see ClockExceptionContract
 */
interface Clock extends ClockInterface
{
    /**
     * The current time as a `PointInTime`, in UTC with microsecond precision.
     *
     * @return T
     *
     * @throws ClockExceptionContract when the inner clock yields a value that is not a canonical Storm
     *                                datetime
     */
    public function now(): DateTimeImmutable;
}
