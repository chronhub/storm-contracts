<?php

declare(strict_types=1);

namespace Storm\Contracts\Stream;

use Throwable;

/**
 * The contracted stream-name failure: a stream name, category, or qualifier is not valid.
 */
interface InvalidStreamException extends Throwable {}
