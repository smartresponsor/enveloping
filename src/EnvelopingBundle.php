<?php

declare(strict_types=1);

namespace App\Enveloping;

use App\Enveloping\DependencyInjection\EnvelopingExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class EnvelopingBundle extends Bundle
{
    public function getContainerExtension(): EnvelopingExtension
    {
        return new EnvelopingExtension();
    }
}
