<?php

declare(strict_types=1);

namespace Storm\Contracts\Chronicler;

use Storm\Contracts\Aggregate\AggregateRepository;

/**
 * The contracted storage failure: an operation against the backing store failed for an
 * infrastructural reason such as connectivity loss, a failed statement, or a corrupt row; the
 * driver's exception is kept as the previous cause. Not recoverable by the caller's logic; it
 * bubbles to the global handler.
 *
 * @see \Storm\Chronicler\Directory\StreamHeadStore
 * @see AggregateRepository::retrieve()
 */
interface StorageFailure extends ChroniclerExceptionContract {}
