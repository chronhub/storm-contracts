<?php

declare(strict_types=1);

namespace Storm\Contracts\Aggregate;

/**
 * A reconstituted history contradicts its own version headers.
 *
 * The rule is exact continuity:
 *
 * - Versions run contiguously from 1, or from the snapshot version plus one on a tail
 * - Every record carries its version header
 * - The trailing header accounts for every applied event exactly
 *
 * A disagreement in either direction is corruption. Fewer events than the header claims is a
 * truncated history, and a decision on that partial state could still pass the store's CAS; more
 * events than it claims means the header lags its stream. The caller's logic cannot recover from
 * it, so it bubbles to the global handler as a data-integrity alarm.
 *
 * @see AggregateRoot::reconstitute()
 */
interface CorruptAggregateHistory extends AggregateExceptionContract {}
