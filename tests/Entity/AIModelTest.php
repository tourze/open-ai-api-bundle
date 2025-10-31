<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\OpenAiApiBundle\Entity\AIModel;
use Tourze\OpenAiApiBundle\Enum\ModelStatus;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(AIModel::class)]
final class AIModelTest extends AbstractEntityTestCase
{
    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'modelId' => ['modelId', 'gpt-4'];
        yield 'name' => ['name', 'GPT-4'];
        yield 'description' => ['description', 'Advanced language model'];
        yield 'owner' => ['owner', 'openai'];
        yield 'status' => ['status', ModelStatus::Available];
        yield 'contextWindow' => ['contextWindow', 8192];
        yield 'inputPricePerToken' => ['inputPricePerToken', '0.000030'];
        yield 'outputPricePerToken' => ['outputPricePerToken', '0.000060'];
        yield 'capabilities' => ['capabilities', ['text-generation', 'code-completion']];
        yield 'isActive' => ['isActive', true];
    }

    public function testEntityCreation(): void
    {
        $entity = new AIModel();
        $this->assertInstanceOf(AIModel::class, $entity);
        $this->assertNull($entity->getId());
        $this->assertSame(ModelStatus::Available, $entity->getStatus());
        $this->assertTrue($entity->isActive());
        $this->assertInstanceOf(\DateTimeImmutable::class, $entity->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $entity->getUpdatedAt());
        $this->assertSame([], $entity->getCapabilities());
    }

    public function testConstructorSetsDateTimes(): void
    {
        $entity = new AIModel();

        $this->assertInstanceOf(\DateTimeImmutable::class, $entity->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $entity->getUpdatedAt());

        // 验证创建时间和更新时间相等（在构造函数中设置）
        $this->assertSame(
            $entity->getCreatedAt()->format('Y-m-d H:i:s'),
            $entity->getUpdatedAt()->format('Y-m-d H:i:s')
        );
    }

    public function testModelIdProperty(): void
    {
        $entity = new AIModel();
        $entity->setModelId('gpt-3.5-turbo');
        $this->assertSame('gpt-3.5-turbo', $entity->getModelId());
    }

    public function testNameProperty(): void
    {
        $entity = new AIModel();
        $entity->setName('GPT-3.5 Turbo');
        $this->assertSame('GPT-3.5 Turbo', $entity->getName());
    }

    public function testDescriptionProperty(): void
    {
        $entity = new AIModel();

        // 默认为 null
        $this->assertNull($entity->getDescription());

        $entity->setDescription('A powerful language model');
        $this->assertSame('A powerful language model', $entity->getDescription());

        $entity->setDescription(null);
        $this->assertNull($entity->getDescription());
    }

    public function testOwnerProperty(): void
    {
        $entity = new AIModel();
        $entity->setOwner('openai');
        $this->assertSame('openai', $entity->getOwner());
    }

    public function testStatusProperty(): void
    {
        $entity = new AIModel();

        // 默认状态是 Available
        $this->assertSame(ModelStatus::Available, $entity->getStatus());

        $entity->setStatus(ModelStatus::Deprecated);
        $this->assertSame(ModelStatus::Deprecated, $entity->getStatus());
    }

    public function testContextWindowProperty(): void
    {
        $entity = new AIModel();

        // 默认为 null
        $this->assertNull($entity->getContextWindow());

        $entity->setContextWindow(4096);
        $this->assertSame(4096, $entity->getContextWindow());

        $entity->setContextWindow(null);
        $this->assertNull($entity->getContextWindow());
    }

    public function testInputPricePerTokenProperty(): void
    {
        $entity = new AIModel();

        // 默认为 null
        $this->assertNull($entity->getInputPricePerToken());

        $entity->setInputPricePerToken('0.000010');
        $this->assertSame('0.000010', $entity->getInputPricePerToken());

        $entity->setInputPricePerToken(null);
        $this->assertNull($entity->getInputPricePerToken());
    }

    public function testOutputPricePerTokenProperty(): void
    {
        $entity = new AIModel();

        // 默认为 null
        $this->assertNull($entity->getOutputPricePerToken());

        $entity->setOutputPricePerToken('0.000020');
        $this->assertSame('0.000020', $entity->getOutputPricePerToken());

        $entity->setOutputPricePerToken(null);
        $this->assertNull($entity->getOutputPricePerToken());
    }

    public function testCapabilitiesProperty(): void
    {
        $entity = new AIModel();

        // 默认为空数组
        $this->assertSame([], $entity->getCapabilities());

        $capabilities = ['text-generation', 'code-completion', 'image-analysis'];
        $entity->setCapabilities($capabilities);
        $this->assertSame($capabilities, $entity->getCapabilities());
    }

    public function testIsActiveProperty(): void
    {
        $entity = new AIModel();

        // 默认为 true
        $this->assertTrue($entity->isActive());

        $entity->setIsActive(false);
        $this->assertFalse($entity->isActive());

        $entity->setIsActive(true);
        $this->assertTrue($entity->isActive());
    }

    public function testSetIsActiveUpdatesTimestamp(): void
    {
        $entity = new AIModel();
        $originalUpdatedAt = $entity->getUpdatedAt();

        // 等待一毫秒确保时间戳不同
        usleep(1000);

        $entity->setIsActive(false);
        $this->assertGreaterThan($originalUpdatedAt, $entity->getUpdatedAt());
    }

    public function testStringRepresentation(): void
    {
        $entity = new AIModel();
        $entity->setName('GPT-4 Turbo');

        $this->assertSame('GPT-4 Turbo', (string) $entity);
    }

    public function testMethodChaining(): void
    {
        $entity = new AIModel();

        $entity->setModelId('gpt-4-turbo');
        $entity->setName('GPT-4 Turbo');
        $entity->setDescription('The latest GPT-4 model');
        $entity->setOwner('openai');
        $entity->setStatus(ModelStatus::Available);
        $entity->setContextWindow(128000);
        $entity->setInputPricePerToken('0.000010');
        $entity->setOutputPricePerToken('0.000030');
        $entity->setCapabilities(['text-generation', 'code-completion']);
        $entity->setIsActive(true);

        // 验证所有属性都已正确设置
        $this->assertSame('gpt-4-turbo', $entity->getModelId());
        $this->assertSame('GPT-4 Turbo', $entity->getName());
        $this->assertSame('The latest GPT-4 model', $entity->getDescription());
        $this->assertSame('openai', $entity->getOwner());
        $this->assertSame(ModelStatus::Available, $entity->getStatus());
        $this->assertSame(128000, $entity->getContextWindow());
        $this->assertSame('0.000010', $entity->getInputPricePerToken());
        $this->assertSame('0.000030', $entity->getOutputPricePerToken());
        $this->assertSame(['text-generation', 'code-completion'], $entity->getCapabilities());
        $this->assertTrue($entity->isActive());
    }

    public function testBooleanPropertyWithIsPrefix(): void
    {
        $entity = new AIModel();

        // 测试 isActive 的 getter 方法名
        $this->assertTrue($entity->isActive());

        $entity->setIsActive(false);
        $this->assertFalse($entity->isActive());
    }

    protected function createEntity(): AIModel
    {
        return new AIModel();
    }
}
