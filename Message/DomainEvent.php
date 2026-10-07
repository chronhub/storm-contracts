<?php

declare(strict_types=1);

namespace Storm\Contracts\Message;

/**
 * A domain event: a serializable fact recorded by an aggregate.
 *
 * SerializablePayload is the store and wire codec, through toPayload and fromPayload. aggregateId
 * exposes the id of the aggregate the event belongs to, read from the payload, so the event is
 * self-contained: a projection, reaction, or cross-service consumer gets the id from the event
 * itself, not only from the envelope header.
 *
 * The self-containment is enforced, not advisory: the repository verifies it at store(), rejecting
 * an event whose aggregateId() contradicts the identity it is being stored under, with
 * EventAggregateIdMismatch, so the stream and the payload can never name different aggregates.
 */
interface DomainEvent extends SerializablePayload
{
    /**
     * The id of the aggregate this event belongs to, in the form the store speaks.
     *
     * A coordinate rather than a degraded identity: it names a stream and keys a row, so it carries
     * no parse and no identity class. A consumer needing the typed identity builds it at its own
     * boundary, where the concrete class is known and a malformed value has somewhere to fail.
     */
    public function aggregateId(): string;
}
