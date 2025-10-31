<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\OpenAiApiBundle\DependencyInjection\OpenAiApiExtension;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;

/**
 * @internal
 */
#[CoversClass(OpenAiApiExtension::class)]
final class OpenAiApiExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    private OpenAiApiExtension $extension;

    private ContainerBuilder $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->extension = new OpenAiApiExtension();
        $this->container = new ContainerBuilder();
        $this->container->setParameter('kernel.environment', 'test');
    }

    public function testLoadShouldLoadServiceConfiguration(): void
    {
        $this->extension->load([], $this->container);

        $definitions = $this->container->getDefinitions();

        $this->assertGreaterThan(0, count($definitions), '应该注册了至少一个服务定义');

        // 验证服务配置的默认设置
        foreach ($definitions as $definition) {
            if (str_starts_with($definition->getClass() ?? '', 'Tourze\OpenAiApiBundle\\')) {
                $this->assertTrue($definition->isAutowired(), '服务应该启用自动装配');
                $this->assertTrue($definition->isAutoconfigured(), '服务应该启用自动配置');
                $this->assertFalse($definition->isPublic(), '服务默认应该是私有的');
            }
        }
    }

    public function testLoadShouldAcceptMultipleConfigurationSets(): void
    {
        $configs = [
            ['some' => 'config'],
            ['another' => 'config'],
        ];

        $this->extension->load($configs, $this->container);

        $definitions = $this->container->getDefinitions();
        $this->assertGreaterThan(0, count($definitions), '应该成功加载多个配置集');
    }

    public function testLoadShouldSetCorrectResourcePath(): void
    {
        $this->extension->load([], $this->container);

        // 验证加载器使用了正确的配置路径
        $expectedConfigPath = dirname(__DIR__, 2) . '/src/Resources/config';
        $this->assertDirectoryExists($expectedConfigPath, '配置目录应该存在');

        $servicesFile = $expectedConfigPath . '/services.yaml';
        $this->assertFileExists($servicesFile, 'services.yaml 配置文件应该存在');
    }
}
