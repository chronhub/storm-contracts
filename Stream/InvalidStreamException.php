<?php

declare(strict_types=1);

namespace Storm\Contracts\Stream;

use Throwable;

/**
 * A stream name, category or qualifier is not valid.
 */
interface InvalidStreamException extends Throwable {}
