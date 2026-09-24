# Changelog

All notable product-facing changes to Enveloping are documented here.

The project follows semantic versioning for the PHP package API. The serialized
Envelope wire format has its own version lifecycle documented in
`TRANSPORT_COMPATIBILITY.md`.

## [Unreleased]

No unreleased product changes yet.

## [1.0.0] - 2026-09-24

### Added

- immutable typed `Envelope` context wrapper for arbitrary subjects;
- generic Actor, Origin, Correlation, and Causation contextual attributes;
- explicit append, replace, removal, lookup, and subject-rebinding semantics;
- explicit parent-to-child propagation through `EnvelopeFactory::inherit()` and
  `inheritOnly()`;
- transport-neutral `EnvelopeCodec` with extensible typed attribute codecs;
- versioned JSON wire adapter;
- optional Symfony Messenger bridge using `EnvelopeContextStamp`;
- typed transport and codec exceptions;
- stable wire type identifiers and recursive JSON-safe payload validation;
- Symfony Bundle/DI integration with automatic custom-codec tagging;
- transport compatibility and evolution contract;
- integration proof for selective propagation across a Messenger boundary.

### Compatibility

- current serialized transport version: **1**;
- JSON and Messenger adapters share the same transport version;
- Symfony Messenger remains optional at runtime;
- Enveloping remains persistence-agnostic and does not require consumer-domain
  components, Doctrine, CRUD, or UI packages.

### Quality

- PHPStan and PHP-CS-Fixer are mandatory repository gates;
- PHPUnit exercises unit and integration behavior with branch/path coverage;
- canonical Gating integration is part of the quality pipeline.

