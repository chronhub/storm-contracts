<?php

declare(strict_types=1);

namespace Storm\Contracts\Aggregate;

use Generator;
use Storm\Contracts\Message\DomainEvent;

/**
 * An AggregateRoot that supports snapshot-accelerated reconstruction.
 *
 * A snapshot is a cache of the aggregate's state at a version, never the source of truth; the
 * events stay authoritative. Implementing this lets the repository load the latest snapshot and
 * replay only the events recorded after it, instead of the whole history.
 *
 * Kept pure: it names no Snapshot DTO or infrastructure type, so the domain only ever deals with
 * the state array and the integer version; the persistence envelope is unpacked at the infra
 * boundary.
 *
 * @template-covariant TId of AggregateIdentity
 *
 * @extends AggregateRoot<TId>
 */
interface SnapshotableAggregateRoot extends AggregateRoot
{
    /**
     * The state-array key carrying the snapshot's shape version, written by `toSnapshot()` and read
     * back by `fromSnapshot()`. One spelling for every writer: a typo'd key would silently mean
     * shape 1 forever, restoring stale-shape rows instead of discarding them.
     */
    public const string SNAPSHOT_VERSION_KEY = '_snapshot_version';

    /**
     * Export the complete state needed to reconstruct the aggregate, as a plain array.
     *
     * Include everything fromSnapshot needs plus the shape-version marker under
     * `SNAPSHOT_VERSION_KEY`; exclude unreleased events and derived or transient data.
     *
     * @return array<string, mixed>
     */
    public function toSnapshot(): array;

    /**
     * Rebuild from a stored state, then replay the events recorded after the snapshot.
     *
     * Returns null when the snapshot is incompatible, its `SNAPSHOT_VERSION_KEY` no longer matching
     * the current shape, so the caller reconstitutes from the full event history instead. The after
     * generator's return value is the aggregate's true current version, provided by the repository.
     *
     * @param  array<string, mixed>  $state  the array returned by `toSnapshot()`
     * @param  int  $version  the version the snapshot was taken at
     * @param  Generator<int, DomainEvent, null, int>  $after  events with version greater than the
     *                                                         snapshot version; the return value is
     *                                                         the aggregate's true current version
     *
     * @throws InvalidSnapshotState when the stored state is semantically unusable; the snapshot
     *                              repository converts it into a cache miss and replays the full
     *                              authoritative history
     * @throws CorruptAggregateHistory when the replayed tail contradicts the snapshot version plus its applied count
     */
    public static function fromSnapshot(AggregateIdentity $aggregateId, array $state, int $version, Generator $after): ?static;
}
