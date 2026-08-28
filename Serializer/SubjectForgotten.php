<?php

declare(strict_types=1);

namespace Storm\Contracts\Serializer;

use Throwable;

/**
 * Contracted marker for the one refusal {@see CipherKeyStore::issue()} promises: the subject is
 * tombstoned, and issuing a key for it would silently resurrect a forgotten identity.
 *
 * The write path lets it bubble; new personal data about a forgotten subject is a compliance bug
 * to surface, never to absorb.
 */
interface SubjectForgotten extends Throwable {}
