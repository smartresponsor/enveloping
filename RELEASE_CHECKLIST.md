# Enveloping release checklist

This checklist is for creating a stable package release. It does not publish or tag a release by itself.

## Required before tagging

- `composer run release:check` passes with no failures or warnings.
- `git status --short` is clean.
- `CHANGELOG.md` moves the intended changes from `Unreleased` to the target release version and date.
- `TRANSPORT_COMPATIBILITY.md` still matches the emitted wire format.
- `composer.json` and `composer.prod.json` describe the same package identity and license.
- Symfony Messenger remains optional unless a deliberate major dependency decision changes that contract.
- No consumer-domain dependency has leaked into Enveloping.
- The release commit is signed.

## Publication prerequisites

- configure a canonical Git remote for the Enveloping repository;
- decide the publication target for `enveloping/envelope` (for example, a Composer repository);
- ensure the publication target exposes the exact signed release commit;
- create a signed semantic-version tag only after the release commit and changelog are final.

## Stable v1 release flow

1. Choose the release version, normally `v1.0.0` for the first stable release.
2. Move the current `Unreleased` changelog entries under that version and add the release date.
3. Run:

   ```bash
   composer run release:check
   ```

4. Confirm the repository is clean and the final commit is signed.
5. Create a signed Git tag for the chosen version.
6. Push the release commit and tag to the canonical remote.
7. Publish or refresh the configured Composer repository.
8. Verify installation from a clean consumer project.
9. Verify one consumer flow with and without the optional Messenger dependency, as applicable.

## Wire-version note

The package release version and the serialized Envelope transport version are separate compatibility surfaces. A package release must not change `EnvelopeTransportDTO::CURRENT_VERSION` unless the wire-format rules in `TRANSPORT_COMPATIBILITY.md` require it.
