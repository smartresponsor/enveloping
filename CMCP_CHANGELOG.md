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

## 2026-09-24 — optional Symfony Messenger bridge milestone

- Verified the current Symfony Messenger Envelope/Stamp model before implementing the bridge.
- Added `EnvelopeContextStamp` as the only Messenger-specific context carrier; the underlying business message remains unchanged.
- Added `EnvelopeMessengerCodec` to translate between Enveloping and Symfony Messenger Envelopes using the stable versioned attribute wire contract.
- Kept `symfony/messenger` optional at runtime: it is suggested by the package and installed only in development; Messenger services load conditionally when `StampInterface` is available.
- Added subject-prefixed `config/envelope_messenger.yaml` and verified real container wiring with `debug:container App\\Enveloping\\Codec\\EnvelopeMessengerCodec`.
- Added tests for round-trip context, missing stamps, invalid subjects, invalid versions, stamp serialization, and transport attribute reconstruction.
- Verification: Composer dev/prod validation PASS; PHPUnit PASS (25 tests, 74 assertions); PHPStan PASS; PHP-CS-Fixer PASS; coverage PASS at 97.3% lines / 86.1% methods / 91.1% branches; Gating PASS with 0 failures and 0 warnings.

## 2026-09-24 — deterministic codec ownership milestone

- Replaced dynamic `supportsType()` probing with explicit `transportTypes()` ownership declarations on `EnvelopeAttributeCodec`.
- `EnvelopeAttributeCodecRegistry` now indexes stable wire types once and rejects empty or duplicate ownership instead of silently using first-wins decode behavior.
- Added automatic Symfony DI tagging for every `EnvelopeAttributeCodec` implementation so consumers can contribute codecs without manual tag boilerplate.
- Added regression coverage for duplicate/empty transport type ownership and DI autoconfiguration.
- Verification: PHPUnit PASS (28 tests, 80 assertions); PHPStan PASS; PHP-CS-Fixer PASS; coverage PASS at 97.5% lines / 86.1% methods / 92.1% branches; Gating PASS with 0 failures and 0 warnings.

## 2026-09-24 — Messenger stamp boundary validation milestone

- Hardened `EnvelopeContextStamp` so transport payload shape is validated at construction instead of trusted through PHPDoc alone.
- Stamp attributes must be a list of entries with non-empty type IDs and scalar/null payload values under string keys.
- Malformed list structure, entry shape, type, payload container, payload keys, and payload values fail fast with explicit exceptions.
- Preserved native serializability and transport DTO reconstruction behavior.
- Verification: PHPUnit PASS (29 tests, 86 assertions); PHPStan PASS; PHP-CS-Fixer PASS; coverage PASS at 97.8% lines / 86.1% methods / 93.3% branches; Gating PASS with 0 failures and 0 warnings.

## 2026-09-24 — Messenger context composition milestone

- Extended `EnvelopeMessengerCodec` to compose Enveloping context onto an existing Symfony Messenger Envelope.
- `withContext()` removes prior `EnvelopeContextStamp` values, preserves all unrelated Messenger stamps, and adds at most one fresh Enveloping context stamp.
- Empty context explicitly clears Enveloping stamps without creating an empty transport marker.
- `withoutContext()` removes only Enveloping context.
- Context replacement requires exact business-message object identity to prevent attaching context to the wrong Messenger message.
- Verification: PHPUnit PASS (34 tests, 98 assertions); PHPStan PASS; PHP-CS-Fixer PASS; coverage PASS at 97.4% lines / 84.2% methods / 93.0% branches; Gating PASS with 0 failures and 0 warnings.

## 2026-09-24 — symmetric codec ownership milestone

- Hardened `EnvelopeAttributeCodecRegistry` so runtime attribute encoding must resolve to exactly one supporting codec instead of first-wins behavior.
- Registry now validates that a codec emits only transport types declared by its own `transportTypes()` contract.
- Preserved unique transport type ownership on decode, making encode/decode ownership symmetric and deterministic.
- Added regression coverage for ambiguous runtime ownership and undeclared emitted wire types.
- Verification: PHPUnit PASS (36 tests, 102 assertions); PHPStan PASS; PHP-CS-Fixer PASS; coverage PASS at 97.1% lines / 84.2% methods / 93.3% branches; Gating PASS with 0 failures and 0 warnings.

## 2026-09-24 — recursive JSON-safe payload milestone

- Expanded attribute transport payloads from flat scalar/null maps to recursive JSON-safe structures: null, scalar values, lists, and string-key maps.
- Added `EnvelopeTransportPayloadValidator` as the shared boundary validator/normalizer used by both `EnvelopeAttributeTransportDTO` and `EnvelopeContextStamp`.
- Transport payload maps are normalized to string-key maps before storage; objects, resources, and non-string map keys are rejected.
- Added regression coverage for nested map/list payloads, native stamp serialization, object values, and invalid map keys.
- Verification: PHPUnit PASS (39 tests, 108 assertions); PHPStan PASS; PHP-CS-Fixer PASS; coverage PASS at 97.3% lines / 85.0% methods / 93.9% branches; Gating PASS with 0 failures and 0 warnings.

## 2026-09-24 — JSON wire adapter milestone

- Added `EnvelopeJsonCodec` as a non-Messenger adapter over the existing versioned `EnvelopeTransportDTO` contract.
- Subject encoding and decoding remain caller-owned; the adapter rejects encoded subjects that are not recursively JSON-safe.
- JSON decoding preserves object-vs-array semantics by parsing objects as `stdClass` before recursive normalization, preventing `{}` from being silently treated as `[]`.
- Envelope document shape, version, attributes list, attribute object shape, type IDs, and payload object shape are validated before reconstruction.
- Registered `EnvelopeJsonCodec` in the reusable Symfony service surface.
- Verification: PHPUnit PASS (44 tests, 126 assertions); PHPStan PASS; PHP-CS-Fixer PASS; coverage PASS at 97.9% lines / 86.7% methods / 94.8% branches; Gating PASS with 0 failures and 0 warnings.

## 2026-09-24 — closed wire wrapper schema milestone

- Hardened the versioned JSON document so only `version`, `subject`, and `attributes` are accepted at the Envelope wrapper level.
- Attribute wrappers in JSON and Messenger stamps now accept exactly `type` and `payload`; unknown structural fields fail fast.
- Built-in attribute payloads now enforce their exact owned key (`identity`, `source`, or `id`) instead of ignoring extra fields.
- Custom attribute payload schema remains fully codec-owned; the shared transport layer continues to enforce only recursive JSON-safe value semantics.
- Verification: PHPUnit PASS (44 tests, 130 assertions); PHPStan PASS; PHP-CS-Fixer PASS; coverage PASS at 97.7% lines / 82.6% methods / 94.1% branches; Gating PASS with 0 failures and 0 warnings.

## 2026-09-24 — typed boundary exception milestone

- Added `EnvelopeTransportException` for malformed or unsupported versioned transport data and `EnvelopeCodecException` for codec ownership/configuration failures.
- JSON encode/decode now wraps native `JsonException` failures into `EnvelopeTransportException` while retaining the original exception as `previous`.
- Transport DTO, payload validator, JSON adapter, Messenger context stamp, Messenger version checks, and built-in attribute decoding expose typed transport failures.
- Codec registry duplicate/empty type ownership, ambiguous runtime ownership, unsupported attribute/type ownership, and undeclared emitted type failures now expose `EnvelopeCodecException`.
- Deliberate caller misuse outside transport corruption remains ordinary `InvalidArgumentException`.
- Verification: PHP-CS-Fixer PASS; PHPStan PASS; PHPUnit coverage PASS (45 tests, 132 assertions); coverage 97.7% lines / 82.6% methods / 94.2% branches; Gating PASS with 0 failures and 0 warnings.

## 2026-09-24 — transport DTO collection validation milestone

- Hardened `EnvelopeTransportDTO` so its attribute collection is runtime-validated instead of relying on PHPDoc alone.
- Attributes must be an ordered list and every item must be an `EnvelopeAttributeTransportDTO`; associative collections and arbitrary values fail fast with `EnvelopeTransportException`.
- Preserved the public wire version and subject contract.
- Verification: PHP-CS-Fixer PASS; PHPStan PASS; PHPUnit coverage PASS (47 tests, 136 assertions); coverage 97.8% lines / 82.6% methods / 94.4% branches.
- Gating is currently blocked only by Canon052 because the pre-existing/concurrently modified `.gating/` consumer tree is no longer considered artifact-only by the current Gating rule. `.gating/README.md` was already dirty before this milestone and was deliberately preserved.

## 2026-09-24 — complete JSON-safe value validation milestone

- Tightened `EnvelopeTransportPayloadValidator` so “JSON-safe” now matches real JSON constraints instead of only PHP scalar/array shapes.
- Non-finite floats (`NAN`, `INF`, `-INF`) are rejected before adapter-specific serialization.
- Strings must be valid UTF-8.
- Recursive payload traversal is bounded at 512 levels, preventing runaway recursion/cyclic-reference style failure modes from escaping the transport boundary.
- JSON subject encoding benefits from the same validator and now rejects non-finite subjects before native `json_encode()` is invoked.
- Verification: PHP-CS-Fixer PASS; PHPStan PASS; PHPUnit coverage PASS (51 tests, 144 assertions); coverage 97.24% lines / 78.26% methods / 93.55% branches.
- Canon052 remains the only known Gating blocker because of the preserved pre-existing `.gating/` consumer-tree state; no `.gating` files were changed by this milestone.

## 2026-09-24 — stable wire type grammar milestone

- Added `EnvelopeTransportTypeValidator` as the shared grammar check for stable attribute wire identifiers.
- Wire type IDs must match `^[a-z][a-z0-9._-]*$`, preserving current built-in and custom examples while rejecting whitespace, uppercase, path-like, and ad-hoc punctuation forms.
- `EnvelopeAttributeTransportDTO`, `EnvelopeAttributeCodecRegistry`, and `EnvelopeContextStamp` now enforce the same grammar with boundary-appropriate typed exceptions.
- PHPStan assertion metadata exposes the validator's non-empty-string guarantee without duplicating runtime checks.
- Verification: PHP-CS-Fixer PASS; PHPStan PASS; PHPUnit coverage PASS (53 tests, 164 assertions); coverage 98.17% lines / 82.98% methods / 96.43% branches.
- Full Gating PASS: 70 rules, 0 failures, 0 warnings. The previously observed Canon052 consumer-artifact blocker was resolved by the concurrent tooling state; this milestone did not modify `.gating`.

## 2026-09-24 — runtime collection contract validation milestone

- Hardened the core `Envelope` constructor so arbitrary iterable items cannot silently enter the typed attribute list; non-attribute values fail as caller misuse with `InvalidArgumentException`.
- Hardened `EnvelopeAttributeCodecRegistry` so every iterable item must implement `EnvelopeAttributeCodec`, with invalid registrations exposed as `EnvelopeCodecException`.
- Hardened `EnvelopeContextStamp::fromTransportAttributes()` so transport attributes must be an ordered list of `EnvelopeAttributeTransportDTO`; malformed collections fail with `EnvelopeTransportException` rather than incidental closure/type errors.
- Added regression coverage for all three runtime collection boundaries.
- Verification: PHP-CS-Fixer PASS; PHPStan PASS; PHPUnit coverage PASS (56 tests, 171 assertions); Gating PASS with 70 rules, 0 failures and 0 warnings.

## 2026-09-24 — RC convergence and Canon052 boundary repair

- Reconnaissance covered the current Enveloping README, Composer development/production manifests, Symfony configuration, source/docblocks, tests, quality scripts, tracked documentation, and the local helper/canon contour for Objecting, Cruding, Viewing, Interfacing, Gating, and Canonization.
- Market/OSS comparison used Symfony Messenger stamps, OpenTelemetry context/baggage, and CloudEvents context attributes as external reference points. RC scope remained transport/context correctness and boundary safety; broader tracing/baggage/CloudEvents adapters remain growth work rather than RC requirements.
- Consulted normative Canonization rules included Canon012, Canon018, Canon022, Canon025, Canon029, Canon032, Canon034, Canon038, Canon041, Canon042, Canon043, Canon045, Canon048, Canon052, and Canon053. Target mapping: Canon018/025/029/032/034/038/043/045/052/053 apply; Canon022 explicitly exempts Enveloping from the normal application dependency baseline; Canon041/042 are non-applicable to this headless foundation; Canon048 is non-applicable because Enveloping owns no Doctrine Entity.
- Canon022 therefore takes precedence over the generic application contour: Objecting, Cruding, Viewing, and Interfacing were treated as contract/reference repositories and were not invented as Enveloping runtime dependencies.
- Repaired the Canon052 consumer artifact boundary by moving the copied Gating owner tree out of `.gating/` into ignored `var/cache/enveloping-gating-pollution-rc`; the operation was reversible and preserved only canonical consumer artifact state under `.gating/`.
- Final acceptance exposed an Xdebug-dependent defect in the 512-level JSON-safe nesting guard: recursive validation could hit the runtime stack guard before Enveloping raised its typed transport exception. Replaced recursive traversal with an explicit typed work stack so the declared depth contract is runtime-independent.
- Final executable acceptance: `composer quality` PASS; PHPUnit 56/56 tests with 171 assertions; Canon040 PASS at 98.2% lines / 83.0% methods / 96.6% branches; Canon052 PASS; full Gating PASS with no failed rules.

