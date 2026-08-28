<?php

declare(strict_types=1);

namespace Storm\Contracts\Chronicler;

use Throwable;

/**
 * The root of the Chronicler module's contracted exception surface: every failure the module
 * ORIGINATES and lets cross its boundary is a ChroniclerExceptionContract. Catch it as the outer net,
 * or catch a specific child for a precise reaction, such as ConcurrencyException for the bounded
 * write-retry.
 *
 * Two things travel under it without being it, and neither is a hole. A failure the module
 * RE-EXPOSES from a neighbour, a serialization, clock or stream fault, carries that neighbour's own
 * contract, the same promise one package over. And a port that declares a `Throwable` tail says in so
 * many words that it grants no exhaustiveness there, the driver failure being unnameable at a layer
 * built to stay free of the driver.
 *
 * The claim is universal, so it is held to the PORTS rather than to a list of leaves: see
 * `ContractedExceptionsTest::no_port_declares_a_failure_the_umbrella_does_not_cover`.
 *
 * @see ConcurrencyException the recoverable write-path signal, caught for a bounded retry
 * @see StorageFailure       the infrastructural read/write failure that bubbles to the global handler
 * @see UndecodableRow       the row this runtime cannot decode, and whether another could
 * @see InvalidPosition
 * @see UnknownEventType
 */
interface ChroniclerExceptionContract extends Throwable {}
