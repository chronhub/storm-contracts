<?php

declare(strict_types=1);

namespace Storm\Contracts\Chronicler;

/**
 * Maps a domain event's PHP class to and from its stable stored type and exposes its current
 * schema version.
 *
 * The event_store.type column carries a stable alias, for example, 'order.placed', decoupled from
 * the FQCN so it survives class renames; event_store.event_version carries the schema version,
 * which drives upcasting on read. An event declares both the alias and the version via the
 * EventType attribute; a class without it falls back to its FQCN at version 1.
 *
 * Alias stability covers type resolution. It does not preserve a payload codec's cryptographic
 * context: personal `v2` envelopes also bind the original PHP class and payload key, which this
 * mapper neither records nor migrates.
 */
interface EventTypeMapper
{
    /**
     * Maps a class FQCN to its stored event type alias.
     */
    public function toType(string $class): string;

    /**
     * Maps a stored event type alias back to its current class FQCN.
     *
     * @return class-string
     *
     * @throws UnknownEventType when the stored type resolves to no known class
     */
    public function toClass(string $type): string;

    /**
     * Maps a class FQCN to its current schema version, or 1 when none is declared.
     *
     * @return positive-int
     */
    public function versionOf(string $class): int;

    /**
     * Every stored type a class may appear as in the event store.
     *
     * Returns, for the given class FQCN:
     *
     * - Its current alias
     * - Any replaced aliases
     * - The FQCN itself, matching rows written before the alias existed
     *
     * This lets a consumer filter the store by event class and still match rows whatever form each
     * was stored as, so a type filter keeps working after an event gains or renames an EventType
     * alias. A class without an EventType attribute resolves to just its FQCN.
     *
     * @return list<string>
     */
    public function storedTypesOf(string $class): array;
}
