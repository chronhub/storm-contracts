<?php

declare(strict_types=1);

namespace Storm\Contracts\Chronicler;

use Storm\Contracts\Aggregate\AggregateRepository;

/**
 * The contracted optimistic-concurrency conflict FAMILY: a competing writer moved the stream past
 * the version the caller loaded, or the same decision landed twice. Whether a retry can succeed
 * is carried by the subtype, never by this root; {@see RetryableConcurrencyConflict} marks the
 * conflicts a reload-and-redecide can win, while a conflict without the marker is terminal, whether
 * already applied or a true clash a reload cannot fix, and must not be blind-retried. Catch this
 * root to classify or log; hang retry policy on the marker.
 *
 * @see AggregateRepository::store()
 */
interface ConcurrencyException extends ChroniclerExceptionContract {}
