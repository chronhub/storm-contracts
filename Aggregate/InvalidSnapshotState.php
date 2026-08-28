<?php

declare(strict_types=1);

namespace Storm\Contracts\Aggregate;

/**
 * The contracted failure of a snapshot state restoration: the stored state bag is syntactically
 * readable but semantically unusable, a business field missing, mistyped, or out of range.
 *
 * A snapshot is only a cache; the events stay authoritative. An aggregate's `restoreState()` that
 * validates its fields strictly throws an implementation of THIS contract for a corrupt cache row,
 * and the snapshot repository converts it into a cache miss; the row is discarded and the history
 * fully replayed, instead of failing a read whose authoritative history is sound. Anything else
 * thrown out of `restoreState()` is a code bug and stays loud; this contract is the line between
 * the two.
 *
 * @see SnapshotableAggregateRoot
 */
interface InvalidSnapshotState extends AggregateExceptionContract {}
