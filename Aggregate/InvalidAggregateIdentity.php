<?php

declare(strict_types=1);

namespace Storm\Contracts\Aggregate;

/**
 * The contracted identity-parse failure: the string form handed is not a valid identity. Catchable
 * at an I/O boundary, such as a console option or API param, to report the expected format; inside
 * the domain it stays a bug, since identities there come from trusted streams.
 *
 * @see AggregateIdentity::fromString()
 */
interface InvalidAggregateIdentity extends AggregateExceptionContract {}
