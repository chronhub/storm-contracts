<?php

declare(strict_types=1);

namespace Storm\Contracts\Aggregate;

use Throwable;

/**
 * The root of the Aggregate domain's contracted exception surface; every aggregate failure the
 * domain lets cross its boundary is an AggregateExceptionContract. Catch it as the outer net, or
 * catch a specific child for a precise reaction, such as InvalidAggregateIdentity at an I/O
 * boundary.
 *
 * @see CorruptAggregateHistory
 * @see InvalidAggregateIdentity
 * @see InvalidSnapshotState
 * @see AggregateTypeMismatch
 * @see EventAggregateIdMismatch
 */
interface AggregateExceptionContract extends Throwable {}
