<?php

declare(strict_types=1);

namespace Storm\Contracts\Chronicler;

use Throwable;

/**
 * Persists the identity assigned to one request within its caller scope and retry window.
 * Acquisition commits before returning. It does not prove dispatch or execution, and a caller
 * must retain the claim after an uncertain send. Consumer deduplication remains a separate gate.
 */
interface IdempotencyRegistry
{
    /**
     * Return the original identity for an identical, unexpired claim, or acquire a fresh one.
     * Scope, key and fingerprint are lowercase SHA-256 hex digests, never request bodies or credentials.
     * Replays do not extend expiration. An expired key starts a new generation without awaiting purge.
     *
     * @throws IdempotencyConflict when the live key belongs to a different fingerprint
     * @throws InvalidIdempotencyClaim when arguments are invalid or an ambient transaction prevents a durable claim
     * @throws Throwable on a storage failure whose driver type is outside this contract
     */
    public function claim(string $scopeHash, string $keyHash, string $fingerprint): string;
}
