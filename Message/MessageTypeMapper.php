<?php

declare(strict_types=1);

namespace Storm\Contracts\Message;

use Storm\Contracts\Chronicler\UnknownEventType;

/**
 * Maps a message class to its stable stored type and back.
 *
 * A stored type is a developer-owned alias, such as `order.placed`, decoupled from the FQCN so it
 * survives a class rename on the surfaces that store it: a store column, a neutral wire body, a saga
 * outbox row. A Messenger lane on `PhpSerializer` and the failure transport keep the class instead.
 *
 * A class without a declared alias maps to its FQCN, which the event store and the wire accept; the
 * saga outbox refuses such a command.
 */
interface MessageTypeMapper
{
    /**
     * Maps a class FQCN to its stored type alias.
     *
     * @param  class-string  $class
     */
    public function toType(string $class): string;

    /**
     * Maps a stored type alias back to its current class FQCN.
     *
     * @return class-string
     *
     * @throws UnknownEventType when the stored type resolves to no known class
     */
    public function toClass(string $type): string;

    /**
     * Every stored type a class may appear as on a durable surface, the current one first.
     *
     * A row written before an alias rename keeps the replaced alias, and one written before any alias
     * keeps the FQCN, so a reader that recognizes a class by its stored column compares against this
     * list rather than against `toType()` alone. The list always holds at least the FQCN, and the FQCN
     * alone for a class without a declared alias or one deleted since its name was written.
     *
     * @param  class-string  $class
     * @return list<string>
     */
    public function storedTypesOf(string $class): array;
}
