<?php

declare(strict_types=1);

namespace Storm\Contracts\Chronicler;

/**
 * The contracted resolution failure of the event-type map, and it covers TWO conditions whose
 * remedies are opposite.
 *
 * A stored type resolves to no known class: usually the scan or bundle was not wired, an alias was
 * renamed without a `replaces`, or the row was written by a release this binary is older than. A
 * corrected or newer runtime resolves it.
 *
 * Or it resolves to a loadable class that is NOT a domain event: the store holds domain events
 * exclusively, so the row came from a restore or an import, and no release will ever decode it.
 *
 * Both are fail-fast, a store that cannot name its own events being a wiring error rather than a
 * business condition; only the disposition differs, and it is read through `UndecodableRow`.
 *
 * @see UndecodableRow the disposition, per instance rather than per class
 * @see EventTypeMapper::toClass()
 */
interface UnknownEventType extends ChroniclerExceptionContract {}
