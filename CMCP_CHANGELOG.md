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
- Verification: PHPUnit PASS (11 tests, 32 assertions); coverage PASS at 97.5% lines / 94.1% methods / 96.8% branches; PHPStan PASS; PHP-CS-Fixer PASS; Gating PASS with 0 failures and 0 warnings.
