<?php

declare(strict_types=1);

namespace Storm\Contracts\Aggregate;

use Stringable;

/**
 * The typed identity of an aggregate root.
 *
 * Applications declare their own, for example OrderId, usually via the ProvideAggregateIdentity
 * trait, so identities are type-safe across the domain. Always Stringable: its string form is the
 * aggregate id stamped on events and used in the stream name.
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
     * Value equality, same concrete type and same value. A foreign identity, another concrete of
     * this interface, answers false and never throws: the signature admits the whole family, so
     * equality is a question here, not the assertion the repository makes with its typed refusal.
     */
    public function equals(self $other): bool;

    /**
     * The identity's canonical string form; `__toString()` renders the same value, the explicit
     * method being the greppable, pipeline-friendly twin.
     */
    public function toString(): string;
}
