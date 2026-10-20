<?php

declare(strict_types=1);

namespace Storm\Contracts\Aggregate;

use Storm\Contracts\Chronicler\ConcurrencyException;
use Storm\Contracts\Chronicler\StorageFailure;
use Storm\Contracts\Chronicler\UnknownEventType;
use Storm\Contracts\Serializer\SubjectForgotten;
use Storm\Contracts\Stream\InvalidStreamException;

/**
 * Persists and reconstitutes aggregate roots through the event store.
 *
 * One instance is bound to a single aggregate type and stream category. It works with the
 * per-aggregate version under optimistic concurrency; the global read-your-writes `Position`
 * belongs to the outbox and the projections and is not exposed here.
 *
 * `TId` and `T` are the bound identity and aggregate types. `AggregateRepositoryManager` binds a
 * repository to a concrete aggregate type, so `retrieve()` returns `?T`: callers skip the
 * `instanceof` narrowing and only check for null. PHPStan also refuses a foreign identity at the
 * call site, the static counterpart of the runtime id-class guard every implementation keeps on
 * `store()` and `retrieve()`.
 *
 * @template TId of AggregateIdentity
 * @template T of AggregateRoot<TId>
 */
interface AggregateRepository
{
    /**
     * Persist the aggregate's newly recorded events under optimistic concurrency.
     *
     * When a competing writer moved the stream past the loaded version, the conflict is a
     * `ConcurrencyException`. Its `RetryableConcurrencyConflict` marker says whether reloading and
     * deciding again can win; an unmarked conflict is terminal, since the decision may already be
     * applied, and is never retried blindly.
     *
     * A call is single-shot per decision. After a failed `store()` the instance is spent: its event
     * buffer is drained and its version stays advanced, so a second `store()` silently persists
     * nothing. Recovery reloads and decides again, which the framework's redelivery does on a fresh
     * `retrieve()`.
     *
     * @param  T  $aggregate
     *
     * @throws ConcurrencyException on a concurrency conflict
     * @throws AggregateTypeMismatch when the aggregate's identity is not this repository's id type
     * @throws EventAggregateIdMismatch when a released event's own `aggregateId()` contradicts the
     *                                  identity being stored, a domain bug refused at the write
     *                                  before it becomes an incoherent fact
     * @throws InvalidStreamException when the aggregate id does not form a valid stream qualifier
     * @throws SubjectForgotten when a personal payload names a tombstoned subject: no key can be
     *                          issued for it, so the write refuses instead of storing in clear
     * @throws StorageFailure on a write-side infrastructure failure, cause preserved: a driver error,
     *                        or an event the codec refuses to write, such as a personal event
     *                        without its subject key or a payload that will not encode. The append is
     *                        translated here after the write chain's own transient retry gave up,
     *                        so that retry still matches the raw driver failure, as in `retrieve()`
     */
    public function store(AggregateRoot $aggregate): void;

    /**
     * Reconstitute the aggregate from its event history, or null when no stream exists.
     *
     * @param  TId  $id
     * @return T|null
     *
     * @throws AggregateTypeMismatch when the given identity is not this repository's id type
     * @throws InvalidStreamException when the aggregate id does not form a valid stream qualifier
     * @throws UnknownEventType when the stored event type is unknown
     * @throws StorageFailure on a read-side infrastructure failure, cause preserved: a driver error,
     *                        a corrupt stored row, a stream row that is not a domain event, or the
     *                        read path of an acceleration cache such as the snapshot store, which
     *                        fails as this error rather than as a silent miss
     * @throws CorruptAggregateHistory when a reconstituted history contradicts its own version headers, in
     *                                 either direction
     */
    public function retrieve(AggregateIdentity $id): ?AggregateRoot;
}
