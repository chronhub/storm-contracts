<?php

declare(strict_types=1);

namespace Storm\Contracts\Message;

use Throwable;

/**
 * Declares that a delivery can succeed once a temporary condition clears.
 *
 * Consumers keep ownership of delay and retry limits; this marker never requests unlimited retries.
 */
interface RetryableDelivery extends Throwable {}
