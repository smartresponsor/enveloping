<?php

declare(strict_types=1);

namespace App\Enveloping;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

/**
 * Boots the standalone debug and container-verification runtime for Enveloping.
 */
final class Kernel extends BaseKernel
{
    use MicroKernelTrait;
}
