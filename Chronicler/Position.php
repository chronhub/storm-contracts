<?php

declare(strict_types=1);

namespace Storm\Contracts\Chronicler;

use Stringable;

/**
 * An opaque, comparable position in the event store: the read-your-writes consistency token.
 *
 * It is the store's own ordinal, produced by the safe-head read and by the row codec. No client
 * receives one: the aggregate repository's `store()` returns `void`, and the HTTP bridge waits on
 * the projection's head rather than on a token, so a reader trades the whole lag for needing no
 * receipt. The token half of the freshness port, `isCaughtUp()` and `waitFor()`, serves a caller
 * that holds a position, and has no consumer in this tree.
 *
 * Consumers compare positions through the value object and serialize them through `toString()`,
 * never parsing the string. The underlying form is still contracted:
 *
 * - A monotone int64 store ordinal, in which aborts burn positions
 * - Read as an integer through `toOrdinal()`
 * - Rendered in decimal by `toString()`
 *
 * The projector's checkpoint, `projections.last_position` as a bigint, and its freshness math
 * depend on that form, so an implementation whose ordinal is not the store ordinal cannot replace
 * it. The checkpoint boundary refuses a non-positive one with an error.
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
     * The opaque string form, the decimal rendering of `toOrdinal()`.
     *
     * `__toString()` renders the same value; the named method can be searched for and passed as a
     * callable.
     */
    public function toString(): string;

    /**
     * The contracted store ordinal as an integer.
     *
     * The checkpoint persists it and the freshness math compares it, so a consumer reads it typed
     * instead of parsing a string it was told is opaque.
     *
     * @return positive-int
     */
    public function toOrdinal(): int;
}
