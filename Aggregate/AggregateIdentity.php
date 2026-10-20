<?php

declare(strict_types=1);

namespace Storm\Contracts\Aggregate;

use Stringable;

/**
 * The typed identity of an aggregate root.
 *
 * Applications declare their own, such as `OrderId`, usually through the `ProvideAggregateIdentity`
 * trait, so identities stay type-safe across the domain. The string form is the aggregate id
 * stamped on events and used in the stream name.
 */
interface AggregateIdentity extends Stringable
{
    /**
     * Rebuild an identity from its string form.
     *
     * @throws InvalidAggregateIdentity when the string is not a valid identity
     */
    public static function fromString(string $id): static;

    /**
     * Generate a fresh identity.
     */
    public static function generate(): static;

    /**
     * Value equality: same concrete type and same value.
     *
     * Another concrete of this interface answers false and never throws, since the signature
     * admits the whole family. The repository, by contrast, refuses a foreign identity with a typed
     * error.
     */
    public function equals(self $other): bool;

    /**
     * The canonical string form, the value `__toString()` also renders.
     *
     * The named method can be searched for and passed as a callable.
     */
    public function toString(): string;
}
