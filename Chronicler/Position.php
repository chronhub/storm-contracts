<?php

declare(strict_types=1);

namespace Storm\Contracts\Chronicler;

use Stringable;

/**
 * An opaque, comparable position in the event store: the read-your-writes consistency token.
 *
 * The store's own ordinal, produced by the safe-head read and by the row codec. Nothing hands one
 * back to a client today: the aggregate repository's `store()` answers `void` on purpose, and the
 * HTTP bridge waits on the projection's HEAD rather than on a token, so a reader trades the whole
 * lag for needing no receipt. The token half of the freshness port, `isCaughtUp()` and `waitFor()`,
 * exists for a caller that HAS a position and has no consumer in this tree.
 *
 * Opaque in USE: consumers compare positions through the value object and serialize them through
 * `toString()`, never parsing the string themselves. The underlying form, however, is contracted:
 * a monotone int64 store ordinal, where aborts burn positions, readable as an integer through
 * `toOrdinal()` and rendered in decimal by `toString()`. The projector's checkpoint schema,
 * `projections.last_position` as a bigint, and its freshness math depend on that form, so an
 * implementation whose ordinal is not the store ordinal is not substitutable; the checkpoint
 * boundary refuses a non-positive one loud.
 */
interface Position extends Stringable
{
    /**
     * Whether this position is strictly before `$other`.
     *
     * @throws InvalidPosition when `$other` is a different Position implementation
     */
    public function isBefore(self $other): bool;

    /**
     * Whether this position is strictly after `$other`.
     *
     * @throws InvalidPosition when `$other` is a different Position implementation
     */
    public function isAfter(self $other): bool;

    /**
     * Whether this position is the same as `$other`.
     *
     * @throws InvalidPosition when `$other` is a different Position implementation
     */
    public function equals(self $other): bool;

    /**
     * The opaque string form of the position: the decimal rendering of `toOrdinal()`;
     * `__toString()` renders the same value, the explicit method being the greppable,
     * pipeline-friendly twin.
     */
    public function toString(): string;

    /**
     * The contracted store ordinal as an integer, what the checkpoint schema persists and the
     * freshness math compares; the typed read that spares every consumer a parse of a string it
     * was told is opaque.
     *
     * @return positive-int
     */
    public function toOrdinal(): int;
}
