<?php

declare(strict_types=1);

namespace Storm\Contracts\Projector;

/**
 * Post-commit hook of a projection batch.
 *
 * The runner fires it after the batch transaction committed, and only when the batch applied at
 * least one record. That is when a read model visibly changed, the one moment to invalidate what
 * was derived from it:
 *
 * - An HTTP cache purge
 * - A websocket nudge
 * - A metrics tick
 *
 * An implementation never throws: a failing side channel, such as a purger being down, must not
 * fail the projection, so it degrades and logs internally. The runner owns that invariant anyway: it
 * absorbs a listener that throws and reports it through observability, never through the run's
 * outcome, so the implementation's own discipline is a second barrier. The hook runs inside the run
 * loop, so it stays fast or hands off.
 *
 * Under the bundle, every service implementing this interface is tagged and the injected port
 * resolves to a composite fan-out, so a second listener never evicts the first. A runner wired
 * without the bundle keeps its null default.
 */
interface ProjectionCommitListener
{
    /**
     * Signal that the named projection committed a batch which applied at least one record.
     */
    public function committed(string $projection): void;
}
