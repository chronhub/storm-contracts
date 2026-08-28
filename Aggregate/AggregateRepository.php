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
 * The repository's currency is the per-aggregate version under optimistic concurrency; the global
 * read-your-writes Position is an outbox or projection concern and is not surfaced here. One
 * repository instance is bound to a single aggregate type and stream category.
 *
 * Generic over the bound identity type TId and aggregate type T: AggregateRepositoryManager binds
 * a repository to a concrete aggregate type, so its retrieve() is typed as ?T and callers skip the
 * instanceof narrowing, only null-checking for the not-found case. PHPStan further refuses a
 * foreign identity at the call site, the static mirror of the runtime id-class guard every
 * implementation keeps on both store() and retrieve().
 *
 * @template TId of AggregateIdentity
 * @template T of AggregateRoot<TId>
 */
interface AggregateRepository
{
    /**
     * Persist the aggregate's newly recorded events under optimistic concurrency.
     *
     * On a concurrency conflict, when the stream moved past the aggregate's loaded version because a
     * competing writer won, the implementation throws the contracted ConcurrencyException. Whether
     * the conflict is worth retrying is carried by its RetryableConcurrencyConflict marker: a marked
     * conflict is won by reload-and-redecide; an unmarked one is terminal, since the decision may
     * already be applied and must not be blind-retried.
     *
     * Single-shot per decision. A store() that THREW leaves the instance spent: its event buffer
     * was drained and its version stays advanced, so retrying store() on the same instance is a
     * silent no-op that persists nothing. Recovery is reload-and-re-decide, where the framework's
     * own redelivery re-runs the handler on a fresh retrieve, never a second store() of the instance.
     *
     * @param  T  $aggregate
     *
     * @throws ConcurrencyException on a concurrency conflict
     * @throws AggregateTypeMismatch when the aggregate's identity is not this repository's id type
     * @throws EventAggregateIdMismatch when a released event's own aggregateId() contradicts the
     *                                  identity being stored, a domain bug surfaced at the write and
     *                                  not persisted as an incoherent fact
     * @throws InvalidStreamException when the aggregate id does not form a valid stream qualifier
     * @throws SubjectForgotten when a personal payload names a tombstoned subject: no key can be
     *                          issued for it, so the write refuses rather than storing in clear
     * @throws StorageFailure on a write-side infrastructure failure, cause preserved: the
     *                        transactional append, translated at this port AFTER the write chain's
     *                        own transient retry gave up, so the raw driver failure stays below the
     *                        retry where it is pattern-matched; the mirror of the retrieve()
     *                        translation
     */
    public function store(AggregateRoot $aggregate): void;

    /**
     * Reconstitute the aggregate from its event history or null when no stream exists.
     *
     * Reads and deserializes the stream. Infra failures of the read, such as a driver error, a
     * corrupt stored row, or a stream row that is not a domain event, cross this port as the
     * contracted StorageFailure with cause preserved. The replay's own corruption,
     * CorruptAggregateHistory, and an unknown stored event type surface distinctly.
     *
     * @param  TId  $id
     * @return T|null
     *
     * @throws AggregateTypeMismatch when the given identity is not this repository's id type
     * @throws InvalidStreamException when the aggregate id does not form a valid stream qualifier
     * @throws UnknownEventType when the stored event type is unknown
     * @throws StorageFailure on a read-side infrastructure failure: the store read, its
     *                        deserialization, a stream row that is not a domain event, or the read
     *                        path of an implementation's acceleration cache such as the snapshot
     *                        store; a failing cache crosses as this failure, never as a silent miss
     * @throws CorruptAggregateHistory when a reconstituted history contradicts its own version headers, in either direction
     */
    public function retrieve(AggregateIdentity $id): ?AggregateRoot;
}
