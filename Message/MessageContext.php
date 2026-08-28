<?php

declare(strict_types=1);

namespace Storm\Contracts\Message;

/**
 * The cross-cutting context of the message currently being handled.
 *
 * This is a deliberately tiny, framework-neutral view of who or what caused the work in flight:
 * correlation, causation, actor, and tenant identifiers, plus the declared transverse bag. It
 * exists so that components on the write path, chiefly the aggregate repository, can stamp newly
 * recorded events with the current context without depending on the transport, Symfony Messenger.
 *
 * The runtime carrier of this context is Messenger stamps, but a single middleware in the Story
 * package binds those stamps into a neutral implementation; nothing outside that middleware ever
 * reads a stamp. The event store and projectors only ever see this neutral contract and the
 * Message.
 *
 * @see \Storm\Message\Message
 */
interface MessageContext
{
    /**
     * Identifier shared by every message in the same workflow / trace.
     */
    public function correlationId(): ?string;

    /**
     * Identifier of the message that directly caused the current one.
     */
    public function causationId(): ?string;

    /**
     * Identifier of the actor, a user or service, on whose behalf the work runs.
     */
    public function actorId(): ?string;

    /**
     * Type of the actor, for example "user" or "system".
     */
    public function actorType(): ?string;

    /**
     * Tenant the work belongs to, when multi-tenant.
     */
    public function tenantId(): ?string;

    /**
     * The declared transverse bag: opaque key/value metadata that propagates along the causal
     * chain exactly like the fixed identifiers above, and that the framework itself never reads.
     *
     * Keys are application header keys, the `__` prefix staying reserved for framework headers;
     * values are JSON-friendly scalars. Which keys propagate across hops is DECLARED wiring under
     * `storm.context.propagated_keys`, never an open floodgate: an undeclared header stays a
     * one-message annotation. Domain data never belongs here; a downstream fact or decision that
     * branches on a value makes it payload. The bag is not covered by crypto-shredding, so personal
     * data is forbidden in it.
     *
     * @return array<string, bool|float|int|string>
     */
    public function bag(): array;
}
