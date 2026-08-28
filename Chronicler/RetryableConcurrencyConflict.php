<?php

declare(strict_types=1);

namespace Storm\Contracts\Chronicler;

/**
 * Marks the retryable side of the optimistic-concurrency family: the writer merely lost a race,
 * and a reload-and-redecide against the now-current stream can win. Retry policy hangs HERE, not
 * on {@see ConcurrencyException}; a conflict without this marker is terminal, whether the decision
 * is already applied or a reload cannot fix the clash, and must never be blind-retried.
 */
interface RetryableConcurrencyConflict extends ConcurrencyException {}
