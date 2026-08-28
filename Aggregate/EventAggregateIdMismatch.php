<?php

declare(strict_types=1);

namespace Storm\Contracts\Aggregate;

/**
 * The contracted coherence failure at store(): a released event's own aggregateId() contradicts the
 * identity of the aggregate being stored; a domain bug that would otherwise persist a permanently
 * incoherent fact, where the stream names one aggregate and the payload another.
 *
 * @see AggregateRepository::store()
 */
interface EventAggregateIdMismatch extends AggregateExceptionContract {}
