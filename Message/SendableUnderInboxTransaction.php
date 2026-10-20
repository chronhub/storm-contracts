<?php

declare(strict_types=1);

namespace Storm\Contracts\Message;

/**
 * Declares that sending this message to a broker under an open inbox transaction is safe.
 *
 * It is declared on the message class, the message-side counterpart of the handler-side
 * `#[DispatchesUnderInboxTransaction]` grant.
 *
 * The dual-write guard refuses an undeclared broker send inside an inbox-owned transaction, since a
 * rollback cannot unsend it and a replay sends a copy. Some messages are built for exactly that:
 *
 * - Fire-and-forget observability records dispatched wherever the instrumented code runs
 * - A saga history entry recorded mid-handling
 *
 * Implementing this interface states once, on the class, that losing the message on a rollback or
 * receiving it twice on a replay is acceptable to every consumer, so it may leave from under any
 * handler, signed or not.
 *
 * Reserve it for messages whose duplication and loss are both harmless, such as telemetry or audit
 * trails with their own deduplication. A domain command or event never qualifies: it goes through an
 * outbox or a handler-level grant, where the at-least-once consequence has a named owner.
 */
interface SendableUnderInboxTransaction {}
