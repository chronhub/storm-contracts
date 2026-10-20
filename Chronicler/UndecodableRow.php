<?php

declare(strict_types=1);

namespace Storm\Contracts\Chronicler;

/**
 * A stored row this runtime cannot turn back into a message.
 *
 * A relay and a transport both decide whether to park such a row or to freeze it, and the
 * exception's type alone cannot tell them: the answer is whether a different binary would decode
 * it. These rows decode elsewhere:
 *
 * - A missing version step
 * - Two upcasters wired onto one version step
 * - An upcaster whose own code throws
 * - A row written by a newer release
 *
 * Parking them would demand a manual requeue for a fault the next deployment already fixed, and
 * break their aggregate's order meanwhile.
 *
 * `resolvableByAnotherRuntime()` answers per instance rather than per class, because one cause falls
 * on both sides: an unresolved type alias is a rollout when the alias is simply not declared here,
 * and poison when it resolves to something that is not a domain event.
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
