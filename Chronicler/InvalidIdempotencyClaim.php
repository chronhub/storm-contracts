<?php

declare(strict_types=1);

namespace Storm\Contracts\Chronicler;

/**
 * An invalid claim or transaction boundary cannot establish a durable request identity.
 */
interface InvalidIdempotencyClaim extends ChroniclerExceptionContract {}
