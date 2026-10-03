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

## 2026-09-24 — attribute class-string contract milestone

- Hardened `Envelope::has()`, `last()`, `all()`, and `without()` so their documented `class-string<EnvelopeAttributeInterface>` contract is enforced at runtime.
- Unknown classes, unrelated classes, and malformed class names now fail explicitly with `InvalidArgumentException` instead of being indistinguishable from a valid query with no matching attributes.
- Polymorphic interface/base-class queries remain supported.
- Verification: PHP-CS-Fixer PASS; PHPStan PASS; PHPUnit coverage PASS (60 tests, 181 assertions); coverage 98.5% lines / 85.4% methods / 97.1% branches; Gating PASS with 70 rules, 0 failures and 0 warnings.

## 2026-09-24 — Symfony service-surface contract milestone

- Verified the compiled standalone container for `EnvelopeCodec`, `EnvelopeJsonCodec`, and the optional `EnvelopeMessengerCodec`.
- Extended bundle regression coverage so `EnvelopeFactory`, `EnvelopeCodec`, and `EnvelopeJsonCodec` must always be registered by the extension.
- `EnvelopeMessengerCodec` remains conditional on the presence of Symfony Messenger, preserving the runtime-optional integration boundary.
- Service visibility remains private/autowired, which is intentional: consumers inject these services rather than fetching them from the container.
- Verification: PHP-CS-Fixer PASS; PHPStan PASS; PHPUnit coverage PASS (60 tests, 184 assertions); Gating PASS with 70 rules, 0 failures and 0 warnings.

## 2026-09-24 — optional Messenger manifest contract milestone

- Added regression coverage across both `composer.json` and `composer.prod.json` proving `symfony/messenger` is not a runtime `require` dependency.
- Messenger remains a `require-dev` dependency for this repository's own bridge tests and a `suggest` entry for consumers.
- This protects Enveloping's runtime independence: installing the package with production dependencies does not force Symfony Messenger.
- Verification: PHP-CS-Fixer PASS; PHPStan PASS; PHPUnit coverage PASS (61 tests, 200 assertions); Gating PASS with 70 rules, 0 failures and 0 warnings.

## 2026-09-24 — symmetric decode ownership milestone

- Hardened `EnvelopeAttributeCodecRegistry::decode()` so wire-type ownership and runtime-attribute ownership are validated symmetrically.
- A codec may decode its wire type only into a runtime attribute that it also declares support for.
- Decoded runtime attributes must not be simultaneously supported by another registered codec; ambiguous decode ownership fails with `EnvelopeCodecException`.
- Added regression coverage for unsupported decoded runtime attributes and ambiguous decoded ownership.
- Verification: PHP-CS-Fixer PASS; PHPStan PASS; PHPUnit coverage PASS (63 tests, 204 assertions); coverage 98.6% lines / 85.4% methods / 97.2% branches; Gating PASS with 70 rules, 0 failures and 0 warnings.

## 2026-09-24 — runtime attribute class-string validation milestone

- Hardened `Envelope::last()`, `all()`, and `without()` so runtime callers must provide a class/interface implementing `EnvelopeAttributeInterface`; `has()` inherits the same validation through `last()`.
- Preserved the strict `class-string<EnvelopeAttributeInterface>` / generic PHPDoc surface for static consumers while retaining defensive validation for dynamic/runtime callers.
- Invalid class names, missing classes, and unrelated classes now fail explicitly with `InvalidArgumentException` instead of silently behaving like an empty lookup/removal.
- Polymorphic interface lookup remains supported, including `EnvelopeAttributeInterface::class` itself.
- Verification: PHP-CS-Fixer PASS; PHPStan PASS; PHPUnit coverage PASS (57 tests, 175 assertions); Gating PASS with 70 rules, 0 failures and 0 warnings; Canon040 reports 98.2% lines / 83.3% methods / 96.7% branches.

## 2026-09-24 — empty JSON payload object round-trip milestone

- Fixed a strict JSON wire asymmetry where an empty attribute payload map (`[]` in PHP) encoded as JSON array `[]` but the decoder correctly required payload wrappers to be JSON objects.
- `EnvelopeJsonCodec` now casts each top-level attribute payload map to an object during JSON encoding, preserving `{}` for empty custom payloads without changing non-empty payload structure.
- Added a custom empty-payload attribute/codec regression proving encode → JSON `{}` → decode round-trip.
- Verification: PHP-CS-Fixer PASS; PHPStan PASS; PHPUnit coverage PASS (58 tests, 177 assertions); Gating PASS with 70 rules, 0 failures and 0 warnings; Canon040 reports 98.5% lines / 85.4% methods / 97.0% branches.

## 2026-09-24 — codec transport type collection validation milestone

- Hardened `EnvelopeAttributeCodecRegistry` so `EnvelopeAttributeCodec::transportTypes()` is runtime-enforced as an ordered `list<string>` instead of relying only on interface PHPDoc.
- Associative transport-type collections and non-string type entries now fail deterministically with `EnvelopeCodecException` rather than leaking incidental `TypeError` or silently accepting malformed codec implementations.
- Stable wire grammar validation remains applied after list/item validation.
- Added negative regression codecs for associative and non-string `transportTypes()` implementations.
- Verification: PHP-CS-Fixer PASS; PHPStan PASS; PHPUnit coverage PASS (60 tests, 181 assertions); Gating PASS with 70 rules, 0 failures and 0 warnings; Canon040 reports 98.5% lines / 85.4% methods / 97.1% branches.

## 2026-09-24 — RC convergence and Canon052 boundary repair

- Reconnaissance covered the current Enveloping README, Composer development/production manifests, Symfony configuration, source/docblocks, tests, quality scripts, tracked documentation, and the local helper/canon contour for Objecting, Cruding, Viewing, Interfacing, Gating, and Canonization.
- Market/OSS comparison used Symfony Messenger stamps, OpenTelemetry context/baggage, and CloudEvents context attributes as external reference points. RC scope remained transport/context correctness and boundary safety; broader tracing/baggage/CloudEvents adapters remain growth work rather than RC requirements.
- Consulted normative Canonization rules included Canon012, Canon018, Canon022, Canon025, Canon029, Canon032, Canon034, Canon038, Canon041, Canon042, Canon043, Canon045, Canon048, Canon052, and Canon053. Target mapping: Canon018/025/029/032/034/038/043/045/052/053 apply; Canon022 explicitly exempts Enveloping from the normal application dependency baseline; Canon041/042 are non-applicable to this headless foundation; Canon048 is non-applicable because Enveloping owns no Doctrine Entity.
- Canon022 therefore takes precedence over the generic application contour: Objecting, Cruding, Viewing, and Interfacing were treated as contract/reference repositories and were not invented as Enveloping runtime dependencies.
- Repaired the Canon052 consumer artifact boundary by moving the copied Gating owner tree out of `.gating/` into ignored `var/cache/enveloping-gating-pollution-rc`; the operation was reversible and preserved only canonical consumer artifact state under `.gating/`.
- Final acceptance exposed an Xdebug-dependent defect in the 512-level JSON-safe nesting guard: recursive validation could hit the runtime stack guard before Enveloping raised its typed transport exception. Replaced recursive traversal with an explicit typed work stack so the declared depth contract is runtime-independent.
- Final executable acceptance: `composer quality` PASS; PHPUnit 56/56 tests with 171 assertions; Canon040 PASS at 98.2% lines / 83.0% methods / 96.6% branches; Canon052 PASS; full Gating PASS with no failed rules.

## 2026-09-24 — explicit context propagation product milestone

- Added explicit parent-to-child propagation to `EnvelopeFactory` without middleware or ambient/global context.
- `create()` remains inherit-none semantics; `inherit()` copies all parent context; `inheritOnly()` copies only caller-selected polymorphic attribute types while preserving parent attribute order.
- Child-specific overrides remain explicit through existing immutable `with()` / `replace()` operations, so Enveloping does not impose domain-specific propagation or cardinality policy.
- Added regression coverage for all/selected/none inheritance, polymorphic selection, invalid selection types, parent immutability, ordering, and explicit child replacement.
- Verification: PHP-CS-Fixer PASS; PHPStan PASS; PHPUnit coverage PASS (69 tests, 215 assertions); coverage 98.6% lines / 86.0% methods / 97.3% branches; Gating PASS with 70 rules, 0 failures and 0 warnings.

## 2026-09-24 — v1 transport compatibility contract milestone

- Added `TRANSPORT_COMPATIBILITY.md` as the normative evolution contract for serialized Envelope version 1.
- Distinguished package SemVer from transport-version evolution and documented exactly which structural/semantic changes require a new wire version.
- Documented deployment responsibility for additive custom attribute types: producers may emit a type only to receivers that own its codec.
- Added a cross-adapter regression proving core DTO, JSON, and Messenger stamp all emit the same `EnvelopeTransportDTO::CURRENT_VERSION`.
- Decoder policy remains explicit exact-version support; multi-version migration is deferred until a real v2 migration exists.
- Verification: PHP-CS-Fixer PASS; PHPStan PASS; PHPUnit coverage PASS (70 tests, 220 assertions); coverage 98.6% lines / 86.0% methods / 97.3% branches; Gating PASS with 70 rules, 0 failures and 0 warnings.

## 2026-09-24 — propagation-to-Messenger integration proof milestone

- Added an integration-level consumer proof covering parent execution context, selective propagation to a child message, explicit child actor replacement, Symfony Messenger transport, and reconstruction on the receiving side.
- The proof verifies that selected `Origin` and `Correlation` context survive the boundary, child-specific `Actor` overrides are preserved, omitted `Causation` does not leak, and the parent Envelope remains immutable.
- This closes the product path without forcing a dependency into a concurrently modified external consumer repository.
- Verification: PHP-CS-Fixer PASS; PHPStan PASS; PHPUnit coverage PASS (71 tests, 228 assertions); coverage 98.6% lines / 86.0% methods / 97.3% branches; Gating PASS with 70 rules, 0 failures and 0 warnings.

## 2026-09-24 — market-facing release package milestone

- Added a root `LICENSE` notice aligned with the Composer-declared `PolyForm-Noncommercial-1.0.0` license and canonical official license URL.
- Added product-facing `CHANGELOG.md`; CMCP orchestration history remains separate from consumer-facing release notes.
- Added installation and quick-start documentation covering standalone use, explicit context propagation, and optional Messenger installation.
- Corrected stale README language from the pre-Messenger phase and documented deterministic codec ownership instead of historical first-match semantics.
- Improved the development and production Composer package descriptions without changing package identity or dependency boundaries.
- Verification: `composer validate --strict --no-check-all` PASS; `composer validate --strict --no-check-all composer.prod.json` PASS; PHP-CS-Fixer PASS; PHPStan PASS; PHPUnit coverage PASS (71 tests, 228 assertions); coverage 98.6% lines / 86.0% methods / 97.3% branches; Gating PASS with 70 rules, 0 failures and 0 warnings.

## 2026-09-24 — executable release-readiness gate milestone

- Added `validate:self` and `release:check` Composer scripts; `release:check` composes strict development/production manifest validation, coding-style verification, PHPStan, coverage-enabled PHPUnit, and canonical Gating.
- Added `RELEASE_CHECKLIST.md` with explicit pre-tag, publication, clean-consumer-install, and wire-version checks.
- Stable tagging/publication remains intentionally separate: no Git remote is configured and no `v1.0.0` tag was created by this milestone.
- All six `release:check` constituent commands passed independently. One PHPStan run initially hit a transient `%TEMP%` cache-write error and passed immediately on isolated rerun; the aggregate Console MCP wrapper did not return a final result on repeated long-form execution, so no project failure is inferred from that wrapper behavior.
- Verified state: both Composer manifests PASS strict validation; PHP-CS-Fixer PASS; PHPStan PASS; PHPUnit coverage PASS (71 tests, 228 assertions); Gating PASS with 70 rules, 0 failures and 0 warnings.

## 2026-09-24 — v1.0.0 release finalization milestone

- Finalized `CHANGELOG.md` with an empty `Unreleased` section and a dated `1.0.0` release section for 2026-09-24.
- Re-ran the full release-candidate acceptance on the final release content: development Composer manifest PASS; production Composer manifest PASS; PHP-CS-Fixer PASS; PHPStan PASS; PHPUnit coverage PASS (71 tests, 228 assertions); Gating PASS with 70 rules, 0 failures and 0 warnings.
- The repository has a configured canonical remote at `git@github.com:smartresponsor/enveloping.git` with `master` tracking `origin/master`.
- This milestone prepares the signed release commit for publication. Signed semantic-version tagging remains a separate Git operation because the current Console MCP registry does not expose tag creation.

## 2026-09-25 — RC transport exception convergence

- Re-read the Enveloping package surface, transport compatibility contract, relevant source/tests, and the mandatory Objecting/Cruding/Viewing/Interfacing, Gating, and Canonization contour before patching.
- Market/OSS comparison confirmed the mature context-propagation baseline: transport metadata is explicit, process-boundary input is validated, and untrusted propagated context must fail at the boundary rather than leak unrelated runtime failures.
- Canon mapping retained Enveloping's explicit Canon022 standalone dependency-baseline exemption and Canon041/042 headless UI exemption; no application-helper runtime dependencies or browser surface were introduced.
- Closed a typed-boundary defect in the built-in attribute decoder: empty `actor.identity`, `origin.source`, `correlation.id`, and `causation.id` wire values now fail as `EnvelopeTransportException` instead of escaping as value-object `InvalidArgumentException`.
- Added regression coverage across all four built-in wire types. The v1 wire shape and documented non-empty built-in invariants are unchanged.
- Final deterministic acceptance: `composer release:check` PASS; Composer dev/prod validation PASS; PHP-CS-Fixer PASS; PHPStan PASS; PHPUnit coverage PASS (72 tests, 232 assertions); coverage 98.63% lines / 86.00% methods / 97.32% branches; Gating PASS with 0 failures and 0 warnings.

## 2026-09-28 — Canon052 artifact-boundary remediation

- Baseline: `master` at `e393c750e92d46adcf42e4a527f64fe8f2365707`, one commit ahead of `origin/master`; CanonScanning reported one hard failure, Canon052, with fresh Inspecting evidence containing three medium observational findings and no analyzer failures.
- Read the current Enveloping package surface plus Canonization/Gating contracts and the Objecting/Cruding/Viewing/Interfacing reference contour. Canonization explicitly keeps Enveloping outside the ordinary application dependency baseline, so no helper runtime dependencies were added.
- Canon052 mapping confirmed Composer integration itself was already canonical; the only failing surface was a copied Gating owner tree exposed under consumer-local `.gating/`.
- Preserved the polluted tree non-destructively by moving it to ignored `var/cache/enveloping-gating-pollution-20260928-093647`, then restored the tracked consumer-only `.gating/README.md`.
- Acceptance: `composer release:check` PASS; Composer dev/prod validation PASS; PHP-CS-Fixer PASS; PHPStan PASS; PHPUnit coverage PASS (72 tests, 232 assertions) with 98.63% lines / 86.00% methods / 97.32% branches; local-dev Gating PASS (9-rule configured subset); post-mutation Inspecting completed with PHPStan 0 errors, Semgrep 0 findings/errors, and the same three non-blocking medium architecture observations. Canon052's concrete current conditions are satisfied: `gating/gate: dev-master`, `../Gating` path repository with `symlink=true`, `gate` + `quality` Composer scripts, packaged production Gating dependency, and consumer `.gating/` restored to artifact-only README state.
- Growth-only observations remain separate: built-in codec type dispatch, JSON decode method decomposition, and potential OpenTelemetry/W3C baggage interop are not RC blockers absent correctness or operability impact.

## 2026-09-30 — autonomous RC canon remediation

- Task: `engine-20260930014336-enveloping-dba7a1`; reconnaissance HEAD `36c5b4286a3d3cce427914140d52ce54f0bc2e40`; branch `master` tracking `origin/master` with no ahead/behind divergence.
- Authoritative upstream evidence fingerprint: `5c969a00f8058a56ab21267f2ac8787ccbb2b5cd75720f0df40962c2366ecdc9`. CanonScanning reported exactly one hard failure: Canon052 Gating integration; fresh Inspecting evidence contained three medium, non-autofixable observations and no high-severity blocker.
- Reconnaissance read the Enveloping README, Composer dev/prod manifests, source/test inventory, existing orchestration journal, and the Objecting/Cruding/Viewing/Interfacing/Gating/Canonization responsibility contour. Canon022 explicitly exempts Enveloping from the ordinary standalone application dependency baseline; Canon041/042 explicitly exempt its headless debug runtime from browser/UI tooling and behavioral-UI coverage.
- Normative canon consulted: `Canon022StandaloneApplicationDependencyBaselineRule.md`, `Canon041BehavioralUiTestToolingRule.md`, `Canon042BehavioralUiCoverageRule.md`, and `Canon052GatingIntegrationRule.md`. Canon052 maps Enveloping to a dev `../Gating` symlink + `dev-master`, packaged production Gating dependency, Composer `gate` in aggregate `quality`, and an artifact-only consumer `.gating/`.
- Market/OSS baseline: Symfony Messenger uses Envelopes/Stamps for transport/message metadata; OpenTelemetry defines immutable execution context and explicit cross-process propagation. Enveloping's current explicit immutable context/wire-codec model is aligned with those mature patterns without importing tracing, ambient global context, or consumer-domain semantics.
- RC-critical workstream: restore Canon052 consumer artifact topology without changing Enveloping runtime semantics. Growth workstream remains post-RC: optional interoperability adapters (for example W3C/OpenTelemetry context/baggage) and internal codec decomposition only when justified by concrete consumers or maintainability pressure.
- Material risk identified: CanonScanning had copied the Gating owner repository into consumer `.gating/`, producing owner code/policy under an artifact-only surface. The polluted directory was preserved non-destructively at ignored `var/cache/enveloping-gating-pollution-20260930-014336`; canonical consumer `.gating/README.md` was then restored.
- Acceptance: `composer release:check` PASS end-to-end; Composer dev/prod validation PASS; PHP-CS-Fixer 0 fixable files; PHPStan 0 errors; PHPUnit/coverage PASS (72 tests, 232 assertions); local Gating PASS (9 configured rules, 0 failed, 0 warnings); Composer audit PASS with no advisories.
- Post-remediation Inspecting PASS as an executed analyzer run: PHPStan 0 errors, Semgrep 0 findings/engine errors, and the same three medium non-autofixable structure observations (built-in codec type dispatch x2; JSON decode long-method) as the supplied baseline.
- Canon052 current-state evidence: `.gating/README.md` is the non-executable consumer artifact notice; `.gating/AGENTS.md` and `.gating/bin/gating` are absent; development/production Composer integration remains canonical. The full CanonScanning producer was not rerun because prior journal evidence shows it copies the Gating owner tree into target `.gating/` before checking and would recreate the prohibited state; the repository-owned release gate plus direct topology inspection are the non-polluting acceptance path.
- No browser/mobile UI, navigation, forms, interactions, or user flows changed. Canon041/042 explicitly exempt Enveloping, so runtime restart, Playwright/Panther, and screenshots are not applicable.
- Final tracked change before integration is only this orchestration journal; the Canon052 remediation itself is workspace hygiene restoring tracked `.gating/README.md` to HEAD while preserving the producer-injected owner tree under ignored `var/cache`.

## 2026-09-30 — engine-20260930205842 current-state RC verification

- Baseline HEAD: `4ec2e74dbe139aba40018b228fd7954f6ce7d1a8` on `master`, tracking `origin/master` at 0 ahead / 0 behind before this journal update. Pre-existing worktree state contained only a deletion of `.gating/README.md`; it was not overwritten or staged because Canon052 permits the consumer artifact surface to be absent and the deletion is not required for the current remediation.
- Authoritative task specification and supplied CanonScanning evidence were read in full. The supplied RED report (2026-09-29 fingerprint `5c969a00f8058a56ab21267f2ac8787ccbb2b5cd75720f0df40962c2366ecdc9`) identified Canon052 owner-code pollution under consumer `.gating/`; the current repository journal records that pollution as already remediated and verified on 2026-09-28 and earlier on 2026-09-30.
- Normative canon consulted: Canonization `Canon052GatingIntegrationRule.md`, architecture README and guard matrix; executable enforcement consulted in Gating `Canon052GatingIntegrationRule.php`, Gating README/composer metadata, and the consumer artifact-boundary documentation. Current mapping remains: `gating/gate: dev-master`, dev `../Gating` path repository with `symlink=true`, production packaged Gating dependency, `gate` composed into `quality`, and no copied executable/policy tree in consumer `.gating/`.
- Related contour re-checked: Objecting, Cruding, Viewing, and Interfacing responsibility/Composer contracts. Enveloping remains the Canon022 optional cross-cutting foundation exemption; no Objecting/Cruding/Viewing/Interfacing runtime dependency was added because doing so would conflict with Enveloping's headless context-envelope responsibility and the current canonical exemption.
- Market/OSS comparison: Symfony Messenger uses Envelopes/Stamps for message metadata; OpenTelemetry defines immutable execution context plus explicit propagators/baggage across process boundaries. Enveloping's immutable envelope + explicit codec/transport boundary remains aligned. Growth-only interop with W3C/OpenTelemetry baggage/propagation stays outside RC unless demanded by a concrete consumer.
- RC-critical workstream outcome: no new runtime patch was justified. The historical Canon052 defect is not present in the current executable gate state, and modifying Enveloping runtime to address an already-remediated Gating-topology issue would be scope drift.
- Fresh deterministic acceptance on the current tree: `composer gate` PASS (9 configured rules, 0 failed, 0 warnings); `composer release:check` PASS end-to-end; both Composer manifests valid; PHP-CS-Fixer reports 0 fixable files; PHPStan reports 0 errors; PHPUnit coverage run PASS (72 tests, 232 assertions).
- Inspecting baseline remains three medium, non-autofixable observations (built-in codec type dispatch x2 and JSON decode method size), with no analyzer failures. No source/runtime mutation occurred in this execution window, so a duplicate Inspecting run was not required.
- UI/runtime applicability: Enveloping is headless and Canon041/042-exempt; no browser/mobile UI, navigation, form, or user flow changed. Runtime restart and visual screenshots are not applicable.

## 2026-10-03 — engine-20261003180232 current-state RC acceptance

- Baseline: `master` at `8bd0a587f731268c3c2d46209a8986ec77c4655a`, tracking `origin/master` at 0 ahead / 0 behind. Pre-existing worktree state is only deletion of `.gating/README.md`; it is preserved as user-owned state because Canon052 allows the consumer artifact directory to be absent and this task does not require restoring it.
- Read the authoritative task specification, current Enveloping README/dev+prod Composer manifests, transport compatibility contract, relevant codec/factory source and tests, supplied CanonScanning RED report, and fresh Inspecting evidence. The supplied RED fingerprint `5c969a00f8058a56ab21267f2ac8787ccbb2b5cd75720f0df40962c2366ecdc9` reports historical Canon052 pollution: a copied Gating owner tree inside consumer `.gating/`.
- Canonization mapping: consulted `Canon052GatingIntegrationRule.md` plus the guard matrix and executable `Canon052GatingIntegrationRule.php`. Current Composer contract remains canonical: `gating/gate: dev-master`, development `../Gating` path repository with `symlink=true`, `gate` included in aggregate `quality`, and production Gating supplied through a VCS repository rather than a local path repository. Current `.gating/` contains no copied executable/policy tree.
- Related contour read: Objecting, Cruding, Viewing, and Interfacing README/Composer contracts; Gating and Canonization owner contracts. Enveloping remains the Canon022 optional cross-cutting foundation and must not acquire those application-helper runtime dependencies merely to satisfy the generic application contour.
- Market/OSS baseline: Symfony Messenger keeps message metadata in Envelope stamps; OpenTelemetry defines immutable execution Context plus explicit propagators/baggage and warns about trusting inbound propagated context. Enveloping's immutable typed context, explicit inheritance, strict transport validation, and adapter boundary align with mature practice. Growth-only work remains optional W3C/OpenTelemetry propagation interoperability and internal codec/decode decomposition; it does not block this RC.
- RC-critical workstream selected: prove that the stale Canon052 RED is no longer present in the actual repository topology and deterministic gates, without mutating runtime semantics or overwriting the pre-existing `.gating/README.md` deletion. Inspecting's three medium findings (built-in type dispatch x2; JSON decode size) remain observational and non-blocking absent a canon/gate escalation.
- Gates to run after this journal mutation: `composer release:check`, post-mutation Inspecting, Git diff/status/branch verification, and publication reconciliation if the resulting tracked journal change is coherent and push is authorized.
- Runtime capacity admitted light checks but initially deferred the aggregate heavy `release:check`; its deterministic constituents were therefore executed individually: `validate:self` PASS, `validate:prod` PASS, PHP-CS-Fixer PASS with 0 fixable files, PHPStan PASS with 0 errors, PHPUnit path coverage PASS (72 tests, 232 assertions), and `composer gate` PASS (10 rules, 0 failed, 0 warnings). This is equivalent evidence for the configured release script constituents without restarting any runtime.
- Post-mutation Inspecting PASS as an analyzer execution: PHPStan 0 errors, Semgrep 0 findings/errors, and exactly the same three medium non-autofixable observations as the supplied baseline (built-in codec dispatch x2; JSON decode method size). No high-severity or executable correctness blocker emerged.
- UI/runtime applicability remains unchanged: Enveloping is headless and Canon041/042-exempt; no browser/mobile UI, navigation, form, or user flow changed, so managed runtime restart, Panther/Playwright, and screenshots are not applicable.

## 2026-10-03 — engine-20261003181040 stale-Canon052 RC verification

- Baseline reconnaissance: `master`; pre-existing worktree state is only deletion of `.gating/README.md`, which is preserved and not adopted by this task. The authoritative supplied RED fingerprint `5c969a00f8058a56ab21267f2ac8787ccbb2b5cd75720f0df40962c2366ecdc9` identifies historical Canon052 Gating-owner pollution under consumer `.gating/`; supplied Inspecting evidence has three medium observational findings and no analyzer failure.
- Read current Enveloping README, dev/prod Composer manifests, release/transport contracts, source/test inventory, and prior orchestration history; read the Objecting, Cruding, Viewing, and Interfacing README/Composer responsibility contour plus Gating owner contract and executable Canon052 mirror.
- Normative Canonization consulted: `Canon022StandaloneApplicationDependencyBaselineRule.md`, `Canon041BehavioralUiTestToolingRule.md`, `Canon042BehavioralUiCoverageRule.md`, `Canon052GatingIntegrationRule.md`, and the architecture guard matrix. Mapping: Canon022 explicitly exempts Enveloping from the application dependency baseline; Canon041/042 explicitly exempt its headless debug runtime; Canon052 requires dev `gating/gate: dev-master`, `../Gating` with `symlink=true`, packaged production Gating, aggregate `quality` including `@gate`, and artifact-only-or-absent consumer `.gating/`.
- Market/OSS baseline retained from repository evidence: Symfony Messenger envelope/stamp metadata and OpenTelemetry immutable context/explicit propagation are the relevant mature patterns. RC-critical workstream is deterministic proof that the stale Canon052 failure is absent from current topology without changing runtime semantics or overwriting the pre-existing `.gating/README.md` deletion. Growth workstream remains optional W3C/OpenTelemetry interop and internal codec/decode decomposition; it is not an RC blocker.
- Material risks: accidentally reintroducing copied Gating owner code into `.gating/`, inventing Objecting/Cruding/Viewing/Interfacing runtime dependencies contrary to Canon022, or converting Inspecting's observational medium findings into speculative RC changes.
- Gates selected: current `composer gate`, complete `release:check` (or deterministic constituents if wrapper capacity blocks), post-journal Inspecting because this task mutates the repository journal, then final Git status/diff/branch/upstream reconciliation and publication where safe.
- Current deterministic acceptance: Composer manifest validation PASS; PHPStan PASS with 0 errors; PHPUnit PASS (72 tests, 232 assertions); repository-wide tracked PHP lint PASS; `git diff --check` PASS. Post-journal Inspecting completed with PHPStan 0 errors, Semgrep 0 findings/errors, and the same three medium non-autofixable observations as the supplied baseline (built-in codec dispatch x2; JSON decode method size), so no new architecture regression or RC-blocking analyzer failure appeared.
- Canon052 topology check: no `App\\Gating` owner-runtime namespace is present anywhere in the current Enveloping scan. Development and production Composer manifests still express the required Gating identities/resolution model, and the only pre-existing `.gating/` worktree change remains deletion of the optional consumer README artifact.
- `composer gate` and aggregate `composer release:check` were attempted repeatedly through the Console MCP Composer execution path but were not started because shared runtime capacity was `ADMIT_LIGHT_ONLY` under `RESOURCE_PRESSURE_WATCH` / `ENGINE_BACKLOG_HIGH`. This is an execution-admission constraint, not a project test failure; the available deterministic constituents above were executed successfully without restarting runtime.
- Market/maturity check was refreshed against current Symfony Messenger and OpenTelemetry documentation: mature patterns continue to separate message payload from envelope/stamp metadata, use immutable execution context with explicit propagation, and treat inbound/outbound propagated context as a trust boundary. No RC-critical capability gap was identified inside Enveloping's responsibility; W3C/OpenTelemetry interoperability remains a growth item.
- UI/runtime applicability: no source/UI/navigation/form/interaction behavior changed. Enveloping remains Canon041/042-exempt, so browser runtime restart, Panther/Playwright execution, and screenshots are not applicable to this verification.

## 2026-10-03 — engine-20261003181850 Canon052 RC verification

- Baseline reconnaissance: `master`; pre-existing worktree state is only deletion of `.gating/README.md`, preserved as user-owned state and not adopted by this task. The supplied CanonScanning fingerprint `5c969a00f8058a56ab21267f2ac8787ccbb2b5cd75720f0df40962c2366ecdc9` reports historical Canon052 consumer `.gating/` pollution; supplied Inspecting evidence contains three medium observational findings and no analyzer failure.
- Read current Enveloping README, dev/prod Composer manifests, transport compatibility contract, relevant codec source, existing orchestration history, and the Objecting/Cruding/Viewing/Interfacing/Gating/Canonization contract contour. Interfacing has no root `MANIFEST.json`; that absence was verified rather than inferred.
- Normative Canonization consulted: `Canon022StandaloneApplicationDependencyBaselineRule.md` and `Canon052GatingIntegrationRule.md`, plus Canonization `AGENTS.md`/README/MANIFEST. Mapping: Enveloping is explicitly exempt from the ordinary application dependency baseline; Canon052 requires development `gating/gate: dev-master`, `../Gating` with `symlink=true`, packaged production Gating, `gate` inside aggregate `quality`, and an artifact-only or absent consumer `.gating/` surface.
- Market/OSS baseline refreshed against Symfony Messenger Envelopes/Stamps, OpenTelemetry PHP context propagation, and W3C Baggage. Mature practice separates execution/transport metadata from business payloads, uses explicit propagation, and treats propagated context as a trust boundary. Enveloping's immutable typed context and strict wire codec remain aligned; W3C/OpenTelemetry interoperability stays growth-only.
- RC-critical workstream: verify current Canon052 topology and deterministic release gates without altering runtime semantics or consuming the pre-existing `.gating/README.md` deletion. Growth workstream: optional interoperability adapters and internal codec/decode decomposition; Inspecting's repeated-type dispatch and long-method observations do not independently block RC.
- Material risks: reintroducing Gating owner code under consumer `.gating/`, inventing application-helper runtime dependencies contrary to Canon022, or speculative source refactors unrelated to the RED cause.
- Gates selected: current Gating, release-check constituents, post-journal Inspecting when available/applicable, and final Git status/diff/branch/upstream reconciliation with publication when safe.
- Acceptance: `composer gate` PASS with 10 rules, 0 failed and 0 warnings; full `composer release:check` PASS; Composer dev/prod manifests valid; PHP-CS-Fixer found 0 fixable files; PHPStan reported 0 errors; PHPUnit coverage PASS (72 tests, 232 assertions).
- Post-journal Inspecting completed successfully with PHPStan 0 errors and Semgrep 0 findings/errors. It reproduced exactly the supplied three medium non-autofixable observations (built-in codec repeated-type dispatch x2; JSON decode long method), so no new RC-blocking analyzer regression emerged.
- Git integration baseline after verification: `master` at `8559e51d18e6b5988acb4d6582e1d6c0896bc6c8`, tracking `origin/master` at 0 ahead / 0 behind. Dirty paths are the pre-existing `.gating/README.md` deletion plus this task's `CMCP_CHANGELOG.md` update; only the journal is eligible for this task's commit.



