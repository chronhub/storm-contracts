<?php

declare(strict_types=1);

namespace Storm\Contracts\Message;

/**
 * A domain message that serializes itself to, and rebuilds itself from, a plain array.
 *
 * Storm serializes messages explicitly rather than by reflection, following the EventSauce pattern:
 * each one flattens its own value objects into JSON-friendly scalars in toPayload and reconstructs
 * them in fromPayload. This keeps full control over the persisted shape and avoids coupling the
 * serializer to any object-normalization magic.
 *
 * The returned array is the content written to and read from the event store.
 */
interface SerializablePayload
{
    /**
     * The message flattened to a storable array.
     *
     * @return array<string, mixed>
     */
    public function toPayload(): array;

    /**
     * Rebuild the message from its stored array.
     *
     * Free to refuse a payload it cannot rebuild by throwing anything: the wrap to the contracted
     * serialization failure is the deserializing port's job, a conforming `MessageSerializer` never
     * lets a raw payload exception through, so an implementation stays loud on corrupt data without
     * naming any framework type.
     *
     * @param  array<string, mixed>  $payload
     */
    public static function fromPayload(array $payload): static;
}
