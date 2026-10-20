<?php

declare(strict_types=1);

namespace Storm\Contracts\Message;

/**
 * The cross-cutting context of the message currently being handled.
 *
 * A small, framework-neutral view of who or what caused the work in flight:
 *
 * - The correlation, causation, actor and tenant identifiers
 * - The declared transverse bag
 *
 * Components on the write path, chiefly the aggregate repository, stamp newly recorded events with
 * it without depending on Symfony Messenger. At runtime the context travels as Messenger stamps,
 * and a single middleware in the Story package binds them into a neutral implementation; nothing
 * else reads a stamp, so the event store and the projectors see only this contract and the
 * `Message`.
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
     * The declared transverse bag of opaque key/value metadata.
     *
     * It propagates along the causal chain like the identifiers above, and the framework never reads
     * it.
     *
     * - Keys are application header keys; the `__` prefix stays reserved for framework headers
     * - Values are JSON-friendly scalars
     * - Only the keys declared under `storm.context.propagated_keys` cross hops; an undeclared
     *   header annotates one message only
     *
     * Domain data never belongs here: a value a downstream fact or decision branches on is payload.
     * Crypto-shredding does not cover the bag, so personal data is forbidden in it.
     *
     * @return array<string, bool|float|int|string>
     */
    public function bag(): array;
}
