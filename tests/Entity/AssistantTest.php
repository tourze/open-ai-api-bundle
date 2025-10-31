<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\OpenAiApiBundle\Entity\Assistant;
use Tourze\OpenAiApiBundle\Enum\AssistantStatus;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(Assistant::class)]
final class AssistantTest extends AbstractEntityTestCase
{
    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'assistantId' => ['assistantId', 'asst_abc123'];
        yield 'name' => ['name', 'My Assistant'];
        yield 'description' => ['description', 'A helpful assistant'];
        yield 'model' => ['model', 'gpt-4'];
        yield 'instructions' => ['instructions', 'You are a helpful assistant'];
        yield 'tools' => ['tools', [['type' => 'code_interpreter']]];
        yield 'metadata' => ['metadata', ['key' => 'value']];
        yield 'status' => ['status', AssistantStatus::Active];
        yield 'temperature' => ['temperature', '0.70'];
        yield 'topP' => ['topP', '1.00'];
        yield 'fileIds' => ['fileIds', ['file-123', 'file-456']];
        yield 'responseFormat' => ['responseFormat', 'json_object'];
    }

    public function testEntityCreation(): void
    {
        $entity = new Assistant();
        $this->assertInstanceOf(Assistant::class, $entity);
        $this->assertNull($entity->getId());
        $this->assertSame(AssistantStatus::Active, $entity->getStatus());
        $this->assertInstanceOf(\DateTimeImmutable::class, $entity->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $entity->getUpdatedAt());
        $this->assertSame([], $entity->getTools());
        $this->assertSame([], $entity->getMetadata());
        $this->assertSame([], $entity->getFileIds());
        $this->assertNull($entity->getDescription());
        $this->assertNull($entity->getInstructions());
        $this->assertNull($entity->getTemperature());
        $this->assertNull($entity->getTopP());
        $this->assertNull($entity->getResponseFormat());
    }

    public function testConstructorSetsDateTimes(): void
    {
        $entity = new Assistant();

        $this->assertInstanceOf(\DateTimeImmutable::class, $entity->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $entity->getUpdatedAt());

        // 验证创建时间和更新时间相等（在构造函数中设置）
        $this->assertSame(
            $entity->getCreatedAt()->format('Y-m-d H:i:s'),
            $entity->getUpdatedAt()->format('Y-m-d H:i:s')
        );
    }

    public function testAssistantIdProperty(): void
    {
        $entity = new Assistant();
        $entity->setAssistantId('asst_xyz789');
        $this->assertSame('asst_xyz789', $entity->getAssistantId());
    }

    public function testNameProperty(): void
    {
        $entity = new Assistant();
        $entity->setName('Code Assistant');
        $this->assertSame('Code Assistant', $entity->getName());
    }

    public function testDescriptionProperty(): void
    {
        $entity = new Assistant();

        // 默认为 null
        $this->assertNull($entity->getDescription());

        $entity->setDescription('An assistant that helps with coding');
        $this->assertSame('An assistant that helps with coding', $entity->getDescription());

        $entity->setDescription(null);
        $this->assertNull($entity->getDescription());
    }

    public function testModelProperty(): void
    {
        $entity = new Assistant();
        $entity->setModel('gpt-3.5-turbo');
        $this->assertSame('gpt-3.5-turbo', $entity->getModel());
    }

    public function testInstructionsProperty(): void
    {
        $entity = new Assistant();

        // 默认为 null
        $this->assertNull($entity->getInstructions());

        $entity->setInstructions('Be helpful and accurate');
        $this->assertSame('Be helpful and accurate', $entity->getInstructions());

        $entity->setInstructions(null);
        $this->assertNull($entity->getInstructions());
    }

    public function testToolsProperty(): void
    {
        $entity = new Assistant();

        // 默认为空数组
        $this->assertSame([], $entity->getTools());

        $tools = [
            ['type' => 'code_interpreter'],
            ['type' => 'retrieval'],
        ];
        $entity->setTools($tools);
        $this->assertSame($tools, $entity->getTools());
    }

    public function testSetToolsUpdatesTimestamp(): void
    {
        $entity = new Assistant();
        $originalUpdatedAt = $entity->getUpdatedAt();

        // 等待一毫秒确保时间戳不同
        usleep(1000);

        $entity->setTools([['type' => 'function']]);
        $this->assertGreaterThan($originalUpdatedAt, $entity->getUpdatedAt());
    }

    public function testMetadataProperty(): void
    {
        $entity = new Assistant();

        // 默认为空数组
        $this->assertSame([], $entity->getMetadata());

        $metadata = ['department' => 'engineering', 'version' => '1.0'];
        $entity->setMetadata($metadata);
        $this->assertSame($metadata, $entity->getMetadata());
    }

    public function testStatusProperty(): void
    {
        $entity = new Assistant();

        // 默认状态是 Active
        $this->assertSame(AssistantStatus::Active, $entity->getStatus());

        $entity->setStatus(AssistantStatus::Archived);
        $this->assertSame(AssistantStatus::Archived, $entity->getStatus());
    }

    public function testSetStatusUpdatesTimestamp(): void
    {
        $entity = new Assistant();
        $originalUpdatedAt = $entity->getUpdatedAt();

        // 等待一毫秒确保时间戳不同
        usleep(1000);

        $entity->setStatus(AssistantStatus::Archived);
        $this->assertGreaterThan($originalUpdatedAt, $entity->getUpdatedAt());
    }

    public function testTemperatureProperty(): void
    {
        $entity = new Assistant();

        // 默认为 null
        $this->assertNull($entity->getTemperature());

        $entity->setTemperature('0.50');
        $this->assertSame('0.50', $entity->getTemperature());

        $entity->setTemperature(null);
        $this->assertNull($entity->getTemperature());
    }

    public function testTopPProperty(): void
    {
        $entity = new Assistant();

        // 默认为 null
        $this->assertNull($entity->getTopP());

        $entity->setTopP('0.90');
        $this->assertSame('0.90', $entity->getTopP());

        $entity->setTopP(null);
        $this->assertNull($entity->getTopP());
    }

    public function testFileIdsProperty(): void
    {
        $entity = new Assistant();

        // 默认为空数组
        $this->assertSame([], $entity->getFileIds());

        $fileIds = ['file-abc123', 'file-def456'];
        $entity->setFileIds($fileIds);
        $this->assertSame($fileIds, $entity->getFileIds());
    }

    public function testSetFileIdsUpdatesTimestamp(): void
    {
        $entity = new Assistant();
        $originalUpdatedAt = $entity->getUpdatedAt();

        // 等待一毫秒确保时间戳不同
        usleep(1000);

        $entity->setFileIds(['file-123']);
        $this->assertGreaterThan($originalUpdatedAt, $entity->getUpdatedAt());
    }

    public function testResponseFormatProperty(): void
    {
        $entity = new Assistant();

        // 默认为 null
        $this->assertNull($entity->getResponseFormat());

        $entity->setResponseFormat('json_object');
        $this->assertSame('json_object', $entity->getResponseFormat());

        $entity->setResponseFormat(null);
        $this->assertNull($entity->getResponseFormat());
    }

    public function testStringRepresentation(): void
    {
        $entity = new Assistant();
        $entity->setName('Math Tutor');

        $this->assertSame('Math Tutor', (string) $entity);
    }

    public function testMethodChaining(): void
    {
        $entity = new Assistant();

        $entity->setAssistantId('asst_chaining_test');
        $entity->setName('Chain Assistant');
        $entity->setDescription('Testing method chaining');
        $entity->setModel('gpt-4');
        $entity->setInstructions('Chain all methods');
        $entity->setTools([['type' => 'code_interpreter']]);
        $entity->setMetadata(['test' => 'chaining']);
        $entity->setStatus(AssistantStatus::Active);
        $entity->setTemperature('0.75');
        $entity->setTopP('0.95');
        $entity->setFileIds(['file-chain']);
        $entity->setResponseFormat('text');

        // 验证所有属性都已正确设置
        $this->assertSame('asst_chaining_test', $entity->getAssistantId());
        $this->assertSame('Chain Assistant', $entity->getName());
        $this->assertSame('Testing method chaining', $entity->getDescription());
        $this->assertSame('gpt-4', $entity->getModel());
        $this->assertSame('Chain all methods', $entity->getInstructions());
        $this->assertSame([['type' => 'code_interpreter']], $entity->getTools());
        $this->assertSame(['test' => 'chaining'], $entity->getMetadata());
        $this->assertSame(AssistantStatus::Active, $entity->getStatus());
        $this->assertSame('0.75', $entity->getTemperature());
        $this->assertSame('0.95', $entity->getTopP());
        $this->assertSame(['file-chain'], $entity->getFileIds());
        $this->assertSame('text', $entity->getResponseFormat());
    }

    protected function createEntity(): Assistant
    {
        return new Assistant();
    }
}
