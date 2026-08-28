<?php

declare(strict_types=1);

namespace Storm\Contracts\Projector;

use Storm\Contracts\Chronicler\Position;
use Storm\Contracts\Chronicler\StorageFailure;

/**
 * Read-your-writes freshness and readiness of a projection, as a consumer sees it: has the
 * projection caught up to a position or to the current head, how far behind is it, and the
 * bounded waits over those probes. The port an edge consumes without ever knowing the projector
 * machinery, for example:
 *
 * - An HTTP bridge serving fresh or returning 202
 * - A readiness probe
 * - A blue/green swap signal
 *
 * Not-fresh is an ANSWER, the boolean `false`, never an exception: the caller decides what
 * staleness means. Every probe is non-locking; the projection advances in its own daemon/run.
 */
interface ProjectionFreshness
{
    /**
     * Single non-blocking poll: has the projection's checkpoint reached `$token` yet?
     *
     * @throws StorageFailure when the checkpoint read fails at the storage level
     */
    public function isCaughtUp(string $name, Position $token): bool;

    /**
     * Wait until the projection's checkpoint reaches `$token` or the timeout elapses.
     *
     * @param  float  $timeoutSeconds  max time to wait; `0` runs a single `isCaughtUp()` poll
     * @param  positive-int  $pollMs  sleep between polls
     *
     * @throws StorageFailure when the checkpoint read fails at the storage level
     */
    public function waitFor(string $name, Position $token, float $timeoutSeconds = 5.0, int $pollMs = 50): bool;

    /**
     * Has the projection's checkpoint reached the current safe head? An empty store is
     * trivially at head.
     *
     * @throws StorageFailure when the safe-head or checkpoint read fails at the storage level
     */
    public function isAtHead(string $name): bool;

    /**
     * Wait until the projection has caught up to the head or the timeout elapses. The GUARANTEE is
     * "caught up as of the request", which covers every write committed before the wait began, so the
     * writer needs no token; HOW the head is resolved is the implementation's business, and freezing
     * it for the wait satisfies the same guarantee, a head only ever moving up.
     *
     * @param  float  $timeoutSeconds  max time to wait; `0` runs a single `isAtHead()` poll
     * @param  positive-int  $pollMs  sleep between polls
     *
     * @throws StorageFailure when the safe-head or checkpoint read fails at the storage level
     */
    public function waitForHead(string $name, float $timeoutSeconds = 5.0, int $pollMs = 50): bool;

    /**
     * Positions the projection is behind the current safe head; 0 means caught up. A
     * point-in-time read: the head moves, and for a daemon it tends toward 0.
     *
     * @throws StorageFailure when the safe-head or checkpoint read fails at the storage level
     */
    public function lag(string $name): int;
}
