# Storm Contracts

The foundational layer of the Storm framework: the pure interfaces every other Storm package
implements or consumes — aggregate identity and roots, the event taxonomy, the clock, the
event-type mapping, the contracted failure types. It depends on nothing but PHP, SPL and PSR, so
depending on it costs you no transitive weight and couples you to no implementation.

## When you reach for it

You rarely install this package alone: it arrives transitively with any Storm package. You reach
INTO it constantly —

- **type-hinting a port** in your own code (`Clock`, `EventTypeMapper`, `AggregateRepository`)
  instead of a concrete, so your domain never imports an implementation package;
- **implementing an interface** the framework calls back (`AggregateIdentity` for your typed ids,
  `DomainEvent` + `SerializablePayload` for your events, `SnapshotableAggregateRoot` to opt an
  aggregate into snapshotting);
- **catching a contracted failure** (`ConcurrencyException`, `StorageFailure`,
  `SerializationExceptionContract`) without knowing which package threw it.

If a type you expected here lives in a module instead (Chronicler's `StreamReader`, every Saga
port), that is deliberate — see rule 2 below.

## What's inside

One sub-namespace per concern, and every interface in a sub-namespace is public contract — browsing
the directory IS the fine-grained map:

- `Aggregate/` — root, identity and repository of event-sourced aggregates, snapshot opt-in, and
  their contracted failures;
- `Chronicler/` — the read-your-writes `Position` token, the event-type mapping (alias ↔ class ↔
  schema version), and the store's contracted failures, concurrency and storage;
- `Clock/` — PSR-20 with the PointInTime guarantee;
- `Message/` — the event taxonomy (`DomainEvent`, `SerializablePayload`), the header SPI, and the
  dual-write-guard exemption marker;
- `Projector/` — the post-commit hook and read-your-writes freshness;
- `Random/` — the jitter a backoff draws its spread from;
- `Serializer/` — the serialization failure and the crypto-shredding key seam;
- `Stream/` — stream validity.

## Wiring

Nothing to wire: there are no services here, only types. Your composition root binds the
implementations (each implementation package registers its own); your code names the contracts.
The one thing you DO register are your own implementations of the callback interfaces — a typed
`AggregateIdentity` per aggregate, `DomainEvent` classes with `#[EventType]` — which the consuming
packages discover through their normal configuration.

## Contributor doctrine — the four load-bearing rules

**1. Contracts depends on nothing** (deptrac: `Contracts: ~`) — only PHP, SPL and PSR types appear
in signatures and typed annotations. That includes the invisible edges: **no `use Storm\<module>\…`
import, even for a docblock `@see`** (reference implementations by FQCN in prose instead), and no
implementation type in a `@throws` — deptrac reads typed annotations, and an idle import is a
loaded trap for the first type-hint that reaches for it. `grep -r "use Storm" src/Contracts` must
match nothing outside `Storm\Contracts\` itself — executable as `Tests/ContractsPurityTest` (six
files had slipped through the manual grep; caught by the Chronicler review). The slippage had a
mechanical cause: pint's `fully_qualified_strict_types` re-imported the symbols from
fully-qualified `@see` tags on every run — fixed in `pint.json` (`phpdoc_tags: []`), and the test
is the tripwire against a config regression.

**2. The placement rule — the vocabulary bounds the layer.** A contract lives here only when it is
expressible in this layer's own vocabulary (Contracts + SPL + PSR). A port whose signature needs a
module's concrete types stays **module-local** — that is why `StreamReader` and `DecisionAppend`
live in Chronicler (their signatures close over `Stream`/`StreamName`) and why every Saga port
stays in Saga. Consumer count does not override vocabulary: a multi-consumer port over module types
stays put (its consumers depend on that module anyway), and a single-consumer contract may sit here
when something in this layer must name it (see rule 3) — `StreamHeadStore` once did without such a
reason, and moved back.

**3. Ports own their exception AS A TYPE.** Each contracted failure is an empty interface here
(`extends Throwable`); the owning module's hierarchy implements it while keeping its SPL base (the
intent axis: RuntimeException = runtime condition, InvalidArgumentException/LogicException = bug).
A port's `@throws` names the contracted type; callers catch domain failures without knowing the
implementing package. These interfaces sit in this layer *because* the ports here name them in
`@throws` — same-layer references pin them. Bugs (LogicException-class) are deliberately NOT
contracted: declaring a bug invites catching it. — Exception to the idiom: a module whose ports are
all module-local (Saga) owns its failure as a local concrete class (`SagaStorageFailure`); hoisting
it here would create the edge rule 1 forbids. Both write and read paths of
`AggregateRepository` contract `StorageFailure`: the translation happens at the repository
boundary, AFTER the write chain's own transient retry has run and given up, so the raw driver
failure stays below the retry that pattern-matches it — wrapping at the source would break the
retry; wrapping at the port cannot.

**4. One failure, no umbrella — a root at two.** A module that surfaces a single contracted failure
extends `Throwable` directly (`ClockExceptionContract`, `SerializationExceptionContract`,
`Stream/InvalidStreamException`) — a family of one needs no root. The *second* externally-named
failure in a module is the trigger to introduce a `<Module>ExceptionContract` root and re-parent
both. `Chronicler` / `Aggregate` show the shape: the root is the OUTER catch net (`catch
(ChroniclerExceptionContract)` = any port failure, log/dead-letter it), the specific child is the
INNER catch for a precise reaction (`ConcurrencyException` for the bounded write-retry, never the
umbrella — which would also swallow the unrecoverable `StorageFailure`).

## Resources

This package is developed in the `chronhub/storm` monorepo; a standalone repository for it is a
READ-ONLY subtree split. Report issues and open pull requests on the monorepo, where the tests,
the architecture gates and the full internal documentation live.

---

*Experimental 0.x: interfaces here change without deprecation cycles — no backward-compatibility
promise and no legacy layer. Pin an exact 0.x tag or commit for reproducibility; pinning fixes
history, not a stable API. Schema changes are resets, not migrations, and a reset destroys data,
so it stays on disposable environments.*
