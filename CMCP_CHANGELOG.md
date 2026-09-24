# CMCP orchestration journal

## 2026-09-23 — repository bootstrap

- Created the standalone Enveloping Git repository under the workspace root.
- Consulted the current Objecting package surface for Symfony 8.1 / PHP 8.4 packaging conventions without importing Objecting responsibilities.
- Selected a transport-agnostic milestone: immutable Envelope mechanics, typed contextual attributes, a deliberately small generic vocabulary, Symfony Bundle/DI integration, and unit tests.
- Explicitly excluded Doctrine persistence, CRUD, Symfony Messenger coupling, consumer-specific attributes, domain-component knowledge, and arbitrary JSON metadata bags.
- Material risks: premature vocabulary growth and accidental coupling to consumers.
- Gates run: Composer install PASS; composer validate --strict PASS; PHPStan level=max PASS; PHPUnit PASS (4 tests, 11 assertions); final Git status/diff inspection pending commit.
- Acceptance state: initial transport-agnostic Enveloping core is implementation-complete for the bootstrap milestone; remote publication remains intentionally pending.

## 2026-09-23 — canonical dual-runtime milestone

- Read Canon025, Canon029, Canon032, Canon034, Canon043, Canon045, and Canon052 before expanding the skeleton.
- Added standalone Symfony runtime surfaces (`Kernel`, `bin/console`, `config/bundles.php`, minimal Framework config) while preserving reusable Bundle composition.
- Added canonical Gating development integration and production Composer manifest without adding CRUD/UI/domain platform dependencies.
- Moved contextual primitives into canonical `ValueObject` / `ValueObjectInterface` technical roots and applied the `Envelope*` subject prefix required by package identity.
- Kept the core transport-agnostic: no Doctrine, CRUD, Messenger, Shipping, Payment, Messaging, Delivering, or Notifying dependency was introduced.
- Verified real standalone boot with `debug:container App\\Enveloping\\Factory\\EnvelopeFactory`.
- Verification: Composer dev/prod validation PASS; PHP-CS-Fixer PASS; PHPStan PASS; PHPUnit PASS (7 tests, 20 assertions); coverage PASS at 97.4% lines / 86.7% methods / 93.9% branches; Gating PASS with 0 failures and 0 warnings.

## 2026-09-23 — polymorphic context semantics milestone

- Reworked Envelope storage into an ordered attribute list so lookup/removal can honor PHP polymorphism rather than exact-class indexing.
- Added `has()`, explicit `replace()`, and immutable `withSubject()` semantics.
- Preserved multi-value attributes by default; cardinality remains a caller/use-case decision rather than package-global knowledge.
- `withSubject()` provides explicit context propagation without making subjects depend on Enveloping or introducing automatic propagation policy.
- Added regression coverage for polymorphic contract lookup/removal, replacement semantics, subject rebinding, and iterable normalization.
- Verification after context semantics: PHPUnit PASS (11 tests, 32 assertions); coverage PASS at 97.5% lines / 94.1% methods / 96.8% branches; PHPStan PASS; PHP-CS-Fixer PASS; Gating PASS with 0 failures and 0 warnings.
- Added minimal invariants for the built-in generic attribute vocabulary: zero-length actor/origin/correlation/causation values are rejected while non-empty values remain opaque and unnormalized.
- Final verification: PHPUnit PASS (12 tests, 37 assertions); PHP coverage 100% lines / 100% methods / 100% branches; PHPStan PASS; PHP-CS-Fixer PASS; Gating PASS with 0 failures and 0 warnings.

## 2026-09-23 — process-boundary codec milestone

- Read Canon012 and Canon048 before introducing serialization mechanics.
- Added transport DTOs for Envelope context while keeping dynamic data confined to the generic serialization boundary.
- Added `EnvelopeCodec`, `EnvelopeAttributeCodec`, `EnvelopeBuiltInAttributeCodec`, and `EnvelopeAttributeCodecRegistry`.
- Subject encoding/decoding remains explicitly owned by the composition layer; Enveloping does not serialize domain objects or Doctrine entities by itself.
- Attribute codecs are extensible through the `enveloping.attribute_codec` Symfony service tag, so consumer-defined contextual attributes can participate without changing Envelope core.
- Verified standalone DI with `debug:container App\\Enveloping\\Codec\\EnvelopeCodec`.
- Verification: PHPUnit PASS (17 tests, 53 assertions); PHPStan PASS; PHP-CS-Fixer PASS; coverage PASS at 95.9% lines / 86.2% methods / 89.2% branches; Gating PASS with 0 failures and 0 warnings.

## 2026-09-23 — stable wire contract milestone

- Removed PHP FQCNs from attribute transport identity; built-in wire types are now stable semantic identifiers: `actor`, `origin`, `correlation`, and `causation`.
- Split codec capability checks into runtime-attribute support and stable transport-type support.
- Added `EnvelopeTransportDTO::CURRENT_VERSION = 1` and explicit rejection of unsupported transport versions.
- Custom attribute codecs own their transport type identifiers without modifying Enveloping core.
- Verification: PHPUnit PASS (19 tests, 57 assertions); PHPStan PASS; PHP-CS-Fixer PASS; coverage PASS at 96.1% lines / 83.3% methods / 89.1% branches; Gating PASS with 0 failures and 0 warnings.
