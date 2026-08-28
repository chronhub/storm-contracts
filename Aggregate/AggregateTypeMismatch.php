<?php

declare(strict_types=1);

namespace Storm\Contracts\Aggregate;

/**
 * The contracted type-mismatch failure: an aggregate or an identity handed to a repository does not
 * match the aggregate type or the id type that repository is bound to; a configuration or wiring
 * error surfaced at the port.
 *
 * @see AggregateRepository::store()
 * @see AggregateRepository::retrieve()
 */
interface AggregateTypeMismatch extends AggregateExceptionContract {}
