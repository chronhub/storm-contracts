<?php

declare(strict_types=1);

namespace Storm\Contracts\Chronicler;

use Storm\Contracts\Message\MessageTypeMapper;

/**
 * The `MessageTypeMapper` with the schema version that drives upcasting, one map for events and
 * commands alike.
 *
 * `event_store.type` holds the alias and `event_store.event_version` the schema version applied on
 * read. An event declares both through the `EventType` attribute, a command its alias alone through
 * `CommandType`, and a class without either falls back to its FQCN at version 1.
 *
 * Alias stability covers type resolution only. A payload codec's cryptographic context is its own:
 * a personal `v3` envelope binds the alias its row stores, but a `v2` envelope binds the original
 * PHP class and payload key, which this mapper neither records nor migrates.
 */
interface EventTypeMapper extends MessageTypeMapper
{
    /**
     * Maps a class FQCN to its current schema version, or 1 when none is declared.
     *
     * @param  class-string  $class
     * @return positive-int
     */
    public function versionOf(string $class): int;
}
