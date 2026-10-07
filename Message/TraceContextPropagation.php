<?php

declare(strict_types=1);

namespace Storm\Contracts\Message;

use Closure;

/**
 * Captures and scopes technical trace context without exposing an instrumentation SDK.
 *
 * Operations and the returned cleanup must not throw or perform network IO. No baggage is carried.
 */
interface TraceContextPropagation
{
    /**
     * Captures the active span, or an empty carrier when no valid span is active.
     *
     * @return array<string, string>
     */
    public function capture(): array;

    /**
     * Activates a validated parent over an empty root and returns its scope cleanup.
     *
     * Invalid or absent input establishes an empty root, never the enclosing message.
     * The caller must invoke the cleanup in `finally` to restore its enclosing context.
     *
     * @param  array<string, string>  $carrier
     * @return Closure(): void
     */
    public function activate(array $carrier): Closure;
}
