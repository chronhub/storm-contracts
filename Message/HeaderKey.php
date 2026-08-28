<?php

declare(strict_types=1);

namespace Storm\Contracts\Message;

/**
 * A type-safe key for a Message header.
 *
 * The framework ships its own keys as the Header enum, but applications extend the header space
 * simply by declaring their own backed enum that implements this interface, with no registry and
 * no central registration:
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
 * Convention: the __ prefix is reserved for framework keys; application keys must not use it.
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
