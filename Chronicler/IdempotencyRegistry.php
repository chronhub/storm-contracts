<?php

declare(strict_types=1);

namespace Storm\Contracts\Chronicler;

/**
 * Persists the identity assigned to one request within its caller scope and retry window.
 *
 * Acquisition commits before returning. A claim proves neither dispatch nor execution, so a caller
 * keeps it after an uncertain send. Consumer deduplication remains a separate gate.
 */
interface IdempotencyRegistry
{
    /**
     * Return the original identity for an identical, unexpired claim, or acquire a fresh one.
     *
     * Scope, key and fingerprint are lowercase SHA-256 hex digests, never request bodies or
     * credentials. A replay does not extend the expiration, and an expired key starts a new
     * generation without waiting for the purge.
     *
     * @throws IdempotencyConflict when the live key belongs to a different fingerprint
     * @throws InvalidIdempotencyClaim when arguments are invalid or an ambient transaction prevents a durable
     *                                 claim
     * @throws StorageFailure when the claim fails at the storage level
     */
    public function claim(string $scopeHash, string $keyHash, string $fingerprint): string;
}
