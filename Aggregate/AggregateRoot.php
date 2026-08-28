<?php

declare(strict_types=1);

namespace Storm\Contracts\Aggregate;

use Generator;
use Storm\Contracts\Message\DomainEvent;

/**
 * An event-sourced aggregate root.
 *
 * It records pure domain events, applies them to its own state, and can be
 * reconstituted from its event history. It knows nothing of the transport, the
 * Message envelope, or persistence; the aggregate repository handles all of that.
 *
 * @template-covariant TId of AggregateIdentity
 */
interface AggregateRoot
{
    /**
     * Rebuild an aggregate from its event history.
     *
     * The generator's return value is the aggregate's version after the replay, provided by the
     * repository. Returns null when the history is empty, meaning the aggregate does not exist.
     *
     * Corruption detection is part of the contract, and it is EXACT: an implementation must reject
     * a history whose claimed version differs, in either direction, from the number of events it
     * applied; a claim below that count means a tampered or over-applied history, a claim above it
     * means events are missing. It must likewise reject an empty history claiming a positive
     * version. Silently accepting any of these would resurrect an aggregate in a state its own
     * stream contradicts. A replayed event with no apply method is a wiring bug, on which
     * implementations fail fast with their own LogicException-class error; such bugs are
     * deliberately not part of the contracted surface.
     *
     * Consequence of the exactness: a stream must stay contiguous from its first event. Any
     * operation that removes events from the middle of a stream, a partial prune or a hard delete,
     * makes its aggregate permanently unloadable as corrupt; that refusal is designed, and
     * crypto-shredding exists precisely so forgetting data never has to delete a row.
     *
     * Repository-facing plumbing, not domain surface: reconstitution belongs to the aggregate
     * repository and to test code replaying a history; domain code creates aggregates through
     * their business factories.
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
     * Return and clear the events recorded since the last release, in recording order, oldest
     * first. The order is load-bearing: the repository stamps each event's version from its
     * position, so an out-of-order release would mis-version the stream.
     *
     * The release is DESTRUCTIVE and single-shot per decision: the buffer is gone while the
     * version stays advanced, so an instance whose store threw after this call is spent; a second
     * release returns [] and a retried store becomes a silent no-op. Never retry the instance;
     * reload and re-decide, which is what the framework's own redelivery does.
     *
     * Repository-facing plumbing, not domain surface: the sole legitimate callers are the
     * aggregate repository and test code exercising the ES lifecycle; any other call site silently
     * discards the recorded events.
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
     * The current version, the number of events applied. A usable aggregate is always at least 1:
     * it is created through a factory that records its first event or reconstituted from a
     * non-empty history. Internally, an implementation may pass through transient 0 during
     * construction or replay, but that state is never exposed: an empty history reconstitutes to
     * null, not a version-0 aggregate.
     *
     * @return positive-int
     */
    public function version(): int;
}
