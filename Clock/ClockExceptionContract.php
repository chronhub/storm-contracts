<?php

declare(strict_types=1);

namespace Storm\Contracts\Clock;

use Throwable;

/**
 * Any failure of the `Clock` port.
 *
 * In practice, a datetime the clock rejects as unparseable or as outside the canonical shape:
 * RFC 3339, UTC, microsecond precision.
 *
 * @see Clock
 */
interface ClockExceptionContract extends Throwable {}
