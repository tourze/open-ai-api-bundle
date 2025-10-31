<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle\DependencyInjection;

use Tourze\SymfonyDependencyServiceLoader\AutoExtension;

class OpenAiApiExtension extends AutoExtension
{
    protected function getConfigDir(): string
    {
        return __DIR__ . '/../Resources/config';
    }
}
