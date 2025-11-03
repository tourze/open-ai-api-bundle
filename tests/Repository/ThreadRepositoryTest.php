<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception\InvalidFieldNameException;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\OpenAiApiBundle\Entity\Thread;
use Tourze\OpenAiApiBundle\Enum\ThreadStatus;
use Tourze\OpenAiApiBundle\Repository\ThreadRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @template TEntity of Thread
 * @internal
 */
#[CoversClass(ThreadRepository::class)]
#[RunTestsInSeparateProcesses]
final class ThreadRepositoryTest extends AbstractRepositoryTestCase
{
    public function testSaveShouldPersistEntity(): void
    {
        $repository = self::getService(ThreadRepository::class);
        $thread = $this->createTestThread();

        $repository->save($thread);

        $this->assertNotNull($thread->getId());

        $foundThread = $repository->find($thread->getId());
        $this->assertInstanceOf(Thread::class, $foundThread);
        $this->assertSame($thread->getThreadId(), $foundThread->getThreadId());
    }

    public function testRemoveShouldDeleteEntity(): void
    {
        $repository = self::getService(ThreadRepository::class);
        $thread = $this->createTestThread();
        $repository->save($thread);
        $threadId = $thread->getId();

        $repository->remove($thread);

        $foundThread = $repository->find($threadId);
        $this->assertNull($foundThread);
    }

    public function testFindByStatus(): void
    {
        $repository = self::getService(ThreadRepository::class);
        $testThreads = $this->createStatusTestThreads($repository);

        $activeResults = $repository->findByStatus(ThreadStatus::Active);
        $this->assertIsArray($activeResults);
        $this->assertNotEmpty($activeResults);
        $this->assertContainsThreadById($activeResults, $testThreads['active'], 'Active thread should be found');

        $archivedResults = $repository->findByStatus(ThreadStatus::Archived);
        $this->assertIsArray($archivedResults);
        $this->assertContainsThreadById($archivedResults, $testThreads['archived'], 'Archived thread should be found');
    }

    public function testFindActive(): void
    {
        $repository = self::getService(ThreadRepository::class);

        $activeThread = $this->createTestThread();
        $activeThread->setTitle('Active Thread');
        $activeThread->setStatus(ThreadStatus::Active);
        $deletedThread = $this->createTestThread();
        $deletedThread->setThreadId('thread-deleted');
        $deletedThread->setTitle('Deleted Thread');
        $deletedThread->setStatus(ThreadStatus::Deleted);

        $repository->save($activeThread);
        $repository->save($deletedThread);

        $results = $repository->findActive();
        $this->assertIsArray($results);
        $this->assertAllThreadsHaveStatus($results, ThreadStatus::Active);
        $this->assertContainsThreadById($results, $activeThread, 'Active thread should be found');
        $this->assertNotContainsThreadById($results, $deletedThread, 'Deleted thread should not be found');
    }

    public function testFindByThreadId(): void
    {
        $repository = self::getService(ThreadRepository::class);

        $thread = $this->createTestThread();
        $thread->setThreadId('thread-unique-123');
        $thread->setTitle('Unique Thread');

        $repository->save($thread);

        $foundThread = $repository->findByThreadId('thread-unique-123');
        $this->assertInstanceOf(Thread::class, $foundThread);
        $this->assertSame('thread-unique-123', $foundThread->getThreadId());
        $this->assertSame($thread->getId(), $foundThread->getId());

        $notFoundThread = $repository->findByThreadId('thread-non-existent');
        $this->assertNull($notFoundThread);
    }

    public function testFindByAssistantId(): void
    {
        $repository = self::getService(ThreadRepository::class);
        $testThreads = $this->createAssistantTestThreads($repository);

        $results = $repository->findByAssistantId('asst-123');
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
        $this->assertContainsThreadById($results, $testThreads['thread1'], 'Thread 1 should be found');
        $this->assertContainsThreadById($results, $testThreads['thread3'], 'Thread 3 should be found');
        $this->assertNotContainsThreadById($results, $testThreads['thread2'], 'Thread 2 should not be found');
    }

    public function testFindByStatusOrdersByUpdatedAtDesc(): void
    {
        $repository = self::getService(ThreadRepository::class);

        $older = $this->createTestThread();
        $older->setThreadId('thread-older');
        $older->setStatus(ThreadStatus::Active);
        $older->setTitle('Older Active Thread');
        $repository->save($older);

        usleep(100000);

        $newer = $this->createTestThread();
        $newer->setThreadId('thread-newer');
        $newer->setStatus(ThreadStatus::Active);
        $newer->setTitle('Newer Active Thread');
        $repository->save($newer);

        $results = $repository->findByStatus(ThreadStatus::Active);
        $this->assertIsArray($results);
        $this->assertDescendingOrder($results, 'updatedAt');
    }

    public function testFindByAssistantIdOrdersByUpdatedAtDesc(): void
    {
        $repository = self::getService(ThreadRepository::class);
        $assistantId = 'asst-test-order-' . uniqid();

        $older = $this->createTestThread();
        $older->setThreadId('thread-older-asst');
        $older->setAssistantId($assistantId);
        $older->setTitle('Older Thread for Assistant');
        $repository->save($older);

        usleep(100000);

        $newer = $this->createTestThread();
        $newer->setThreadId('thread-newer-asst');
        $newer->setAssistantId($assistantId);
        $newer->setTitle('Newer Thread for Assistant');
        $repository->save($newer);

        $results = $repository->findByAssistantId($assistantId);
        $this->assertIsArray($results);
        $this->assertGreaterThanOrEqual(2, count($results));
        $this->assertDescendingOrder($results, 'updatedAt');
    }

    public function testBasicRepositoryFunctionality(): void
    {
        $repository = self::getService(ThreadRepository::class);

        // 测试基本的查找功能
        $thread1 = $this->createTestThread();
        $thread2 = $this->createTestThread();
        $thread2->setThreadId('thread-second');
        $thread2->setTitle('Second Thread');

        $repository->save($thread1);
        $repository->save($thread2);

        // 测试 findAll
        $allThreads = $repository->findAll();
        $this->assertIsArray($allThreads);
        $this->assertGreaterThanOrEqual(2, count($allThreads));

        // 测试 findBy
        $foundByStatus = $repository->findBy(['status' => ThreadStatus::Active]);
        $this->assertIsArray($foundByStatus);
        $this->assertGreaterThanOrEqual(1, count($foundByStatus));

        // 测试 findOneBy
        $foundOne = $repository->findOneBy(['threadId' => $thread2->getThreadId()]);
        $this->assertInstanceOf(Thread::class, $foundOne);
        $this->assertSame($thread2->getId(), $foundOne->getId());

        // 测试 count
        $totalCount = $repository->count([]);
        $this->assertIsInt($totalCount);
        $this->assertGreaterThanOrEqual(2, $totalCount);

        $statusCount = $repository->count(['status' => ThreadStatus::Active]);
        $this->assertIsInt($statusCount);
        $this->assertGreaterThanOrEqual(2, $statusCount);
    }

    public function testThreadProperties(): void
    {
        $repository = self::getService(ThreadRepository::class);

        $thread = $this->createTestThread();
        $thread->setTitle('Test Properties');
        $thread->setDescription('Thread for testing properties');
        $thread->setMessageCount(5);
        $thread->setAssistantId('asst-prop-test');
        $thread->setToolResources(['code_interpreter' => ['file_ids' => ['file-123']]]);

        $repository->save($thread);

        $foundThread = $repository->find($thread->getId());
        $this->assertInstanceOf(Thread::class, $foundThread);
        $this->assertSame('Test Properties', $foundThread->getTitle());
        $this->assertSame('Thread for testing properties', $foundThread->getDescription());
        $this->assertSame(5, $foundThread->getMessageCount());
        $this->assertSame('asst-prop-test', $foundThread->getAssistantId());
        $this->assertSame(['code_interpreter' => ['file_ids' => ['file-123']]], $foundThread->getToolResources());
    }

    protected function onSetUp(): void
    {
    }

    /**
     * @return ServiceEntityRepository<Thread>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return self::getService(ThreadRepository::class);
    }

    protected function createNewEntity(): object
    {
        return $this->createTestThread();
    }

    public function testFindActiveThreads(): void
    {
        $repository = self::getService(ThreadRepository::class);

        $activeThread = $this->createTestThread();
        $activeThread->setTitle('Active Thread via Alias');
        $activeThread->setStatus(ThreadStatus::Active);
        $archivedThread = $this->createTestThread();
        $archivedThread->setThreadId('thread-archived-alias');
        $archivedThread->setTitle('Archived Thread');
        $archivedThread->setStatus(ThreadStatus::Archived);

        $repository->save($activeThread);
        $repository->save($archivedThread);

        $results = $repository->findActiveThreads();
        $this->assertIsArray($results);
        $this->assertAllThreadsHaveStatus($results, ThreadStatus::Active);
        $this->assertContainsThreadById($results, $activeThread, 'Active thread should be found via findActiveThreads');
        $this->assertNotContainsThreadById($results, $archivedThread, 'Archived thread should not be found via findActiveThreads');
    }

    public function testFindByAssistant(): void
    {
        $repository = self::getService(ThreadRepository::class);

        $thread1 = $this->createTestThread();
        $thread1->setThreadId('thread-by-assistant-1');
        $thread1->setAssistantId('asst-alias-test');
        $thread1->setTitle('Thread for Assistant via Alias');
        $thread2 = $this->createTestThread();
        $thread2->setThreadId('thread-by-assistant-2');
        $thread2->setAssistantId('asst-other');
        $thread2->setTitle('Thread for Other Assistant');

        $repository->save($thread1);
        $repository->save($thread2);

        $results = $repository->findByAssistant('asst-alias-test');
        $this->assertIsArray($results);
        $this->assertContainsThreadById($results, $thread1, 'Thread 1 should be found via findByAssistant');
        $this->assertNotContainsThreadById($results, $thread2, 'Thread 2 should not be found via findByAssistant');
    }

    public function testFindRecentlyUpdated(): void
    {
        $repository = self::getService(ThreadRepository::class);

        $thread1 = $this->createTestThread();
        $thread1->setTitle('Thread 1 for Recent Update Test');
        $repository->save($thread1);

        usleep(100000);

        $thread2 = $this->createTestThread();
        $thread2->setThreadId('thread-recent-2');
        $thread2->setTitle('Thread 2 for Recent Update Test');
        $repository->save($thread2);

        $results = $repository->findRecentlyUpdated(10);
        $this->assertIsArray($results);
        $this->assertLessThanOrEqual(10, count($results));
        $this->assertDescendingOrder($results, 'updatedAt');
    }

    public function testFindWithMetadata(): void
    {
        $repository = self::getService(ThreadRepository::class);

        $threadWithMetadata = $this->createTestThread();
        $threadWithMetadata->setTitle('Thread with Metadata');
        $threadWithMetadata->setMetadata(['feature' => 'enabled', 'priority' => 'high']);
        $threadWithoutMetadata = $this->createTestThread();
        $threadWithoutMetadata->setThreadId('thread-no-metadata');
        $threadWithoutMetadata->setTitle('Thread without Metadata');
        $threadWithoutMetadata->setMetadata([]);

        $repository->save($threadWithMetadata);
        $repository->save($threadWithoutMetadata);

        try {
            $results = $repository->findWithMetadata();
            $this->assertIsArray($results);
        } catch (InvalidFieldNameException $e) {
            if (str_contains($e->getMessage(), 'JSON_LENGTH')) {
                self::markTestSkipped('Database does not support JSON_LENGTH function: ' . $e->getMessage());
            }
            throw $e;
        }

        foreach ($results as $result) {
            $this->assertInstanceOf(Thread::class, $result);
            $this->assertNotEmpty($result->getMetadata(), 'Thread should have non-empty metadata');
        }

        $this->assertContainsThreadById($results, $threadWithMetadata, 'Thread with metadata should be found');
        $this->assertNotContainsThreadById($results, $threadWithoutMetadata, 'Thread without metadata should not be found');
    }

    public function testFindWithToolResources(): void
    {
        $repository = self::getService(ThreadRepository::class);

        $threadWithTools = $this->createTestThread();
        $threadWithTools->setTitle('Thread with Tool Resources');
        $threadWithTools->setToolResources(['code_interpreter' => ['file_ids' => ['file-123']]]);
        $threadWithoutTools = $this->createTestThread();
        $threadWithoutTools->setThreadId('thread-no-tools');
        $threadWithoutTools->setTitle('Thread without Tool Resources');
        $threadWithoutTools->setToolResources([]);

        $repository->save($threadWithTools);
        $repository->save($threadWithoutTools);

        $results = $repository->findWithToolResources();
        $this->assertIsArray($results);

        foreach ($results as $result) {
            $this->assertInstanceOf(Thread::class, $result);
            $this->assertNotEmpty($result->getToolResources(), 'Thread should have non-empty tool resources');
        }

        $this->assertContainsThreadById($results, $threadWithTools, 'Thread with tool resources should be found');
        $this->assertNotContainsThreadById($results, $threadWithoutTools, 'Thread without tool resources should not be found');
    }

    public function testFindWithoutTitle(): void
    {
        $repository = self::getService(ThreadRepository::class);

        $threadWithTitle = $this->createTestThread();
        $threadWithTitle->setTitle('Thread with Title');
        $threadWithoutTitle = $this->createTestThread();
        $threadWithoutTitle->setThreadId('thread-no-title');
        $threadWithoutTitle->setTitle(null);

        $repository->save($threadWithTitle);
        $repository->save($threadWithoutTitle);

        $results = $repository->findWithoutTitle();
        $this->assertIsArray($results);

        foreach ($results as $result) {
            $this->assertInstanceOf(Thread::class, $result);
            $this->assertNull($result->getTitle(), 'Thread should have null title');
        }

        $this->assertNotContainsThreadById($results, $threadWithTitle, 'Thread with title should not be found');
        $this->assertContainsThreadById($results, $threadWithoutTitle, 'Thread without title should be found');
    }

    public function testSearch(): void
    {
        $repository = self::getService(ThreadRepository::class);

        $searchableThread = $this->createTestThread();
        $searchableThread->setTitle('Searchable Programming Thread');
        $searchableThread->setDescription('This thread is about coding and programming');
        $otherThread = $this->createTestThread();
        $otherThread->setThreadId('thread-other-search');
        $otherThread->setTitle('Other Thread');
        $otherThread->setDescription('This thread is about weather');

        $repository->save($searchableThread);
        $repository->save($otherThread);

        $results = $repository->search('Searchable');
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
        $this->assertContainsThreadById($results, $searchableThread, 'Should find thread by title');

        $results2 = $repository->search('programming');
        $this->assertContainsThreadById($results2, $searchableThread, 'Should find thread by description');

        $results3 = $repository->search('nonexistent-keyword-xyz');
        $this->assertNotContainsThreadById($results3, $searchableThread, 'Should not find thread with non-existent keyword');
    }

    public function testFindByMessageCountRange(): void
    {
        $repository = self::getService(ThreadRepository::class);

        $lowMessageThread = $this->createTestThread();
        $lowMessageThread->setMessageCount(3);
        $lowMessageThread->setTitle('Low Message Thread');
        $midMessageThread = $this->createTestThread();
        $midMessageThread->setThreadId('thread-mid-msg');
        $midMessageThread->setMessageCount(15);
        $midMessageThread->setTitle('Mid Message Thread');
        $highMessageThread = $this->createTestThread();
        $highMessageThread->setThreadId('thread-high-msg');
        $highMessageThread->setMessageCount(50);
        $highMessageThread->setTitle('High Message Thread');

        $repository->save($lowMessageThread);
        $repository->save($midMessageThread);
        $repository->save($highMessageThread);

        $results = $repository->findByMessageCountRange(10, 20);
        $this->assertIsArray($results);
        foreach ($results as $result) {
            $this->assertGreaterThanOrEqual(10, $result->getMessageCount());
            $this->assertLessThanOrEqual(20, $result->getMessageCount());
        }
        $this->assertContainsThreadById($results, $midMessageThread, 'Mid thread should be found');
        $this->assertNotContainsThreadById($results, $lowMessageThread, 'Low thread should not be found');
        $this->assertNotContainsThreadById($results, $highMessageThread, 'High thread should not be found');
    }

    public function testFindByDateRange(): void
    {
        $repository = self::getService(ThreadRepository::class);

        $now = new \DateTimeImmutable();
        $startDate = $now->modify('-1 hour');
        $endDate = $now->modify('+1 hour');

        $thread1 = $this->createTestThread();
        $thread1->setTitle('Thread 1 in Range');
        $repository->save($thread1);

        usleep(100000);

        $thread2 = $this->createTestThread();
        $thread2->setThreadId('thread-2');
        $thread2->setTitle('Thread 2 in Range');
        $repository->save($thread2);

        $results = $repository->findByDateRange($startDate, $endDate);
        $this->assertIsArray($results);

        foreach ($results as $result) {
            $this->assertInstanceOf(Thread::class, $result);
            $this->assertGreaterThanOrEqual($startDate, $result->getCreatedAt());
            $this->assertLessThanOrEqual($endDate, $result->getCreatedAt());
        }

        $this->assertContainsThreadById($results, $thread1, 'Thread 1 should be found in date range');
        $this->assertContainsThreadById($results, $thread2, 'Thread 2 should be found in date range');
    }

    public function testFindActiveWithManyMessages(): void
    {
        $repository = self::getService(ThreadRepository::class);

        $activeThreadWithManyMsg = $this->createTestThread();
        $activeThreadWithManyMsg->setStatus(ThreadStatus::Active);
        $activeThreadWithManyMsg->setMessageCount(25);
        $activeThreadWithManyMsg->setTitle('Active Thread with Many Messages');
        $activeThreadWithFewMsg = $this->createTestThread();
        $activeThreadWithFewMsg->setThreadId('thread-few-msg');
        $activeThreadWithFewMsg->setStatus(ThreadStatus::Active);
        $activeThreadWithFewMsg->setMessageCount(5);
        $activeThreadWithFewMsg->setTitle('Active Thread with Few Messages');
        $archivedThreadWithManyMsg = $this->createTestThread();
        $archivedThreadWithManyMsg->setThreadId('thread-archived-many');
        $archivedThreadWithManyMsg->setStatus(ThreadStatus::Archived);
        $archivedThreadWithManyMsg->setMessageCount(30);
        $archivedThreadWithManyMsg->setTitle('Archived Thread with Many Messages');

        $repository->save($activeThreadWithManyMsg);
        $repository->save($activeThreadWithFewMsg);
        $repository->save($archivedThreadWithManyMsg);

        $results = $repository->findActiveWithManyMessages(10);
        $this->assertIsArray($results);

        foreach ($results as $result) {
            $this->assertInstanceOf(Thread::class, $result);
            $this->assertSame(ThreadStatus::Active, $result->getStatus());
            $this->assertGreaterThanOrEqual(10, $result->getMessageCount());
        }

        $this->assertContainsThreadById($results, $activeThreadWithManyMsg, 'Active thread with many messages should be found');
        $this->assertNotContainsThreadById($results, $activeThreadWithFewMsg, 'Active thread with few messages should not be found');
        $this->assertNotContainsThreadById($results, $archivedThreadWithManyMsg, 'Archived thread should not be found');
    }

    public function testFindRecentlyActive(): void
    {
        $repository = self::getService(ThreadRepository::class);

        $activeThread1 = $this->createTestThread();
        $activeThread1->setStatus(ThreadStatus::Active);
        $activeThread1->setTitle('Recently Active Thread 1');
        $activeThread2 = $this->createTestThread();
        $activeThread2->setThreadId('thread-active2');
        $activeThread2->setStatus(ThreadStatus::Active);
        $activeThread2->setTitle('Recently Active Thread 2');
        $archivedThread = $this->createTestThread();
        $archivedThread->setThreadId('thread-archived');
        $archivedThread->setStatus(ThreadStatus::Archived);
        $archivedThread->setTitle('Archived Thread');

        $repository->save($activeThread1);
        $repository->save($activeThread2);
        $repository->save($archivedThread);

        $results = $repository->findRecentlyActive(5);
        $this->assertIsArray($results);
        $this->assertLessThanOrEqual(5, count($results));
        $this->assertAllThreadsHaveStatus($results, ThreadStatus::Active);
        $this->assertDescendingOrder($results, 'updatedAt');
        $this->assertContainsThreadById($results, $activeThread1, 'Active thread 1 should be found');
        $this->assertContainsThreadById($results, $activeThread2, 'Active thread 2 should be found');
        $this->assertNotContainsThreadById($results, $archivedThread, 'Archived thread should not be found');
    }

    public function testFindRecentlyCreated(): void
    {
        $repository = self::getService(ThreadRepository::class);

        $thread1 = $this->createTestThread();
        $thread1->setTitle('Recently Created Thread 1');
        $repository->save($thread1);

        usleep(100000);

        $thread2 = $this->createTestThread();
        $thread2->setThreadId('thread-created2');
        $thread2->setTitle('Recently Created Thread 2');
        $repository->save($thread2);

        $results = $repository->findRecentlyCreated(5);
        $this->assertIsArray($results);
        $this->assertLessThanOrEqual(5, count($results));
        $this->assertDescendingOrder($results, 'createdAt');
        $this->assertContainsThreadById($results, $thread1, 'Thread 1 should be found');
        $this->assertContainsThreadById($results, $thread2, 'Thread 2 should be found');
    }

    public function testGetThreadStatistics(): void
    {
        $repository = self::getService(ThreadRepository::class);

        $activeThread = $this->createTestThread();
        $activeThread->setStatus(ThreadStatus::Active);
        $activeThread->setMessageCount(10);
        $activeThread->setAssistantId('asst-stats-test');
        $activeThread->setToolResources(['code_interpreter' => ['file_ids' => ['file-1']]]);

        $archivedThread = $this->createTestThread();
        $archivedThread->setThreadId('thread-archived-stats');
        $archivedThread->setStatus(ThreadStatus::Archived);
        $archivedThread->setMessageCount(20);
        $archivedThread->setToolResources([]);

        $repository->save($activeThread);
        $repository->save($archivedThread);

        $stats = $repository->getThreadStatistics();
        $this->assertIsArray($stats);

        $this->assertArrayHasKey('total', $stats);
        $this->assertIsInt($stats['total']);
        $this->assertGreaterThanOrEqual(2, $stats['total']);

        $this->assertArrayHasKey('by_status', $stats);
        $this->assertIsArray($stats['by_status']);

        $this->assertArrayHasKey('total_messages', $stats);
        $this->assertIsInt($stats['total_messages']);
        $this->assertGreaterThanOrEqual(30, $stats['total_messages']);

        $this->assertArrayHasKey('average_messages', $stats);
        $this->assertIsNumeric($stats['average_messages']);
        $this->assertGreaterThan(0, (float) $stats['average_messages']);

        $this->assertArrayHasKey('with_assistant', $stats);
        $this->assertIsInt($stats['with_assistant']);
        $this->assertGreaterThanOrEqual(1, $stats['with_assistant']);

        $this->assertArrayHasKey('with_tool_resources', $stats);
        $this->assertIsInt($stats['with_tool_resources']);
        $this->assertGreaterThanOrEqual(1, $stats['with_tool_resources']);
    }

    public function testCountByStatus(): void
    {
        $repository = self::getService(ThreadRepository::class);

        $activeThread1 = $this->createTestThread();
        $activeThread1->setStatus(ThreadStatus::Active);
        $activeThread1->setTitle('Active Thread 1 for Count');

        $activeThread2 = $this->createTestThread();
        $activeThread2->setThreadId('thread-active-count2');
        $activeThread2->setStatus(ThreadStatus::Active);
        $activeThread2->setTitle('Active Thread 2 for Count');

        $archivedThread = $this->createTestThread();
        $archivedThread->setThreadId('thread-archived-count');
        $archivedThread->setStatus(ThreadStatus::Archived);
        $archivedThread->setTitle('Archived Thread for Count');

        $repository->save($activeThread1);
        $repository->save($activeThread2);
        $repository->save($archivedThread);

        $activeCount = $repository->countByStatus(ThreadStatus::Active);
        $this->assertIsInt($activeCount);
        $this->assertGreaterThanOrEqual(2, $activeCount);

        $archivedCount = $repository->countByStatus(ThreadStatus::Archived);
        $this->assertIsInt($archivedCount);
        $this->assertGreaterThanOrEqual(1, $archivedCount);

        $deletedCount = $repository->countByStatus(ThreadStatus::Deleted);
        $this->assertIsInt($deletedCount);
        $this->assertGreaterThanOrEqual(0, $deletedCount);
    }

    public function testFindEmptyThreads(): void
    {
        $repository = self::getService(ThreadRepository::class);

        $emptyThread = $this->createTestThread();
        $emptyThread->setMessageCount(0);
        $emptyThread->setTitle('Empty Thread');
        $nonEmptyThread = $this->createTestThread();
        $nonEmptyThread->setThreadId('thread-non-empty');
        $nonEmptyThread->setMessageCount(5);
        $nonEmptyThread->setTitle('Non-Empty Thread');

        $repository->save($emptyThread);
        $repository->save($nonEmptyThread);

        $results = $repository->findEmptyThreads();
        $this->assertIsArray($results);

        foreach ($results as $result) {
            $this->assertInstanceOf(Thread::class, $result);
            $this->assertSame(0, $result->getMessageCount());
        }

        $this->assertContainsThreadById($results, $emptyThread, 'Empty thread should be found');
        $this->assertNotContainsThreadById($results, $nonEmptyThread, 'Non-empty thread should not be found');
    }

    public function testFindInactiveThreads(): void
    {
        $repository = self::getService(ThreadRepository::class);
        $entityManager = self::getService(EntityManagerInterface::class);

        // 创建一个"旧"线程，模拟很久之前更新的数据
        $inactiveThread = $this->createTestThread();
        $inactiveThread->setStatus(ThreadStatus::Active);
        $inactiveThread->setTitle('Inactive Thread');
        $repository->save($inactiveThread);

        // 使用反射或原生SQL强制设置旧的updatedAt时间戳
        // 这是测试中唯一合理的方式来模拟历史数据
        $oldDate = new \DateTimeImmutable('-2 hours');
        $entityManager->createQuery(
            'UPDATE ' . Thread::class . ' t SET t.updatedAt = :oldDate WHERE t.id = :id'
        )
            ->setParameter('oldDate', $oldDate)
            ->setParameter('id', $inactiveThread->getId())
            ->execute()
        ;

        // 清除实体管理器缓存，确保重新从数据库加载
        $entityManager->clear();

        // 创建一个"新"线程，代表最近活跃的数据
        $activeThread = $this->createTestThread();
        $activeThread->setThreadId('thread-recently-active');
        $activeThread->setStatus(ThreadStatus::Active);
        $activeThread->setTitle('Recently Active Thread');
        $repository->save($activeThread);

        // 设置截止时间为1小时前
        $cutoffDate = new \DateTimeImmutable('-1 hour');

        $results = $repository->findInactiveThreads($cutoffDate);
        $this->assertIsArray($results);

        $foundInactive = false;
        $foundActive = false;

        foreach ($results as $result) {
            $this->assertInstanceOf(Thread::class, $result);
            $this->assertSame(ThreadStatus::Active, $result->getStatus());
            $this->assertLessThan($cutoffDate, $result->getUpdatedAt());

            if ($result->getId() === $inactiveThread->getId()) {
                $foundInactive = true;
            } elseif ($result->getId() === $activeThread->getId()) {
                $foundActive = true;
            }
        }

        $this->assertTrue($foundInactive, 'Inactive thread should be found');
        $this->assertFalse($foundActive, 'Recently active thread should not be found');
    }

    public function testCountByAssistantId(): void
    {
        $repository = self::getService(ThreadRepository::class);

        $thread1 = $this->createTestThread();
        $thread1->setAssistantId('asst-count-test');
        $thread1->setTitle('Thread 1 for Assistant Count');

        $thread2 = $this->createTestThread();
        $thread2->setThreadId('thread-count2');
        $thread2->setAssistantId('asst-count-test');
        $thread2->setTitle('Thread 2 for Assistant Count');

        $thread3 = $this->createTestThread();
        $thread3->setThreadId('thread-other-asst');
        $thread3->setAssistantId('asst-other');
        $thread3->setTitle('Thread for Other Assistant');

        $repository->save($thread1);
        $repository->save($thread2);
        $repository->save($thread3);

        $countForTestAsst = $repository->countByAssistantId('asst-count-test');
        $this->assertIsInt($countForTestAsst);
        $this->assertSame(2, $countForTestAsst);

        $countForOtherAsst = $repository->countByAssistantId('asst-other');
        $this->assertIsInt($countForOtherAsst);
        $this->assertSame(1, $countForOtherAsst);

        $countForNonExistentAsst = $repository->countByAssistantId('asst-non-existent');
        $this->assertIsInt($countForNonExistentAsst);
        $this->assertSame(0, $countForNonExistentAsst);
    }

    /**
     * @return array<string, Thread>
     */
    private function createStatusTestThreads(ThreadRepository $repository): array
    {
        $activeThread = $this->createTestThread();
        $activeThread->setTitle('Active Thread');
        $activeThread->setStatus(ThreadStatus::Active);
        $archivedThread = $this->createTestThread();
        $archivedThread->setThreadId('thread-archived');
        $archivedThread->setTitle('Archived Thread');
        $archivedThread->setStatus(ThreadStatus::Archived);

        $repository->save($activeThread);
        $repository->save($archivedThread);

        return ['active' => $activeThread, 'archived' => $archivedThread];
    }

    /**
     * @return array<string, Thread>
     */
    private function createAssistantTestThreads(ThreadRepository $repository): array
    {
        $thread1 = $this->createTestThread();
        $thread1->setThreadId('thread-asst1');
        $thread1->setAssistantId('asst-123');
        $thread1->setTitle('Thread for Assistant 123');
        $thread2 = $this->createTestThread();
        $thread2->setThreadId('thread-asst2');
        $thread2->setAssistantId('asst-456');
        $thread2->setTitle('Thread for Assistant 456');
        $thread3 = $this->createTestThread();
        $thread3->setThreadId('thread-asst3');
        $thread3->setAssistantId('asst-123');
        $thread3->setTitle('Another Thread for Assistant 123');

        $repository->save($thread1);
        $repository->save($thread2);
        $repository->save($thread3);

        return ['thread1' => $thread1, 'thread2' => $thread2, 'thread3' => $thread3];
    }

    /**
     * @param array<Thread> $results
     */
    private function assertAllThreadsHaveStatus(array $results, ThreadStatus $expectedStatus): void
    {
        foreach ($results as $result) {
            $this->assertInstanceOf(Thread::class, $result);
            $this->assertSame($expectedStatus, $result->getStatus());
        }
    }

    /**
     * @param array<Thread> $results
     */
    private function assertContainsThreadById(array $results, Thread $targetThread, string $message): void
    {
        $found = false;
        foreach ($results as $result) {
            if ($result->getId() === $targetThread->getId()) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, $message);
    }

    /**
     * @param array<Thread> $results
     */
    private function assertNotContainsThreadById(array $results, Thread $targetThread, string $message): void
    {
        $found = false;
        foreach ($results as $result) {
            if ($result->getId() === $targetThread->getId()) {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found, $message);
    }

    /**
     * @param array<Thread> $results
     */
    private function assertDescendingOrder(array $results, string $property): void
    {
        if (count($results) < 2) {
            return;
        }

        for ($i = 0; $i < count($results) - 1; ++$i) {
            $currentTime = 'createdAt' === $property ? $results[$i]->getCreatedAt() : $results[$i]->getUpdatedAt();
            $nextTime = 'createdAt' === $property ? $results[$i + 1]->getCreatedAt() : $results[$i + 1]->getUpdatedAt();
            $this->assertGreaterThanOrEqual($nextTime->getTimestamp(), $currentTime->getTimestamp(), "Results should be ordered by {$property} DESC");
        }
    }

    private function createTestThread(): Thread
    {
        $thread = new Thread();
        $thread->setThreadId('thread_' . uniqid());
        $thread->setTitle('Test Thread ' . uniqid());
        $thread->setMetadata(['test' => true]);
        $thread->setStatus(ThreadStatus::Active);
        $thread->setMessageCount(0);
        $thread->setAssistantId('asst-test');
        $thread->setDescription('Test thread description');
        $thread->setToolResources([]);

        return $thread;
    }
}
