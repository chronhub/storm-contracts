<?php

declare(strict_types=1);

namespace Storm\Contracts\Message;

/**
 * A domain message that serializes itself to, and rebuilds itself from, a plain array.
 *
 * Storm serializes messages explicitly rather than by reflection, following the EventSauce pattern:
 * each message flattens its own value objects into JSON-friendly scalars in `toPayload()` and
 * rebuilds them in `fromPayload()`. The message keeps full control of the persisted shape, and the
 * serializer needs no reflective object normalization.
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
     * It may refuse a payload it cannot rebuild by throwing anything. A conforming `MessageSerializer`
     * wraps that exception into the contracted serialization failure, so an implementation fails on
     * corrupt data without naming any framework type.
     *
     * @param  array<string, mixed>  $payload
     */
    public static function fromPayload(array $payload): static;
}
