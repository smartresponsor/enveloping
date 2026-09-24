<?php

declare(strict_types=1);

namespace App\Enveloping\Codec;

use App\Enveloping\DTO\EnvelopeAttributeTransportDTO;
use App\Enveloping\ValueObjectInterface\EnvelopeAttributeInterface;

/**
 * Encodes and decodes one family of typed envelope attributes.
 */
interface EnvelopeAttributeCodec
{
    /**
     * Reports whether this codec owns the runtime attribute instance or transport type.
     */
    public function supports(EnvelopeAttributeInterface|string $attribute): bool;

    /**
     * Converts a supported typed attribute into transport-safe scalar payload data.
     */
    public function encode(EnvelopeAttributeInterface $attribute): EnvelopeAttributeTransportDTO;

    /**
     * Reconstructs a supported typed attribute from its transport representation.
     */
    public function decode(EnvelopeAttributeTransportDTO $transport): EnvelopeAttributeInterface;
}
