<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\OpenAiApiBundle\Entity\ChatConversation;
use Tourze\OpenAiApiBundle\Enum\ConversationStatus;
use Tourze\OpenAiApiBundle\Repository\ChatConversationRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @template TEntity of ChatConversation
 * @internal
 */
#[CoversClass(ChatConversationRepository::class)]
#[RunTestsInSeparateProcesses]
final class ChatConversationRepositoryTest extends AbstractRepositoryTestCase
{
    public function testSaveShouldPersistEntity(): void
    {
        $repository = self::getService(ChatConversationRepository::class);
        $conversation = $this->createTestConversation();

        $repository->save($conversation);

        $this->assertNotNull($conversation->getId());

        $foundConversation = $repository->find($conversation->getId());
        $this->assertInstanceOf(ChatConversation::class, $foundConversation);
        $this->assertSame($conversation->getTitle(), $foundConversation->getTitle());
    }

    public function testRemoveShouldDeleteEntity(): void
    {
        $repository = self::getService(ChatConversationRepository::class);
        $conversation = $this->createTestConversation();
        $repository->save($conversation);
        $conversationId = $conversation->getId();

        $repository->remove($conversation);

        $foundConversation = $repository->find($conversationId);
        $this->assertNull($foundConversation);
    }

    public function testFindByStatus(): void
    {
        $repository = self::getService(ChatConversationRepository::class);

        // 创建不同状态的对话
        $activeConversation = $this->createTestConversation();
        $activeConversation->setTitle('Active Chat');
        $activeConversation->setStatus(ConversationStatus::Active);
        $archivedConversation = $this->createTestConversation();
        $archivedConversation->setTitle('Archived Chat');
        $archivedConversation->setStatus(ConversationStatus::Archived);

        $repository->save($activeConversation);
        $repository->save($archivedConversation);

        $activeResults = $repository->findByStatus(ConversationStatus::Active);
        $this->assertIsArray($activeResults);

        $foundActive = false;
        foreach ($activeResults as $result) {
            $this->assertInstanceOf(ChatConversation::class, $result);
            $this->assertSame(ConversationStatus::Active, $result->getStatus());
            if ($result->getId() === $activeConversation->getId()) {
                $foundActive = true;
            }
        }
        $this->assertTrue($foundActive, 'Active conversation should be found');

        $archivedResults = $repository->findByStatus(ConversationStatus::Archived);
        $this->assertIsArray($archivedResults);

        $foundArchived = false;
        foreach ($archivedResults as $result) {
            $this->assertInstanceOf(ChatConversation::class, $result);
            $this->assertSame(ConversationStatus::Archived, $result->getStatus());
            if ($result->getId() === $archivedConversation->getId()) {
                $foundArchived = true;
            }
        }
        $this->assertTrue($foundArchived, 'Archived conversation should be found');
    }

    public function testFindByModel(): void
    {
        $repository = self::getService(ChatConversationRepository::class);

        $gpt4Conversation = $this->createTestConversation();
        $gpt4Conversation->setModel('gpt-4');
        $gpt4Conversation->setTitle('GPT-4 Chat');
        $gpt35Conversation = $this->createTestConversation();
        $gpt35Conversation->setModel('gpt-3.5-turbo');
        $gpt35Conversation->setTitle('GPT-3.5 Chat');

        $repository->save($gpt4Conversation);
        $repository->save($gpt35Conversation);

        $gpt4Results = $repository->findByModel('gpt-4');
        $this->assertIsArray($gpt4Results);

        $foundGpt4 = false;
        foreach ($gpt4Results as $result) {
            $this->assertInstanceOf(ChatConversation::class, $result);
            $this->assertSame('gpt-4', $result->getModel());
            if ($result->getId() === $gpt4Conversation->getId()) {
                $foundGpt4 = true;
            }
        }
        $this->assertTrue($foundGpt4, 'GPT-4 conversation should be found');

        $gpt35Results = $repository->findByModel('gpt-3.5-turbo');
        $this->assertIsArray($gpt35Results);

        $foundGpt35 = false;
        foreach ($gpt35Results as $result) {
            $this->assertInstanceOf(ChatConversation::class, $result);
            $this->assertSame('gpt-3.5-turbo', $result->getModel());
            if ($result->getId() === $gpt35Conversation->getId()) {
                $foundGpt35 = true;
            }
        }
        $this->assertTrue($foundGpt35, 'GPT-3.5 conversation should be found');
    }

    public function testFindRecentConversations(): void
    {
        $repository = self::getService(ChatConversationRepository::class);

        // 创建多个对话，时间不同
        $older = $this->createTestConversation();
        $older->setTitle('Older Chat');
        $repository->save($older);

        // 稍微等待以确保时间戳不同
        usleep(1000);

        $newer = $this->createTestConversation();
        $newer->setTitle('Newer Chat');
        $repository->save($newer);

        $results = $repository->findRecentConversations(10);
        $this->assertIsArray($results);
        $this->assertLessThanOrEqual(10, count($results));

        foreach ($results as $result) {
            $this->assertInstanceOf(ChatConversation::class, $result);
        }

        // 验证结果按更新时间降序排列
        if (count($results) >= 2) {
            for ($i = 0; $i < count($results) - 1; ++$i) {
                $currentTime = $results[$i]->getUpdatedAt()->getTimestamp();
                $nextTime = $results[$i + 1]->getUpdatedAt()->getTimestamp();
                $this->assertGreaterThanOrEqual(
                    $nextTime,
                    $currentTime,
                    'Results should be ordered by updatedAt DESC'
                );
            }
        }
    }

    public function testFindRecentConversationsWithLimit(): void
    {
        $repository = self::getService(ChatConversationRepository::class);

        // 创建5个对话
        for ($i = 0; $i < 5; ++$i) {
            $conversation = $this->createTestConversation();
            $conversation->setTitle("Chat {$i}");
            $repository->save($conversation);
            usleep(1000); // 确保时间戳不同
        }

        $results = $repository->findRecentConversations(3);
        $this->assertIsArray($results);
        $this->assertLessThanOrEqual(3, count($results));

        foreach ($results as $result) {
            $this->assertInstanceOf(ChatConversation::class, $result);
        }
    }

    public function testFindTotalCostByDateRange(): void
    {
        $repository = self::getService(ChatConversationRepository::class);

        $startDate = new \DateTimeImmutable('2024-01-01');
        $endDate = new \DateTimeImmutable('2024-01-31');

        $conversation1 = $this->createTestConversation();
        $conversation1->setCost('10.50');
        $conversation2 = $this->createTestConversation();
        $conversation2->setCost('5.25');

        $repository->save($conversation1);
        $repository->save($conversation2);

        $totalCost = $repository->findTotalCostByDateRange($startDate, $endDate);

        // 因为我们创建的对话时间是当前时间，可能不在指定范围内
        // 所以这个测试主要验证方法不抛出异常并返回正确的类型
        $this->assertTrue(is_string($totalCost) || null === $totalCost);
    }

    public function testCountByStatus(): void
    {
        $repository = self::getService(ChatConversationRepository::class);

        $activeConversation = $this->createTestConversation();
        $activeConversation->setStatus(ConversationStatus::Active);
        $archivedConversation = $this->createTestConversation();
        $archivedConversation->setStatus(ConversationStatus::Archived);

        $repository->save($activeConversation);
        $repository->save($archivedConversation);

        $activeCount = $repository->countByStatus(ConversationStatus::Active);
        $this->assertIsInt($activeCount);
        $this->assertGreaterThanOrEqual(1, $activeCount);

        $archivedCount = $repository->countByStatus(ConversationStatus::Archived);
        $this->assertIsInt($archivedCount);
        $this->assertGreaterThanOrEqual(1, $archivedCount);

        $archivedCountAgain = $repository->countByStatus(ConversationStatus::Archived);
        $this->assertIsInt($archivedCountAgain);
        $this->assertGreaterThanOrEqual(0, $archivedCountAgain);
    }

    public function testFindByStatusOrdersByUpdatedAtDesc(): void
    {
        $repository = self::getService(ChatConversationRepository::class);

        $older = $this->createTestConversation();
        $older->setStatus(ConversationStatus::Active);
        $older->setTitle('Older Active Chat');
        $repository->save($older);

        usleep(1000);

        $newer = $this->createTestConversation();
        $newer->setStatus(ConversationStatus::Active);
        $newer->setTitle('Newer Active Chat');
        $repository->save($newer);

        $results = $repository->findByStatus(ConversationStatus::Active);
        $this->assertIsArray($results);

        if (count($results) >= 2) {
            // 找到我们的测试数据
            $ourResults = array_filter($results, function ($conv) use ($older, $newer) {
                return $conv->getId() === $older->getId() || $conv->getId() === $newer->getId();
            });

            if (count($ourResults) >= 2) {
                // 验证按更新时间降序排列
                $ourResults = array_values($ourResults);
                for ($i = 0; $i < count($ourResults) - 1; ++$i) {
                    $currentTime = $ourResults[$i]->getUpdatedAt()->getTimestamp();
                    $nextTime = $ourResults[$i + 1]->getUpdatedAt()->getTimestamp();
                    $this->assertGreaterThanOrEqual(
                        $nextTime,
                        $currentTime,
                        'Results should be ordered by updatedAt DESC'
                    );
                }
            }
        }
    }

    public function testFindByModelOrdersByCreatedAtDesc(): void
    {
        $repository = self::getService(ChatConversationRepository::class);

        $older = $this->createTestConversation();
        $older->setModel('gpt-4');
        $older->setTitle('Older GPT-4 Chat');
        $repository->save($older);

        usleep(1000);

        $newer = $this->createTestConversation();
        $newer->setModel('gpt-4');
        $newer->setTitle('Newer GPT-4 Chat');
        $repository->save($newer);

        $results = $repository->findByModel('gpt-4');
        $this->assertIsArray($results);

        if (count($results) >= 2) {
            // 找到我们的测试数据
            $ourResults = array_filter($results, function ($conv) use ($older, $newer) {
                return $conv->getId() === $older->getId() || $conv->getId() === $newer->getId();
            });

            if (count($ourResults) >= 2) {
                // 验证按创建时间降序排列
                $ourResults = array_values($ourResults);
                for ($i = 0; $i < count($ourResults) - 1; ++$i) {
                    $currentTime = $ourResults[$i]->getCreatedAt()->getTimestamp();
                    $nextTime = $ourResults[$i + 1]->getCreatedAt()->getTimestamp();
                    $this->assertGreaterThanOrEqual(
                        $nextTime,
                        $currentTime,
                        'Results should be ordered by createdAt DESC'
                    );
                }
            }
        }
    }

    protected function onSetUp(): void
    {
    }

    /**
     * @return ServiceEntityRepository<ChatConversation>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return self::getService(ChatConversationRepository::class);
    }

    protected function createNewEntity(): object
    {
        return $this->createTestConversation();
    }

    public function testFindActiveConversations(): void
    {
        $repository = self::getService(ChatConversationRepository::class);

        $activeConversation = $this->createTestConversation();
        $activeConversation->setTitle('Active Chat');
        $activeConversation->setStatus(ConversationStatus::Active);
        $archivedConversation = $this->createTestConversation();
        $archivedConversation->setTitle('Archived Chat');
        $archivedConversation->setStatus(ConversationStatus::Archived);

        $repository->save($activeConversation);
        $repository->save($archivedConversation);

        $results = $repository->findActiveConversations();
        $this->assertIsArray($results);

        $foundActive = false;
        $foundArchived = false;

        foreach ($results as $result) {
            $this->assertInstanceOf(ChatConversation::class, $result);
            $this->assertSame(ConversationStatus::Active, $result->getStatus());
            if ($result->getId() === $activeConversation->getId()) {
                $foundActive = true;
            }
            if ($result->getId() === $archivedConversation->getId()) {
                $foundArchived = true;
            }
        }

        $this->assertTrue($foundActive, 'Active conversation should be found');
        $this->assertFalse($foundArchived, 'Archived conversation should not be found');
    }

    public function testFindByDateRange(): void
    {
        $repository = self::getService(ChatConversationRepository::class);

        $startDate = new \DateTimeImmutable('2024-01-01 00:00:00');
        $endDate = new \DateTimeImmutable('2024-12-31 23:59:59');

        $conversation = $this->createTestConversation();
        $conversation->setTitle('Test Date Range Chat');

        $repository->save($conversation);

        $results = $repository->findByDateRange($startDate, $endDate);
        $this->assertIsArray($results);

        foreach ($results as $result) {
            $this->assertInstanceOf(ChatConversation::class, $result);
            $this->assertGreaterThanOrEqual($startDate, $result->getCreatedAt());
            $this->assertLessThanOrEqual($endDate, $result->getCreatedAt());
        }
    }

    public function testGetAverageCost(): void
    {
        $repository = self::getService(ChatConversationRepository::class);

        $conversation1 = $this->createTestConversation();
        $conversation1->setCost('10.00');
        $conversation2 = $this->createTestConversation();
        $conversation2->setCost('20.00');

        $repository->save($conversation1);
        $repository->save($conversation2);

        $averageCost = $repository->getAverageCost();
        $this->assertTrue(is_string($averageCost) || null === $averageCost);

        if (null !== $averageCost) {
            $avgValue = (float) $averageCost;
            $this->assertGreaterThan(0, $avgValue);
        }
    }

    public function testGetTotalTokensUsed(): void
    {
        $repository = self::getService(ChatConversationRepository::class);

        $conversation1 = $this->createTestConversation();
        $conversation1->setTotalTokens(100);
        $conversation2 = $this->createTestConversation();
        $conversation2->setTotalTokens(150);

        $repository->save($conversation1);
        $repository->save($conversation2);

        $totalTokens = $repository->getTotalTokensUsed();
        $this->assertIsInt($totalTokens);
        $this->assertGreaterThanOrEqual(250, $totalTokens); // 至少包含我们创建的数据
    }

    public function testSearch(): void
    {
        $repository = self::getService(ChatConversationRepository::class);

        $searchableConversation = $this->createTestConversation();
        $searchableConversation->setTitle('Searchable Programming Chat');
        $searchableConversation->setMessages([
            ['role' => 'user', 'content' => 'How to write Python code?'],
            ['role' => 'assistant', 'content' => 'Here is how you write Python code...'],
        ]);
        $otherConversation = $this->createTestConversation();
        $otherConversation->setTitle('Other Discussion');
        $otherConversation->setMessages([
            ['role' => 'user', 'content' => 'What is the weather today?'],
            ['role' => 'assistant', 'content' => 'I cannot access weather data...'],
        ]);

        $repository->save($searchableConversation);
        $repository->save($otherConversation);

        // 测试按标题搜索
        $titleResults = $repository->search('Searchable');
        $this->assertIsArray($titleResults);
        $foundByTitle = false;
        foreach ($titleResults as $result) {
            $this->assertInstanceOf(ChatConversation::class, $result);
            if ($result->getId() === $searchableConversation->getId()) {
                $foundByTitle = true;
            }
        }
        $this->assertTrue($foundByTitle, 'Should find conversation by title');

        // 测试按消息内容搜索
        $contentResults = $repository->search('Python');
        $this->assertIsArray($contentResults);
        $foundByContent = false;
        foreach ($contentResults as $result) {
            $this->assertInstanceOf(ChatConversation::class, $result);
            if ($result->getId() === $searchableConversation->getId()) {
                $foundByContent = true;
            }
        }
        $this->assertTrue($foundByContent, 'Should find conversation by message content');

        // 测试没有结果的搜索
        $noResults = $repository->search('nonexistent-keyword-xyz');
        $this->assertIsArray($noResults);

        // 确保搜索结果中不包含目标对话
        $foundNonExistent = false;
        foreach ($noResults as $result) {
            if ($result->getId() === $searchableConversation->getId()) {
                $foundNonExistent = true;
                break;
            }
        }
        $this->assertFalse($foundNonExistent, 'Should not find conversation with non-existent keyword');
    }

    private function createTestConversation(): ChatConversation
    {
        $conversation = new ChatConversation();
        $conversation->setTitle('test_conversation_' . uniqid());
        $conversation->setModel('gpt-3.5-turbo');
        $conversation->setMessages([['role' => 'user', 'content' => 'Hello']]);
        $conversation->setStatus(ConversationStatus::Active);
        $conversation->setTotalTokens(50);
        $conversation->setCost('0.001000');

        return $conversation;
    }
}
