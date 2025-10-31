<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\OpenAiApiBundle\Entity\ChatConversation;
use Tourze\OpenAiApiBundle\Enum\ConversationStatus;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * @extends ServiceEntityRepository<ChatConversation>
 */
#[AsRepository(entityClass: ChatConversation::class)]
class ChatConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChatConversation::class);
    }

    /**
     * @return ChatConversation[]
     */
    public function findByStatus(ConversationStatus $status): array
    {
        /** @var ChatConversation[] */
        return $this->createQueryBuilder('c')
            ->andWhere('c.status = :status')
            ->setParameter('status', $status)
            ->orderBy('c.updatedAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return ChatConversation[]
     */
    public function findByModel(string $model): array
    {
        /** @var ChatConversation[] */
        return $this->createQueryBuilder('c')
            ->andWhere('c.model = :model')
            ->setParameter('model', $model)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return ChatConversation[]
     */
    public function findRecentConversations(int $limit = 10): array
    {
        /** @var ChatConversation[] */
        return $this->createQueryBuilder('c')
            ->orderBy('c.updatedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findTotalCostByDateRange(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): ?string
    {
        $result = $this->createQueryBuilder('c')
            ->select('SUM(c.cost) as totalCost')
            ->andWhere('c.createdAt >= :startDate')
            ->andWhere('c.createdAt <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return null !== $result ? (string) $result : null;
    }

    public function countByStatus(ConversationStatus $status): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('c.status = :status')
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * 保存对话实体
     */
    public function save(ChatConversation $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * 删除对话实体
     */
    public function remove(ChatConversation $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * 查找活跃的对话
     *
     * @return ChatConversation[]
     */
    public function findActiveConversations(): array
    {
        return $this->findByStatus(ConversationStatus::Active);
    }

    /**
     * 根据日期范围查找对话
     *
     * @return ChatConversation[]
     */
    public function findByDateRange(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): array
    {
        /** @var ChatConversation[] */
        return $this->createQueryBuilder('c')
            ->andWhere('c.createdAt >= :startDate')
            ->andWhere('c.createdAt <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 获取平均成本
     */
    public function getAverageCost(): ?string
    {
        $result = $this->createQueryBuilder('c')
            ->select('AVG(c.cost) as avgCost')
            ->andWhere('c.cost IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return null !== $result ? (string) $result : null;
    }

    /**
     * 获取总使用的令牌数
     */
    public function getTotalTokensUsed(): int
    {
        $result = $this->createQueryBuilder('c')
            ->select('SUM(c.totalTokens) as totalTokens')
            ->andWhere('c.totalTokens IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return null !== $result ? (int) $result : 0;
    }

    /**
     * 搜索对话（按标题或消息内容）
     *
     * @return ChatConversation[]
     */
    public function search(string $query): array
    {
        /** @var ChatConversation[] */
        return $this->createQueryBuilder('c')
            ->andWhere('c.title LIKE :query OR c.messages LIKE :searchQuery')
            ->setParameter('query', '%' . $query . '%')
            ->setParameter('searchQuery', '%' . $query . '%')
            ->orderBy('c.updatedAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
