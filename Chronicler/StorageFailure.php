<?php

declare(strict_types=1);

namespace Storm\Contracts\Chronicler;

use Storm\Contracts\Aggregate\AggregateRepository;

/**
 * An operation against the backing store failed for an infrastructural reason, such as:
 *
 * - Connectivity loss
 * - A failed statement
 * - A corrupt row
 *
 * The driver's exception is kept as the previous cause. The caller's logic cannot recover from it,
 * so it bubbles to the global handler.
 *
 * @see \Storm\Chronicler\Directory\StreamHeadStore
 * @see AggregateRepository::retrieve()
 */
interface StorageFailure extends ChroniclerExceptionContract {}
