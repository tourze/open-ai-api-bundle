<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\OpenAiApiBundle\Entity\ChatConversation;
use Tourze\OpenAiApiBundle\Enum\ConversationStatus;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(ChatConversation::class)]
final class ChatConversationTest extends AbstractEntityTestCase
{
    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'title' => ['title', 'Test Conversation'];
        yield 'model' => ['model', 'gpt-4'];
        yield 'messages' => ['messages', [['role' => 'user', 'content' => 'Hello']]];
        yield 'status' => ['status', ConversationStatus::Active];
        yield 'totalTokens' => ['totalTokens', 100];
        yield 'cost' => ['cost', '0.005000'];
        yield 'systemPrompt' => ['systemPrompt', 'You are a helpful assistant'];
        yield 'temperature' => ['temperature', '0.70'];
    }

    public function testEntityCreation(): void
    {
        $entity = new ChatConversation();
        $this->assertInstanceOf(ChatConversation::class, $entity);
        $this->assertNull($entity->getId());
        $this->assertSame(ConversationStatus::Active, $entity->getStatus());
        $this->assertInstanceOf(\DateTimeImmutable::class, $entity->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $entity->getUpdatedAt());
    }

    public function testConstructorSetsDateTimes(): void
    {
        $entity = new ChatConversation();

        $this->assertInstanceOf(\DateTimeImmutable::class, $entity->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $entity->getUpdatedAt());

        // 验证创建时间和更新时间相等（在构造函数中设置）
        $this->assertSame(
            $entity->getCreatedAt()->format('Y-m-d H:i:s'),
            $entity->getUpdatedAt()->format('Y-m-d H:i:s')
        );
    }

    public function testTitleProperty(): void
    {
        $entity = new ChatConversation();
        $entity->setTitle('Test Title');
        $this->assertSame('Test Title', $entity->getTitle());
    }

    public function testModelProperty(): void
    {
        $entity = new ChatConversation();
        $entity->setModel('gpt-3.5-turbo');
        $this->assertSame('gpt-3.5-turbo', $entity->getModel());
    }

    public function testMessagesProperty(): void
    {
        $entity = new ChatConversation();
        $messages = [
            ['role' => 'user', 'content' => 'Hello'],
            ['role' => 'assistant', 'content' => 'Hi there!'],
        ];

        $entity->setMessages($messages);
        $this->assertSame($messages, $entity->getMessages());
    }

    public function testSetMessagesUpdatesTimestamp(): void
    {
        $entity = new ChatConversation();
        $originalUpdatedAt = $entity->getUpdatedAt();

        // 等待一毫秒确保时间戳不同
        usleep(1000);

        $entity->setMessages([['role' => 'user', 'content' => 'Hello']]);
        $this->assertGreaterThan($originalUpdatedAt, $entity->getUpdatedAt());
    }

    public function testAddMessage(): void
    {
        $entity = new ChatConversation();
        $entity->setMessages([]);

        $message = ['role' => 'user', 'content' => 'Hello'];
        $entity->addMessage($message);

        $this->assertCount(1, $entity->getMessages());
        $this->assertSame($message, $entity->getMessages()[0]);
    }

    public function testAddMessageUpdatesTimestamp(): void
    {
        $entity = new ChatConversation();
        $originalUpdatedAt = $entity->getUpdatedAt();

        // 等待一毫秒确保时间戳不同
        usleep(1000);

        $entity->addMessage(['role' => 'user', 'content' => 'Hello']);
        $this->assertGreaterThan($originalUpdatedAt, $entity->getUpdatedAt());
    }

    public function testStatusProperty(): void
    {
        $entity = new ChatConversation();

        // 默认状态是 Active
        $this->assertSame(ConversationStatus::Active, $entity->getStatus());

        $entity->setStatus(ConversationStatus::Archived);
        $this->assertSame(ConversationStatus::Archived, $entity->getStatus());
    }

    public function testTotalTokensProperty(): void
    {
        $entity = new ChatConversation();

        // 默认为 null
        $this->assertNull($entity->getTotalTokens());

        $entity->setTotalTokens(150);
        $this->assertSame(150, $entity->getTotalTokens());

        $entity->setTotalTokens(null);
        $this->assertNull($entity->getTotalTokens());
    }

    public function testCostProperty(): void
    {
        $entity = new ChatConversation();

        // 默认为 null
        $this->assertNull($entity->getCost());

        $entity->setCost('0.125000');
        $this->assertSame('0.125000', $entity->getCost());

        $entity->setCost(null);
        $this->assertNull($entity->getCost());
    }

    public function testSystemPromptProperty(): void
    {
        $entity = new ChatConversation();

        // 默认为 null
        $this->assertNull($entity->getSystemPrompt());

        $entity->setSystemPrompt('You are a helpful assistant');
        $this->assertSame('You are a helpful assistant', $entity->getSystemPrompt());

        $entity->setSystemPrompt(null);
        $this->assertNull($entity->getSystemPrompt());
    }

    public function testTemperatureProperty(): void
    {
        $entity = new ChatConversation();

        // 默认为 null
        $this->assertNull($entity->getTemperature());

        $entity->setTemperature('0.80');
        $this->assertSame('0.80', $entity->getTemperature());

        $entity->setTemperature(null);
        $this->assertNull($entity->getTemperature());
    }

    public function testStringRepresentation(): void
    {
        $entity = new ChatConversation();
        $entity->setTitle('My Chat');

        $this->assertSame('My Chat', (string) $entity);
    }

    public function testMethodChaining(): void
    {
        $entity = new ChatConversation();

        $entity->setTitle('Test Chat');
        $entity->setModel('gpt-4');
        $entity->setMessages([]);
        $entity->setStatus(ConversationStatus::Active);
        $entity->setTotalTokens(100);
        $entity->setCost('0.005000');
        $entity->setSystemPrompt('System prompt');
        $entity->setTemperature('0.70');

        // 验证所有属性都已正确设置
        $this->assertSame('Test Chat', $entity->getTitle());
        $this->assertSame('gpt-4', $entity->getModel());
        $this->assertSame([], $entity->getMessages());
        $this->assertSame(ConversationStatus::Active, $entity->getStatus());
        $this->assertSame(100, $entity->getTotalTokens());
        $this->assertSame('0.005000', $entity->getCost());
        $this->assertSame('System prompt', $entity->getSystemPrompt());
        $this->assertSame('0.70', $entity->getTemperature());
    }

    protected function createEntity(): ChatConversation
    {
        return new ChatConversation();
    }
}
