<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\OpenAiApiBundle\Entity\AIModel;
use Tourze\OpenAiApiBundle\Enum\ModelStatus;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * @extends ServiceEntityRepository<AIModel>
 */
#[AsRepository(entityClass: AIModel::class)]
class AIModelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AIModel::class);
    }

    /**
     * 根据状态查找模型
     *
     * @return AIModel[]
     */
    public function findByStatus(ModelStatus $status): array
    {
        /** @var AIModel[] */
        return $this->createQueryBuilder('m')
            ->andWhere('m.status = :status')
            ->setParameter('status', $status)
            ->orderBy('m.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找活跃的可用模型
     *
     * @return AIModel[]
     */
    public function findActiveAvailableModels(): array
    {
        /** @var AIModel[] */
        return $this->createQueryBuilder('m')
            ->andWhere('m.status = :status')
            ->andWhere('m.isActive = :active')
            ->setParameter('status', ModelStatus::Available)
            ->setParameter('active', true)
            ->orderBy('m.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 根据所有者查找模型
     *
     * @return AIModel[]
     */
    public function findByOwner(string $owner): array
    {
        /** @var AIModel[] */
        return $this->createQueryBuilder('m')
            ->andWhere('m.owner = :owner')
            ->setParameter('owner', $owner)
            ->orderBy('m.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 根据模型ID查找模型
     */
    public function findByModelId(string $modelId): ?AIModel
    {
        /** @var AIModel|null */
        return $this->createQueryBuilder('m')
            ->andWhere('m.modelId = :modelId')
            ->setParameter('modelId', $modelId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * 查找具有特定能力的模型
     *
     * @return AIModel[]
     */
    public function findByCapability(string $capability): array
    {
        /** @var AIModel[] */
        return $this->createQueryBuilder('m')
            ->andWhere('m.capabilities LIKE :capability')
            ->setParameter('capability', '%"' . $capability . '"%')
            ->andWhere('m.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('m.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 根据上下文窗口范围查找模型
     *
     * @return AIModel[]
     */
    public function findByContextWindowRange(?int $minWindow = null, ?int $maxWindow = null): array
    {
        $qb = $this->createQueryBuilder('m')
            ->andWhere('m.contextWindow IS NOT NULL')
            ->andWhere('m.isActive = :active')
            ->setParameter('active', true)
        ;

        if (null !== $minWindow) {
            $qb->andWhere('m.contextWindow >= :minWindow')
                ->setParameter('minWindow', $minWindow)
            ;
        }

        if (null !== $maxWindow) {
            $qb->andWhere('m.contextWindow <= :maxWindow')
                ->setParameter('maxWindow', $maxWindow)
            ;
        }

        /** @var AIModel[] */
        return $qb->orderBy('m.contextWindow', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 根据价格范围查找模型（按输入价格排序）
     *
     * @return AIModel[]
     */
    public function findByPriceRange(?string $maxInputPrice = null, ?string $maxOutputPrice = null): array
    {
        $qb = $this->createQueryBuilder('m')
            ->andWhere('m.isActive = :active')
            ->setParameter('active', true)
        ;

        if (null !== $maxInputPrice) {
            $qb->andWhere('m.inputPricePerToken IS NOT NULL')
                ->andWhere('m.inputPricePerToken <= :maxInputPrice')
                ->setParameter('maxInputPrice', $maxInputPrice)
            ;
        }

        if (null !== $maxOutputPrice) {
            $qb->andWhere('m.outputPricePerToken IS NOT NULL')
                ->andWhere('m.outputPricePerToken <= :maxOutputPrice')
                ->setParameter('maxOutputPrice', $maxOutputPrice)
            ;
        }

        /** @var AIModel[] */
        return $qb->orderBy('m.inputPricePerToken', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 保存模型实体
     */
    public function save(AIModel $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * 删除模型实体
     */
    public function remove(AIModel $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * 获取模型统计信息
     *
     * @return array<string, mixed>
     */
    public function getModelStatistics(): array
    {
        $qb = $this->createQueryBuilder('m');

        return [
            'total' => $qb->select('COUNT(m.id)')
                ->getQuery()
                ->getSingleScalarResult(),
            'active' => $qb->select('COUNT(m.id)')
                ->andWhere('m.isActive = :active')
                ->setParameter('active', true)
                ->getQuery()
                ->getSingleScalarResult(),
            'by_status' => $this->createQueryBuilder('m')
                ->select('m.status as status, COUNT(m.id) as count')
                ->groupBy('m.status')
                ->getQuery()
                ->getResult(),
            'by_owner' => $this->createQueryBuilder('m')
                ->select('m.owner as owner, COUNT(m.id) as count')
                ->groupBy('m.owner')
                ->orderBy('count', 'DESC')
                ->getQuery()
                ->getResult(),
        ];
    }

    /**
     * 搜索模型（按名称或描述）
     *
     * @return AIModel[]
     */
    public function search(string $query): array
    {
        /** @var AIModel[] */
        return $this->createQueryBuilder('m')
            ->andWhere('m.name LIKE :query OR m.description LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->andWhere('m.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('m.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 获取最近更新的模型
     *
     * @return AIModel[]
     */
    public function findRecentlyUpdated(int $limit = 10): array
    {
        /** @var AIModel[] */
        return $this->createQueryBuilder('m')
            ->orderBy('m.updatedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }
}
