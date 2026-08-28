<?php

declare(strict_types=1);

namespace Storm\Contracts\Message;

/**
 * Declares, on the MESSAGE class, that sending it to a broker while an inbox transaction is open is
 * safe by design; the message-side counterpart of the handler-side
 * `#[DispatchesUnderInboxTransaction]` grant.
 *
 * The dual-write guard refuses an undeclared broker send inside an inbox-owned transaction: a
 * rollback cannot unsend it, and a replay re-sends a copy. Some messages are DESIGNED for exactly
 * that shape: fire-and-forget observability records dispatched from wherever the instrumented code
 * runs, or a saga history entry recorded mid-handling. Implementing this interface states the
 * contract once, on the class: losing the message on a rollback or receiving it twice on a replay
 * is acceptable to every consumer, so it may leave from under ANY handler, signed or not.
 *
 * Reserve it for messages whose duplication and loss are both harmless, such as telemetry or audit
 * trails with their own dedup. A domain command or event never qualifies: those route through an
 * outbox or a handler-level grant, where the at-least-once consequence has a named owner.
 */
interface SendableUnderInboxTransaction {}
