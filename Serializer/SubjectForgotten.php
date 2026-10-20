<?php

declare(strict_types=1);

namespace Storm\Contracts\Serializer;

use Throwable;

/**
 * The one refusal {@see CipherKeyStore::issue()} promises: the subject is tombstoned.
 *
 * Issuing a key for it would resurrect a forgotten identity. The write path lets it bubble, since
 * new personal data about a forgotten subject is a compliance bug to surface.
 */
interface SubjectForgotten extends Throwable {}
