<?php

declare(strict_types=1);

namespace Storm\Contracts\Aggregate;

/**
 * The contracted corruption failure of the replay path: a reconstituted history contradicts its own
 * version headers. The rule is exact continuity: versions run contiguously from 1, or from the
 * snapshot version plus one on a tail; every record carries its header, and the trailing header
 * accounts for every applied event exactly. EITHER direction of disagreement is corruption: fewer
 * events than the header claims means the history is truncated and a decision on the partial state
 * could pass the store's CAS; more events than the header claims means the header lags its stream.
 * Not recoverable by the caller's logic; it bubbles to the global handler as a data-integrity alarm.
 *
 * @see AggregateRoot::reconstitute()
 */
interface CorruptAggregateHistory extends AggregateExceptionContract {}
