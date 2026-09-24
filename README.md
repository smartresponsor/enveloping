# Enveloping

Enveloping is a Symfony-oriented foundation component for attaching typed contextual semantics to a subject without changing that subject's intrinsic state.

## Canonical responsibility

Enveloping owns how execution context is attached. It does not own the business meaning or lifecycle of consumer objects.

    Object      -> what exists
    Operation   -> what is being done
    Envelope    -> in what context it is being done

An enveloped subject can be an entity, command, event, message, query, operation, reference, or any other PHP value. The subject does not implement an Enveloping interface and does not need a dependency on this package.

## Dependency boundary

Enveloping must remain independent of consumer components. Shipping, Payment, Messaging, Notifying, Delivering and other domain components can operate without Enveloping. The host/application composition layer may place their objects or operations into an Envelope when contextual semantics are required.

Enveloping does not know which attributes a specific consumer supports.

## Typed contextual attributes

Attributes are typed objects, not an arbitrary JSON metadata bag. The initial cross-component vocabulary is deliberately small: Actor, Origin, Correlation and Causation.

Domain-specific facts such as a payment provider, shipping carrier, message recipient, or product state remain in their owning domain or operation.

## Persistence boundary

The core is ephemeral and persistence-agnostic. It owns no Doctrine entities, migrations, CRUD surface, audit store, or execution-history database.

Symfony Messenger stamps may later adapt Envelope attributes, but symfony/messenger is intentionally not a core dependency.

## Symfony package surface

The package exposes `App\\Enveloping\\EnvelopingBundle` and an `EnvelopeFactory` service. The core value objects remain usable without a Symfony container.

The repository supports canonical dual-runtime operation:

- reusable bundle composition through `EnvelopingBundle`;
- standalone boot through `bin/console`, `Kernel`, and `config/bundles.php` for container verification and debugging.

The standalone runtime does not make Enveloping a CRUD/UI application consumer. Canon022 explicitly exempts `enveloping/envelope` from the mandatory application dependency baseline, while Canon041/042 exclude its headless debug runtime from browser/UI tooling and behavioral-UI coverage requirements.

## Core type vocabulary

The core uses canonical technical-role-first placement:

- `ValueObject/Envelope` — immutable contextual wrapper;
- `ValueObjectInterface/EnvelopeAttributeInterface` — typed contextual value contract;
- `EnvelopeActorAttribute`, `EnvelopeOriginAttribute`, `EnvelopeCorrelationAttribute`, and `EnvelopeCausationAttribute` — the initial deliberately small generic vocabulary.

Consumer-specific contextual concepts remain outside this package until repeated cross-component use proves that they are genuinely generic.

## Envelope semantics

Attribute lookup is polymorphic: callers may query a concrete `Envelope*Attribute` type or a compatible contract such as `EnvelopeAttributeInterface`. Attachment order is preserved.

`with()` appends context and permits repeated values of the same type. Enveloping intentionally does not declare global singleton/multi-value cardinality. When a composing use case wants singleton semantics it calls `replace()` explicitly.

`without()` removes attributes compatible with the requested type, while `has()`, `last()`, and `all()` provide typed introspection. `withSubject()` explicitly rebinds the same immutable context to another subject; context propagation is therefore caller-controlled rather than automatic.

## Process-boundary encoding

`EnvelopeCodec` provides a transport-neutral boundary without making the core depend on Symfony Messenger or any domain repository.

- the composing application owns subject encoding/decoding through explicit callbacks;
- `EnvelopeAttributeCodec` owns typed attribute encoding/decoding;
- `EnvelopeAttributeCodecRegistry` selects the first supporting codec;
- `EnvelopeBuiltInAttributeCodec` handles only the generic attributes owned by Enveloping;
- additional codecs can be registered through the `enveloping.attribute_codec` service tag without changing Envelope core;
- implementations of `EnvelopeAttributeCodec` are autoconfigured into that tag;
- every codec declares the stable wire type IDs it owns, and duplicate or empty type ownership is rejected at registry construction;
- runtime attribute encoding must resolve to exactly one codec;
- a codec may emit only wire type IDs declared by its own `transportTypes()` contract.

The transport DTO is intentionally dynamic only at the serialization boundary. Enveloping does not infer how a Shipment, Payment, Message, Entity, or other subject should cross a process boundary. The caller must convert such subjects to a transport-safe scalar/value representation first.

Attribute payloads support recursive JSON-safe values: `null`, booleans, integers, floats, strings, lists, and string-key maps containing the same value forms. Objects, resources, and non-string map keys are rejected at the boundary. Payload maps are normalized before storage in transport DTOs or Messenger stamps.

The wire contract is decoupled from PHP class names. Built-in attributes use stable transport type identifiers (`actor`, `origin`, `correlation`, `causation`), and custom codecs own their own stable type identifiers. `EnvelopeTransportDTO` carries an explicit format version; the current version is `1`, and unsupported versions are rejected during decoding.

## Optional Symfony Messenger bridge

When `symfony/messenger` is installed, Enveloping conditionally registers `EnvelopeMessengerCodec`. The bridge maps an Enveloping `Envelope` onto Symfony Messenger's own `Envelope` without changing the business message:

- the Messenger message remains the original object;
- Enveloping context is carried in one `EnvelopeContextStamp`;
- the stamp contains only versioned scalar transport data and is serializable;
- a Messenger Envelope without an Enveloping stamp decodes to empty Enveloping context;
- Messenger remains optional at package runtime and is only a development dependency of this repository.

This keeps Symfony transport metadata at the integration edge instead of leaking `StampInterface` into the Enveloping core model.

The Messenger bridge also supports existing Symfony envelopes. `withContext()` replaces only `EnvelopeContextStamp`, preserving unrelated Messenger stamps; `withoutContext()` removes only Enveloping context. Empty Enveloping context clears the stamp entirely, and replacing context requires the exact same business message object to prevent context/message mismatch.

## JSON wire adapter

`EnvelopeJsonCodec` is a second transport adapter over the same versioned Envelope DTO contract. It proves that Messenger is not the transport model itself: HTTP, MCP, CLI, files, or other process boundaries can use the same stable context wire format without depending on Symfony Messenger.

Subject encoding/decoding remains external. The JSON adapter requires the encoded subject to be JSON-safe, preserves strict JSON object-vs-array semantics, validates envelope/attribute document shape, and delegates attribute reconstruction back to the typed codec registry.

The versioned wrapper schema is closed: Envelope JSON documents contain exactly `version`, `subject`, and `attributes`; attribute wrappers contain exactly `type` and `payload`; Messenger context-stamp entries follow the same two-field wrapper. Unknown structural fields are rejected instead of ignored. Built-in attribute payload schemas are strict as well, while custom payload schemas remain owned entirely by their custom codecs.
