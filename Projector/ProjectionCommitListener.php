<?php

declare(strict_types=1);

namespace Storm\Contracts\Projector;

/**
 * Post-commit hook of a projection batch: fired by the runner AFTER the batch transaction
 * committed and only when the batch applied at least one record.
 *
 * That is the moment a read model visibly changed, the only correct moment to invalidate
 * whatever was derived from it: an HTTP cache purge, a websocket nudge, a metrics tick.
 *
 * The contract is strictly fire-and-forget: an implementation MUST NOT throw. A failing
 * side-channel, such as a purger being down, must never fail the projection; degrade and log
 * inside the implementation. The RUNNER enforces the clause regardless: it owns the invariant,
 * so a listener that throws anyway is absorbed and surfaced through observability, never through
 * the run's outcome. The implementation's discipline is redundant defense, not the only barrier.
 * Called inside the run loop, so keep it fast or hand off.
 *
 * Implementations are COLLECTED, never a single slot: under the bundle every service implementing
 * this interface is tagged and the injected port resolves to a composite fan-out, so a second
 * listener never evicts the first; a runner wired without the bundle keeps its Null default.
 */
interface ProjectionCommitListener
{
    /**
     * Signal that the named projection committed a batch which applied at least one record.
     */
    public function committed(string $projection): void;
}
