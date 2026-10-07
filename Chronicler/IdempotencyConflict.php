<?php

declare(strict_types=1);

namespace Storm\Contracts\Chronicler;

/**
 * A live request key is already bound to another fingerprint.
 */
interface IdempotencyConflict extends ChroniclerExceptionContract {}
