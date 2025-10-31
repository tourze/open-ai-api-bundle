<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\OpenAiApiBundle\OpenAiApiBundle;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;

/**
 * @internal
 */
#[CoversClass(OpenAiApiBundle::class)]
#[RunTestsInSeparateProcesses]
final class OpenAiApiBundleTest extends AbstractBundleTestCase
{
}
