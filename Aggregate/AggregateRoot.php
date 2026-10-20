<?php

declare(strict_types=1);

namespace Storm\Contracts\Aggregate;

use Generator;
use Storm\Contracts\Message\DomainEvent;

/**
 * An event-sourced aggregate root.
 *
 * It records pure domain events, applies them to its own state, and is reconstituted from its
 * event history. The transport, the `Message` envelope and persistence belong to the aggregate
 * repository.
 *
 * @template-covariant TId of AggregateIdentity
 */
interface AggregateRoot
{
    /**
     * Rebuild an aggregate from its event history, or null when the history is empty.
     *
     * The generator's return value is the version after the replay, provided by the repository.
     * An implementation rejects, as corruption:
     *
     * - A claimed version below the number of applied events, a tampered or over-applied history
     * - A claimed version above it, a history with missing events
     * - An empty history claiming a positive version
     *
     * Accepting any of these would resurrect an aggregate in a state its own stream contradicts. A
     * replayed event with no apply method is a wiring bug: implementations fail fast with their own
     * `LogicException`-class error, outside the contracted failures.
     *
     * The check being exact, a stream stays contiguous from its first event. A partial prune or a
     * hard delete in the middle of a stream leaves its aggregate unloadable as corrupt, and that
     * refusal is intended: crypto-shredding lets data be forgotten without deleting a row.
     *
     * Only the aggregate repository and test code replaying a history call this; domain code
     * creates aggregates through their business factories.
     *
     * @param  Generator<int, DomainEvent, null, int>  $events  the return value from `getReturn()`
     *                                                          is the history's trailing version
     *                                                          header, typed and load-bearing, the
     *                                                          exact corruption check's counterpart
     *
     * @throws CorruptAggregateHistory when the replayed history contradicts its own version header
     */
    public static function reconstitute(AggregateIdentity $aggregateId, Generator $events): ?static;

    /**
     * Return and clear the events recorded since the last release, oldest first.
     *
     * The repository stamps each event's version from its position, so an out-of-order release
     * would mis-version the stream.
     *
     * The release empties the buffer while the version stays advanced, once per decision. An
     * instance whose store failed after this call is spent: a second release returns `[]` and a
     * retried store silently persists nothing. Reload and decide again instead, as the framework's
     * redelivery does.
     *
     * Only the aggregate repository and test code exercising the event-sourcing lifecycle call
     * this; any other call site discards the recorded events.
     *
     * @return list<DomainEvent>
     */
    public function releaseEvents(): array;

    /**
     * The aggregate's typed identity.
     *
     * @return TId
     */
    public function identity(): AggregateIdentity;

    /**
     * The current version, the number of events applied.
     *
     * A usable aggregate is at least at version 1, created by a factory that records its first
     * event or reconstituted from a non-empty history. An implementation may pass through 0 during
     * construction or replay, but never exposes it: an empty history reconstitutes to null.
     *
     * @return positive-int
     */
    public function version(): int;
}
