<?php

declare(strict_types=1);

namespace Storm\Contracts\Aggregate;

/**
 * The string form handed to an identity does not parse.
 *
 * Catch it at an I/O boundary, such as a console option or an API parameter, to report the
 * expected format. Inside the domain it is a bug, since identities there come from trusted streams.
 *
 * @see AggregateIdentity::fromString()
 */
interface InvalidAggregateIdentity extends AggregateExceptionContract {}
