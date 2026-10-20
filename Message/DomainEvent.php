<?php

declare(strict_types=1);

namespace Storm\Contracts\Message;

/**
 * A domain event: a serializable fact recorded by an aggregate.
 *
 * `toPayload()` and `fromPayload()` are its store and wire codec. `aggregateId()`, read from the
 * payload, makes the event self-contained: a projection, a reaction or a cross-service consumer
 * gets the id from the event itself, not only from the envelope header.
 *
 * The repository enforces it at `store()`: an event whose `aggregateId()` contradicts the identity
 * it is stored under is refused with `EventAggregateIdMismatch`, so the stream and the payload never
 * name different aggregates.
 */
interface DomainEvent extends SerializablePayload
{
    /**
     * The id of the aggregate this event belongs to, as the store writes it.
     *
     * It names a stream and keys a row, so it is a plain string with no parse and no identity
     * class. A consumer needing the typed identity builds it at its own boundary, where the concrete
     * class is known and a malformed value can fail.
     */
    public function aggregateId(): string;
}
