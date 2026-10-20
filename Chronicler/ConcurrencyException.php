<?php

declare(strict_types=1);

namespace Storm\Contracts\Chronicler;

use Storm\Contracts\Aggregate\AggregateRepository;

/**
 * The optimistic-concurrency conflict family.
 *
 * A competing writer moved the stream past the version the caller loaded, or the same decision
 * landed twice.
 *
 * Whether a retry can succeed is carried by the subtype, never by this root.
 * {@see RetryableConcurrencyConflict} marks the conflicts that reloading and deciding again can win.
 * A conflict without the marker is terminal, whether already applied or a clash a reload cannot fix,
 * and is never retried blindly. Catch this root to classify or log, and hang retry policy on the
 * marker.
 *
 * @see AggregateRepository::store()
 */
interface ConcurrencyException extends ChroniclerExceptionContract {}
