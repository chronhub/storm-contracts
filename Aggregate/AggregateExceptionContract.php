<?php

declare(strict_types=1);

namespace Storm\Contracts\Aggregate;

use Throwable;

/**
 * Root of every failure the Aggregate domain lets cross its boundary.
 *
 * Catch it to handle any aggregate failure, or catch a child for a precise reaction, such as
 * `InvalidAggregateIdentity` at an I/O boundary.
 *
 * @see CorruptAggregateHistory
 * @see InvalidAggregateIdentity
 * @see InvalidSnapshotState
 * @see AggregateTypeMismatch
 * @see EventAggregateIdMismatch
 */
interface AggregateExceptionContract extends Throwable {}
