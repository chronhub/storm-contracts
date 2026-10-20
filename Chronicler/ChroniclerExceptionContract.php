<?php

declare(strict_types=1);

namespace Storm\Contracts\Chronicler;

use Throwable;

/**
 * Root of every failure the Chronicler module originates and lets cross its boundary.
 *
 * Catch it to handle any of them, or catch a child for a precise reaction, such as
 * `ConcurrencyException` for the bounded write retry.
 *
 * Two kinds of failure cross the boundary without implementing it:
 *
 * - A failure the module re-exposes from a neighbor, such as a serialization, clock or stream fault,
 *   keeps that neighbor's own contract, the same promise one package over
 *
 * - A port that declares a `Throwable` tail grants no exhaustiveness there, since the driver failure
 *   cannot be named at a layer built to stay free of the driver
 *
 * The rule holds for every port, so it is checked on the ports rather than on a list of leaves, by
 * `ContractedExceptionsTest::no_port_declares_a_failure_the_umbrella_does_not_cover`.
 *
 * @see ConcurrencyException the recoverable write-path signal, caught for a bounded retry
 * @see StorageFailure       the infrastructural read/write failure that bubbles to the global handler
 * @see UndecodableRow       the row this runtime cannot decode, and whether another could
 * @see InvalidPosition
 * @see UnknownEventType
 */
interface ChroniclerExceptionContract extends Throwable {}
