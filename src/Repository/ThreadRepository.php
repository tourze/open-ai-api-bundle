<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\OpenAiApiBundle\Entity\Thread;
use Tourze\OpenAiApiBundle\Enum\ThreadStatus;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * @extends ServiceEntityRepository<Thread>
 */
#[AsRepository(entityClass: Thread::class)]
class ThreadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Thread::class);
    }

    /**
     * 根据状态查找线程
     *
     * @return Thread[]
     */
    public function findByStatus(ThreadStatus $status): array
    {
        /** @var Thread[] */
        return $this->createQueryBuilder('t')
            ->andWhere('t.status = :status')
            ->setParameter('status', $status)
            ->orderBy('t.updatedAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找活跃的线程
     *
     * @return Thread[]
     */
    public function findActive(): array
    {
        return $this->findByStatus(ThreadStatus::Active);
    }

    /**
     * 根据线程ID查找线程
     */
    public function findByThreadId(string $threadId): ?Thread
    {
        /** @var Thread|null */
        return $this->createQueryBuilder('t')
            ->andWhere('t.threadId = :threadId')
            ->setParameter('threadId', $threadId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * 根据助手ID查找线程
     *
     * @return Thread[]
     */
    public function findByAssistantId(string $assistantId): array
    {
        /** @var Thread[] */
        return $this->createQueryBuilder('t')
            ->andWhere('t.assistantId = :assistantId')
            ->setParameter('assistantId', $assistantId)
            ->orderBy('t.updatedAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 根据消息数量范围查找线程
     *
     * @return Thread[]
     */
    public function findByMessageCountRange(?int $minCount = null, ?int $maxCount = null): array
    {
        $qb = $this->createQueryBuilder('t');

        if (null !== $minCount) {
            $qb->andWhere('t.messageCount >= :minCount')
                ->setParameter('minCount', $minCount)
            ;
        }

        if (null !== $maxCount) {
            $qb->andWhere('t.messageCount <= :maxCount')
                ->setParameter('maxCount', $maxCount)
            ;
        }

        /** @var Thread[] */
        return $qb->orderBy('t.messageCount', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 根据日期范围查找线程
     *
     * @return Thread[]
     */
    public function findByDateRange(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): array
    {
        /** @var Thread[] */
        return $this->createQueryBuilder('t')
            ->andWhere('t.createdAt >= :startDate')
            ->andWhere('t.createdAt <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 搜索线程（按标题或描述）
     *
     * @return Thread[]
     */
    public function search(string $query): array
    {
        /** @var Thread[] */
        return $this->createQueryBuilder('t')
            ->andWhere('t.title LIKE :query OR t.description LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('t.updatedAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找有工具资源的线程
     *
     * @return Thread[]
     */
    public function findWithToolResources(): array
    {
        /** @var Thread[] */
        return $this->createQueryBuilder('t')
            ->andWhere('t.toolResources IS NOT NULL')
            ->andWhere('t.toolResources NOT LIKE :emptyArray')
            ->andWhere('t.toolResources NOT LIKE :emptyObject')
            ->setParameter('emptyArray', '[]')
            ->setParameter('emptyObject', '{}')
            ->orderBy('t.updatedAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找有元数据的线程
     *
     * @return Thread[]
     */
    public function findWithMetadata(): array
    {
        /** @var Thread[] */
        return $this->createQueryBuilder('t')
            ->andWhere('t.metadata IS NOT NULL')
            ->andWhere('t.metadata NOT LIKE :emptyArray')
            ->andWhere('t.metadata NOT LIKE :emptyObject')
            ->setParameter('emptyArray', '[]')
            ->setParameter('emptyObject', '{}')
            ->orderBy('t.updatedAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找活跃消息多的线程
     *
     * @return Thread[]
     */
    public function findActiveWithManyMessages(int $messageThreshold = 10): array
    {
        /** @var Thread[] */
        return $this->createQueryBuilder('t')
            ->andWhere('t.status = :status')
            ->andWhere('t.messageCount >= :threshold')
            ->setParameter('status', ThreadStatus::Active)
            ->setParameter('threshold', $messageThreshold)
            ->orderBy('t.messageCount', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找最近活跃的线程
     *
     * @return Thread[]
     */
    public function findRecentlyActive(int $limit = 10): array
    {
        /** @var Thread[] */
        return $this->createQueryBuilder('t')
            ->andWhere('t.status = :status')
            ->setParameter('status', ThreadStatus::Active)
            ->orderBy('t.updatedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 保存线程实体
     */
    public function save(Thread $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * 删除线程实体
     */
    public function remove(Thread $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * 获取线程统计信息
     *
     * @return array<string, mixed>
     */
    public function getThreadStatistics(): array
    {
        return [
            'total' => $this->createQueryBuilder('t')
                ->select('COUNT(t.id)')
                ->getQuery()
                ->getSingleScalarResult(),
            'by_status' => $this->createQueryBuilder('t')
                ->select('t.status as status, COUNT(t.id) as count')
                ->groupBy('t.status')
                ->getQuery()
                ->getResult(),
            'total_messages' => $this->createQueryBuilder('t')
                ->select('SUM(t.messageCount)')
                ->getQuery()
                ->getSingleScalarResult(),
            'average_messages' => $this->createQueryBuilder('t')
                ->select('AVG(t.messageCount)')
                ->getQuery()
                ->getSingleScalarResult(),
            'with_assistant' => $this->createQueryBuilder('t')
                ->select('COUNT(t.id)')
                ->andWhere('t.assistantId IS NOT NULL')
                ->getQuery()
                ->getSingleScalarResult(),
            'with_tool_resources' => $this->createQueryBuilder('t')
                ->select('COUNT(t.id)')
                ->andWhere('t.toolResources IS NOT NULL')
                ->andWhere('t.toolResources NOT LIKE :emptyArray')
                ->andWhere('t.toolResources NOT LIKE :emptyObject')
                ->setParameter('emptyArray', '[]')
                ->setParameter('emptyObject', '{}')
                ->getQuery()
                ->getSingleScalarResult(),
        ];
    }

    /**
     * 查找最近创建的线程
     *
     * @return Thread[]
     */
    public function findRecentlyCreated(int $limit = 10): array
    {
        /** @var Thread[] */
        return $this->createQueryBuilder('t')
            ->orderBy('t.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找最近更新的线程
     *
     * @return Thread[]
     */
    public function findRecentlyUpdated(int $limit = 10): array
    {
        /** @var Thread[] */
        return $this->createQueryBuilder('t')
            ->orderBy('t.updatedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 统计指定状态的线程数量
     */
    public function countByStatus(ThreadStatus $status): int
    {
        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->andWhere('t.status = :status')
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * 查找空线程（没有消息的线程）
     *
     * @return Thread[]
     */
    public function findEmptyThreads(): array
    {
        /** @var Thread[] */
        return $this->createQueryBuilder('t')
            ->andWhere('t.messageCount = 0')
            ->orderBy('t.createdAt', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找长时间未活跃的线程
     *
     * @return Thread[]
     */
    public function findInactiveThreads(\DateTimeImmutable $beforeDate): array
    {
        /** @var Thread[] */
        return $this->createQueryBuilder('t')
            ->andWhere('t.updatedAt < :beforeDate')
            ->andWhere('t.status = :status')
            ->setParameter('beforeDate', $beforeDate)
            ->setParameter('status', ThreadStatus::Active)
            ->orderBy('t.updatedAt', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 根据助手ID统计线程数量
     */
    public function countByAssistantId(string $assistantId): int
    {
        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->andWhere('t.assistantId = :assistantId')
            ->setParameter('assistantId', $assistantId)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * 查找没有标题的线程
     *
     * @return Thread[]
     */
    public function findWithoutTitle(): array
    {
        /** @var Thread[] */
        return $this->createQueryBuilder('t')
            ->andWhere('t.title IS NULL')
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找活跃的线程（别名方法）
     *
     * @return Thread[]
     */
    public function findActiveThreads(): array
    {
        return $this->findActive();
    }

    /**
     * 根据助手查找线程（别名方法）
     *
     * @return Thread[]
     */
    public function findByAssistant(string $assistantId): array
    {
        return $this->findByAssistantId($assistantId);
    }
}
