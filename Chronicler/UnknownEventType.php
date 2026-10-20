<?php

declare(strict_types=1);

namespace Storm\Contracts\Chronicler;

/**
 * The event-type map cannot resolve a stored type, for one of two reasons with opposite remedies:
 *
 * - The type resolves to no known class: usually the scan or bundle was not wired, an alias was
 *   renamed without a `replaces`, or the row was written by a newer release. A corrected or newer
 *   runtime resolves it.
 *
 * - The type resolves to a loadable class that is not a domain event. The store holds domain events
 *   only, so the row came from a restore or an import, and no release will ever decode it.
 *
 * Both fail fast, since a store that cannot name its own events is a wiring error rather than a
 * business condition. Only the disposition differs, read through `UndecodableRow`.
 *
 * @see UndecodableRow the disposition, per instance rather than per class
 * @see EventTypeMapper::toClass()
 */
interface UnknownEventType extends ChroniclerExceptionContract {}
