<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\OpenAiApiBundle\Entity\Thread;
use Tourze\OpenAiApiBundle\Enum\ThreadStatus;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(Thread::class)]
final class ThreadTest extends AbstractEntityTestCase
{
    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'threadId' => ['threadId', 'thread_abc123'];
        yield 'title' => ['title', 'My Thread'];
        yield 'metadata' => ['metadata', ['key' => 'value']];
        yield 'status' => ['status', ThreadStatus::Active];
        yield 'messageCount' => ['messageCount', 5];
        yield 'assistantId' => ['assistantId', 'asst_xyz789'];
        yield 'description' => ['description', 'Thread description'];
        yield 'toolResources' => ['toolResources', ['code_interpreter' => ['file_ids' => ['file-123']]]];
    }

    public function testEntityCreation(): void
    {
        $entity = new Thread();
        $this->assertInstanceOf(Thread::class, $entity);
        $this->assertNull($entity->getId());
        $this->assertSame(ThreadStatus::Active, $entity->getStatus());
        $this->assertInstanceOf(\DateTimeImmutable::class, $entity->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $entity->getUpdatedAt());
        $this->assertSame([], $entity->getMetadata());
        $this->assertSame([], $entity->getToolResources());
        $this->assertSame(0, $entity->getMessageCount());
        $this->assertNull($entity->getTitle());
        $this->assertNull($entity->getAssistantId());
        $this->assertNull($entity->getDescription());
    }

    public function testConstructorSetsDateTimes(): void
    {
        $entity = new Thread();

        $this->assertInstanceOf(\DateTimeImmutable::class, $entity->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $entity->getUpdatedAt());

        // 验证创建时间和更新时间相等（在构造函数中设置）
        $this->assertSame(
            $entity->getCreatedAt()->format('Y-m-d H:i:s'),
            $entity->getUpdatedAt()->format('Y-m-d H:i:s')
        );
    }

    public function testThreadIdProperty(): void
    {
        $entity = new Thread();
        $entity->setThreadId('thread_xyz789');
        $this->assertSame('thread_xyz789', $entity->getThreadId());
    }

    public function testTitleProperty(): void
    {
        $entity = new Thread();

        // 默认为 null
        $this->assertNull($entity->getTitle());

        $entity->setTitle('Chat Thread');
        $this->assertSame('Chat Thread', $entity->getTitle());

        $entity->setTitle(null);
        $this->assertNull($entity->getTitle());
    }

    public function testMetadataProperty(): void
    {
        $entity = new Thread();

        // 默认为空数组
        $this->assertSame([], $entity->getMetadata());

        $metadata = ['user_id' => '123', 'category' => 'support'];
        $entity->setMetadata($metadata);
        $this->assertSame($metadata, $entity->getMetadata());
    }

    public function testStatusProperty(): void
    {
        $entity = new Thread();

        // 默认状态是 Active
        $this->assertSame(ThreadStatus::Active, $entity->getStatus());

        $entity->setStatus(ThreadStatus::Archived);
        $this->assertSame(ThreadStatus::Archived, $entity->getStatus());
    }

    public function testSetStatusUpdatesTimestamp(): void
    {
        $entity = new Thread();
        $originalUpdatedAt = $entity->getUpdatedAt();

        // 等待一毫秒确保时间戳不同
        usleep(1000);

        $entity->setStatus(ThreadStatus::Deleted);
        $this->assertGreaterThan($originalUpdatedAt, $entity->getUpdatedAt());
    }

    public function testMessageCountProperty(): void
    {
        $entity = new Thread();

        // 默认为 0
        $this->assertSame(0, $entity->getMessageCount());

        $entity->setMessageCount(10);
        $this->assertSame(10, $entity->getMessageCount());
    }

    public function testSetMessageCountUpdatesTimestamp(): void
    {
        $entity = new Thread();
        $originalUpdatedAt = $entity->getUpdatedAt();

        // 等待一毫秒确保时间戳不同
        usleep(1000);

        $entity->setMessageCount(1);
        $this->assertGreaterThan($originalUpdatedAt, $entity->getUpdatedAt());
    }

    public function testAssistantIdProperty(): void
    {
        $entity = new Thread();

        // 默认为 null
        $this->assertNull($entity->getAssistantId());

        $entity->setAssistantId('asst_helper123');
        $this->assertSame('asst_helper123', $entity->getAssistantId());

        $entity->setAssistantId(null);
        $this->assertNull($entity->getAssistantId());
    }

    public function testDescriptionProperty(): void
    {
        $entity = new Thread();

        // 默认为 null
        $this->assertNull($entity->getDescription());

        $entity->setDescription('Customer support thread');
        $this->assertSame('Customer support thread', $entity->getDescription());

        $entity->setDescription(null);
        $this->assertNull($entity->getDescription());
    }

    public function testToolResourcesProperty(): void
    {
        $entity = new Thread();

        // 默认为空数组
        $this->assertSame([], $entity->getToolResources());

        $toolResources = [
            'code_interpreter' => ['file_ids' => ['file-abc', 'file-def']],
            'file_search' => ['vector_store_ids' => ['vs_123']],
        ];
        $entity->setToolResources($toolResources);
        $this->assertSame($toolResources, $entity->getToolResources());
    }

    public function testSetToolResourcesUpdatesTimestamp(): void
    {
        $entity = new Thread();
        $originalUpdatedAt = $entity->getUpdatedAt();

        // 等待一毫秒确保时间戳不同
        usleep(1000);

        $entity->setToolResources(['file_search' => []]);
        $this->assertGreaterThan($originalUpdatedAt, $entity->getUpdatedAt());
    }

    public function testStringRepresentationWithTitle(): void
    {
        $entity = new Thread();
        $entity->setThreadId('thread_123');
        $entity->setTitle('My Chat');

        $this->assertSame('My Chat', (string) $entity);
    }

    public function testStringRepresentationWithoutTitle(): void
    {
        $entity = new Thread();
        $entity->setThreadId('thread_456');

        // 没有标题时返回 threadId
        $this->assertSame('thread_456', (string) $entity);
    }

    public function testStringRepresentationWithEmptyTitle(): void
    {
        $entity = new Thread();
        $entity->setThreadId('thread_789');
        $entity->setTitle('');

        // 空标题时返回 threadId
        $this->assertSame('thread_789', (string) $entity);
    }

    public function testMethodChaining(): void
    {
        $entity = new Thread();

        $entity->setThreadId('thread_chaining');
        $entity->setTitle('Chain Test');
        $entity->setMetadata(['test' => 'chaining']);
        $entity->setStatus(ThreadStatus::Active);
        $entity->setMessageCount(3);
        $entity->setAssistantId('asst_chain');
        $entity->setDescription('Testing chaining');
        $entity->setToolResources(['code_interpreter' => []]);

        // 验证所有属性都已正确设置
        $this->assertSame('thread_chaining', $entity->getThreadId());
        $this->assertSame('Chain Test', $entity->getTitle());
        $this->assertSame(['test' => 'chaining'], $entity->getMetadata());
        $this->assertSame(ThreadStatus::Active, $entity->getStatus());
        $this->assertSame(3, $entity->getMessageCount());
        $this->assertSame('asst_chain', $entity->getAssistantId());
        $this->assertSame('Testing chaining', $entity->getDescription());
        $this->assertSame(['code_interpreter' => []], $entity->getToolResources());
    }

    protected function createEntity(): Thread
    {
        return new Thread();
    }
}
