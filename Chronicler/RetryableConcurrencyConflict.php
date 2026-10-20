<?php

declare(strict_types=1);

namespace Storm\Contracts\Chronicler;

/**
 * Marks the conflicts of the optimistic-concurrency family that a retry can win.
 *
 * The writer lost a race, and reloading the current stream then deciding again can succeed. Retry
 * policy hangs on this marker rather than on {@see ConcurrencyException}: a conflict without it is
 * terminal, whether the decision is already applied or a reload cannot fix the clash, and is never
 * retried blindly.
 */
interface RetryableConcurrencyConflict extends ConcurrencyException {}
