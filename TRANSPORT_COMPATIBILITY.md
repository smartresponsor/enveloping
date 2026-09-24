# Envelope transport compatibility

Enveloping has two distinct compatibility surfaces:

1. the PHP package API, governed by package semantic versioning;
2. the serialized Envelope transport contract, governed by `EnvelopeTransportDTO::CURRENT_VERSION`.

They evolve independently. A package release does not require a transport-version bump unless serialized Envelope data changes incompatibly.

## Current transport version

The current transport version is **1**.

JSON and Symfony Messenger adapters use the same version identifier. There is no adapter-specific transport version.

## Version 1 stable surface

Version 1 fixes the following wrapper contract:

- Envelope document fields: `version`, `subject`, `attributes`;
- attribute wrapper fields: `type`, `payload`;
- attribute type grammar: `^[a-z][a-z0-9._-]*$`;
- recursive JSON-safe payload semantics;
- built-in type identifiers:
  - `actor`;
  - `origin`;
  - `correlation`;
  - `causation`.

Built-in payload keys are part of the version-1 contract:

- `actor.identity`;
- `origin.source`;
- `correlation.id`;
- `causation.id`.

## Changes that do not require a transport-version bump

The following are compatible when they do not alter already serialized meanings:

- adding PHP helper APIs around Envelope composition;
- adding a new transport adapter over the same DTO contract;
- adding a new custom attribute codec and type identifier, provided every receiver that may receive that type has the codec installed;
- adding validation that rejects data which was never valid under the documented version-1 contract;
- internal performance or implementation changes that preserve emitted and accepted wire data.

A newly introduced attribute type is not automatically understood by older receivers. Deployment therefore remains a composition concern: producers must not emit a type to receivers that do not own its codec.

## Changes that require a new transport version

A new transport version is required when existing serialized version-1 data would need a different structural or semantic interpretation, including:

- renaming/removing wrapper fields;
- changing the meaning or representation of `subject`;
- changing attribute wrapper structure;
- renaming an existing built-in type identifier;
- changing an existing built-in payload key or its meaning;
- relaxing or changing type-ID grammar in a way that changes accepted identity semantics;
- changing JSON-safe value semantics incompatibly;
- changing ordering semantics if consumers can observe the order.

## Decoder policy

The current decoder policy is exact-version support.

Version-1 decoders accept version 1 and reject other versions with `EnvelopeTransportException`. Multi-version decoding must be introduced explicitly when a real migration requires it; Enveloping does not silently guess or coerce versions.

## Package SemVer and wire versions

Package SemVer and transport version are related but not identical:

- patch/minor package releases may keep transport version 1;
- a package major release may still use transport version 1 if wire compatibility is preserved;
- introducing transport version 2 does not by itself define the package major version, but the package release must document migration and deployment requirements.

## Deployment rule

A producer may emit only transport versions and attribute type identifiers supported by every intended receiver on that route.

Enveloping deliberately does not negotiate distributed deployment compatibility automatically. The host/application composition layer owns rollout ordering and routing compatibility.
