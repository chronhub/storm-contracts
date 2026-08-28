<?php

declare(strict_types=1);

namespace Storm\Contracts\Chronicler;

/**
 * The contracted position failure: a value that is not a valid store position, or a comparison
 * across two different Position implementations.
 *
 * @see Position
 */
interface InvalidPosition extends ChroniclerExceptionContract {}
