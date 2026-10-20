<?php

declare(strict_types=1);

namespace Storm\Contracts\Aggregate;

/**
 * A released event's own `aggregateId()` contradicts the identity of the aggregate being stored.
 *
 * Refused at `store()`: this domain bug would otherwise persist a permanently incoherent fact, the
 * stream naming one aggregate and the payload another.
 *
 * @see AggregateRepository::store()
 */
interface EventAggregateIdMismatch extends AggregateExceptionContract {}
