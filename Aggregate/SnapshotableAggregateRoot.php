<?php

declare(strict_types=1);

namespace Storm\Contracts\Aggregate;

use Generator;
use Storm\Contracts\Message\DomainEvent;

/**
 * An aggregate root the repository can rebuild from a snapshot.
 *
 * A snapshot caches the aggregate's state at a version, and the events stay authoritative. The
 * repository loads the latest snapshot and replays only the events recorded after it.
 *
 * The interface names no snapshot DTO or infrastructure type: the domain deals with the state array
 * and the integer version, and the persistence envelope is unpacked at the infrastructure boundary.
 *
 * @template-covariant TId of AggregateIdentity
 *
 * @extends AggregateRoot<TId>
 */
interface SnapshotableAggregateRoot extends AggregateRoot
{
    /**
     * The state-array key of the snapshot's shape version.
     *
     * `toSnapshot()` writes it and `fromSnapshot()` reads it. Every writer uses this one spelling: a
     * mistyped key would read as shape 1 forever, restoring stale-shape rows instead of ignoring
     * them.
     */
    public const string SNAPSHOT_VERSION_KEY = '_snapshot_version';

    /**
     * Export the complete state needed to rebuild the aggregate.
     *
     * It holds everything `fromSnapshot()` needs plus the shape version under `SNAPSHOT_VERSION_KEY`,
     * and leaves out unreleased events and derived or transient data.
     *
     * @return array<string, mixed>
     */
    public function toSnapshot(): array;

    /**
     * Rebuild from a stored state, then replay the events recorded after the snapshot.
     *
     * Returns null when `SNAPSHOT_VERSION_KEY` no longer matches the current shape, so the caller
     * reconstitutes from the full history instead.
     *
     * @param  array<string, mixed>  $state  the array returned by `toSnapshot()`
     * @param  int  $version  the version the snapshot was taken at
     * @param  Generator<int, DomainEvent, null, int>  $after  events with a version above the
     *                                                         snapshot's; the return value is the
     *                                                         aggregate's current version, provided
     *                                                         by the repository
     *
     * @throws InvalidSnapshotState when the stored state is unusable; the snapshot repository turns
     *                              it into a cache miss and replays the full history
     * @throws CorruptAggregateHistory when the replayed tail contradicts the snapshot version plus its
     *                                 applied count
     */
    public static function fromSnapshot(AggregateIdentity $aggregateId, array $state, int $version, Generator $after): ?static;
}
