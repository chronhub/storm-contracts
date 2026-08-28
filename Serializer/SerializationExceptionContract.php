<?php

declare(strict_types=1);

namespace Storm\Contracts\Serializer;

use Throwable;

/**
 * The contracted serialization failure: a message cannot be serialized or deserialized, such as a
 * malformed wire header, an unloadable type, or a missing or mistyped field.
 */
interface SerializationExceptionContract extends Throwable {}
