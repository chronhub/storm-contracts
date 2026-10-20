<?php

declare(strict_types=1);

namespace Storm\Contracts\Serializer;

use Throwable;

/**
 * A message cannot be serialized or deserialized, for example because of:
 *
 * - A malformed wire header
 * - An unloadable type
 * - A missing or mistyped field
 */
interface SerializationExceptionContract extends Throwable {}
