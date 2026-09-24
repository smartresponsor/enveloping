<?php

declare(strict_types=1);

namespace App\Enveloping\Exception;

/**
 * Signals malformed or unsupported serialized Envelope transport data.
 */
final class EnvelopeTransportException extends \InvalidArgumentException
{
}
