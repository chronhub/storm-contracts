<?php

declare(strict_types=1);

namespace Storm\Contracts\Aggregate;

/**
 * A stored snapshot state is readable but unusable.
 *
 * A business field is missing, mistyped or out of range.
 *
 * A snapshot is a cache and the events stay authoritative. An aggregate whose `restoreState()`
 * validates its fields throws this contract for a corrupt cache row; the snapshot repository
 * discards the row and replays the full history, so a sound history still loads and the next sweep
 * regenerates the snapshot. Anything else thrown out of `restoreState()` is a code bug and
 * propagates: this contract is what tells the two apart.
 *
 * @see SnapshotableAggregateRoot
 */
interface InvalidSnapshotState extends AggregateExceptionContract {}
