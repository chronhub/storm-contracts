<?php

declare(strict_types=1);

namespace Storm\Contracts\Message;

/**
 * A type-safe key for a `Message` header.
 *
 * The framework ships its own keys as the `Header` enum. An application adds its own by declaring a
 * backed enum that implements this interface, with no registry:
 *
 * ```php
 * enum OrderHeader: string implements HeaderKey
 * {
 *     case Channel = 'channel';
 *
 *     public function key(): string
 *     {
 *         return $this->value;
 *     }
 * }
 * ```
 *
 * The `__` prefix is reserved for framework keys; application keys never use it.
 *
 * @see \Storm\Message\Message
 */
interface HeaderKey
{
    /**
     * The storage key written to the header bag, and to the header jsonb column.
     */
    public function key(): string;
}
