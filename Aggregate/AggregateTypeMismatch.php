<?php

declare(strict_types=1);

namespace Storm\Contracts\Aggregate;

/**
 * An aggregate or an identity handed to a repository does not match the types it is bound to.
 *
 * A configuration or wiring error, surfaced at the port.
 *
 * @see AggregateRepository::store()
 * @see AggregateRepository::retrieve()
 */
interface AggregateTypeMismatch extends AggregateExceptionContract {}
