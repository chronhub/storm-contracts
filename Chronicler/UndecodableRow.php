<?php

declare(strict_types=1);

namespace Storm\Contracts\Chronicler;

/**
 * A stored row this runtime cannot turn back into a message.
 *
 * The classification a relay and a transport both need, and the one neither can make from the
 * exception's TYPE alone: what separates a row to park from a row to freeze is not what went wrong,
 * it is whether a DIFFERENT binary would succeed. A missing version step, two upcasters wired onto
 * one, an upcaster whose own code throws, a row written by a newer release: all of them decode
 * elsewhere, so parking them demands a manual requeue for a fault the next deployment already fixed,
 * and breaks their aggregate's order meanwhile.
 *
 * `resolvableByAnotherRuntime()` carries the answer per instance rather than per class, because one
 * cause genuinely straddles: an unresolved type alias reads as a rollout when the alias is simply not
 * declared here, and as poison when it resolves to something that is not a domain event.
 *
 * @see ChroniclerExceptionContract the root every contracted Chronicler failure implements
 */
interface UndecodableRow extends ChroniclerExceptionContract
{
    /**
     * Whether a corrected or newer runtime would decode this row.
     *
     * True means a deployment state: freeze the row where it is, keep its order, and report it. False
     * means no release will ever decode it: park it, and let the tail proceed.
     */
    public function resolvableByAnotherRuntime(): bool;
}
