<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use EasyCorp\Bundle\EasyAdminBundle\EasyAdminBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;

class OpenAiApiBundle extends Bundle implements BundleDependencyInterface
{
    /**
     * @return array<class-string<Bundle>, array<string, bool>>
     */
    public static function getBundleDependencies(): array
    {
        return [
            DoctrineBundle::class => [],
            EasyAdminBundle::class => [],
            TwigBundle::class => [],
        ];
    }
}
