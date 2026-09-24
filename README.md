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

The package exposes App\Enveloping\EnvelopingBundle and an EnvelopeFactory service. The core value objects remain usable without a Symfony container.
