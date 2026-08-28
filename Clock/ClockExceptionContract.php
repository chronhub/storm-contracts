<?php

declare(strict_types=1);

namespace Storm\Contracts\Clock;

use Throwable;

/**
 * The contracted failure of the Clock port: any clock error, in practice a datetime the clock
 * rejects as unparseable or not the canonical UTC RFC 3339 shape with microsecond precision.
 *
 * @see Clock
 */
interface ClockExceptionContract extends Throwable {}
