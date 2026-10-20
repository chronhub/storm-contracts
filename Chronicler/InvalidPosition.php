<?php

declare(strict_types=1);

namespace Storm\Contracts\Chronicler;

/**
 * An invalid store position, or a comparison across two different `Position` implementations.
 *
 * @see Position
 */
interface InvalidPosition extends ChroniclerExceptionContract {}
