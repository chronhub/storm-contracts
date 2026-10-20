<?php

declare(strict_types=1);

namespace Storm\Contracts\Message;

/**
 * Generates unique identifiers for message metadata, chiefly the event id.
 *
 * The identifier scheme is one swappable decision. The default implementation produces time-ordered
 * UUID v7 values, which keep the event store roughly insertion-ordered on the id.
 */
interface MetaIdentityGenerator
{
    /**
     * A fresh, unique identifier in its canonical string form.
     */
    public function generate(): string;
}
