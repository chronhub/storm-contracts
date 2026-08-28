<?php

declare(strict_types=1);

namespace Storm\Contracts\Serializer;

/**
 * The per-subject cipher keys behind crypto-shredding: one key per subject, issued on the first
 * write of a marked event, destroyed to forget the subject. The stored events are never touched;
 * destroying the key is the erasure, and it leaves a tombstone, so the forgetting is PROVABLE, an
 * audit can ask when it happened, and IDEMPOTENT, since re-forgetting is a green no-op.
 *
 * Key material is raw 32-byte strings, sized for XChaCha20-Poly1305. How it is protected at rest,
 * behind a master key or a vault, is the implementation's concern and invisible here, which keeps
 * the port vault-ready without naming any vault.
 *
 * `null` from {@see keyFor()} is AUTHORITATIVE: the subject is forgotten, or was never issued a
 * key, and the reader renders the declared fallbacks. An infrastructure failure must THROW the
 * implementation's own exception instead; silently rendering fallbacks because a database blinked
 * would present redaction as truth.
 */
interface CipherKeyStore
{
    /**
     * The subject's key material, or null when the subject is forgotten or was never issued a key.
     */
    public function keyFor(string $subject): ?string;

    /**
     * The subject's key material, issuing a fresh key on first use.
     *
     * Idempotent: an existing key is returned, never rotated.
     *
     * @throws SubjectForgotten when the subject is tombstoned; writing new personal data about a
     *                          forgotten subject silently resurrects them, so it refuses loud, and
     *                          a legitimate return needs a NEW subject id, minted by the domain
     */
    public function issue(string $subject): string;

    /**
     * Destroy the subject's key material, keeping a tombstone row as the proof of forgetting.
     *
     * A subject never issued a key gets a tombstone too, so the proof exists either way.
     *
     * @return bool true when this call did the forgetting; false when the subject was already
     *              tombstoned, which is the idempotence a re-run reports instead of failing
     */
    public function destroy(string $subject): bool;

    /**
     * Whether the subject is tombstoned, distinguishing "forgotten" from "never issued", which
     * {@see keyFor()} collapses.
     *
     * A forgotten subject's CLEARTEXT values, rows written before the class was marked, must render
     * fallbacks too, while a never-issued subject's stay readable.
     */
    public function isDestroyed(string $subject): bool;
}
