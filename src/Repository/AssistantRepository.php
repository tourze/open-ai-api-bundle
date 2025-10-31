<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\OpenAiApiBundle\Entity\Assistant;
use Tourze\OpenAiApiBundle\Enum\AssistantStatus;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * @extends ServiceEntityRepository<Assistant>
 */
#[AsRepository(entityClass: Assistant::class)]
class AssistantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Assistant::class);
    }

    /**
     * 根据状态查找助手
     *
     * @return Assistant[]
     */
    public function findByStatus(AssistantStatus $status): array
    {
        /** @var Assistant[] */
        return $this->createQueryBuilder('a')
            ->andWhere('a.status = :status')
            ->setParameter('status', $status)
            ->orderBy('a.updatedAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找活跃的助手
     *
     * @return Assistant[]
     */
    public function findActive(): array
    {
        return $this->findByStatus(AssistantStatus::Active);
    }

    /**
     * 根据助手ID查找助手
     */
    public function findByAssistantId(string $assistantId): ?Assistant
    {
        /** @var Assistant|null */
        return $this->createQueryBuilder('a')
            ->andWhere('a.assistantId = :assistantId')
            ->setParameter('assistantId', $assistantId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * 根据模型查找助手
     *
     * @return Assistant[]
     */
    public function findByModel(string $model): array
    {
        /** @var Assistant[] */
        return $this->createQueryBuilder('a')
            ->andWhere('a.model = :model')
            ->setParameter('model', $model)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 根据工具查找助手（拥有特定工具的助手）
     *
     * @return Assistant[]
     */
    public function findByTool(string $toolType): array
    {
        /** @var Assistant[] */
        return $this->createQueryBuilder('a')
            ->andWhere('a.tools LIKE :toolType')
            ->setParameter('toolType', '%"' . $toolType . '"%')
            ->andWhere('a.status = :status')
            ->setParameter('status', AssistantStatus::Active)
            ->orderBy('a.updatedAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 根据文件ID查找助手（使用特定文件的助手）
     *
     * @return Assistant[]
     */
    public function findByFileId(string $fileId): array
    {
        /** @var Assistant[] */
        return $this->createQueryBuilder('a')
            ->andWhere('a.fileIds LIKE :fileId')
            ->setParameter('fileId', '%"' . $fileId . '"%')
            ->orderBy('a.updatedAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 根据温度范围查找助手
     *
     * @return Assistant[]
     */
    public function findByTemperatureRange(?float $minTemperature = null, ?float $maxTemperature = null): array
    {
        $qb = $this->createQueryBuilder('a')
            ->andWhere('a.temperature IS NOT NULL')
            ->andWhere('a.status = :status')
            ->setParameter('status', AssistantStatus::Active)
        ;

        if (null !== $minTemperature) {
            $qb->andWhere('a.temperature >= :minTemp')
                ->setParameter('minTemp', (string) $minTemperature)
            ;
        }

        if (null !== $maxTemperature) {
            $qb->andWhere('a.temperature <= :maxTemp')
                ->setParameter('maxTemp', (string) $maxTemperature)
            ;
        }

        /** @var Assistant[] */
        return $qb->orderBy('a.temperature', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 搜索助手（按名称或描述）
     *
     * @return Assistant[]
     */
    public function search(string $query): array
    {
        /** @var Assistant[] */
        return $this->createQueryBuilder('a')
            ->andWhere('a.name LIKE :query OR a.description LIKE :query OR a.instructions LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('a.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 保存助手实体
     */
    public function save(Assistant $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * 删除助手实体
     */
    public function remove(Assistant $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * 获取助手统计信息
     *
     * @return array<string, mixed>
     */
    public function getAssistantStatistics(): array
    {
        return [
            'total' => $this->createQueryBuilder('a')
                ->select('COUNT(a.id)')
                ->getQuery()
                ->getSingleScalarResult(),
            'by_status' => $this->createQueryBuilder('a')
                ->select('a.status as status, COUNT(a.id) as count')
                ->groupBy('a.status')
                ->getQuery()
                ->getResult(),
            'by_model' => $this->createQueryBuilder('a')
                ->select('a.model as model, COUNT(a.id) as count')
                ->groupBy('a.model')
                ->orderBy('count', 'DESC')
                ->getQuery()
                ->getResult(),
            'with_tools' => $this->createQueryBuilder('a')
                ->select('COUNT(a.id)')
                ->andWhere('a.tools IS NOT NULL')
                ->andWhere('a.tools NOT LIKE :emptyArray')
                ->setParameter('emptyArray', '[]')
                ->getQuery()
                ->getSingleScalarResult(),
            'with_files' => $this->createQueryBuilder('a')
                ->select('COUNT(a.id)')
                ->andWhere('a.fileIds IS NOT NULL')
                ->andWhere('a.fileIds NOT LIKE :emptyArray')
                ->setParameter('emptyArray', '[]')
                ->getQuery()
                ->getSingleScalarResult(),
        ];
    }

    /**
     * 查找有特定响应格式的助手
     *
     * @return Assistant[]
     */
    public function findByResponseFormat(string $responseFormat): array
    {
        /** @var Assistant[] */
        return $this->createQueryBuilder('a')
            ->andWhere('a.responseFormat = :responseFormat')
            ->setParameter('responseFormat', $responseFormat)
            ->andWhere('a.status = :status')
            ->setParameter('status', AssistantStatus::Active)
            ->orderBy('a.updatedAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 获取最近创建的助手
     *
     * @return Assistant[]
     */
    public function findRecentlyCreated(int $limit = 10): array
    {
        /** @var Assistant[] */
        return $this->createQueryBuilder('a')
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 获取最近更新的助手
     *
     * @return Assistant[]
     */
    public function findRecentlyUpdated(int $limit = 10): array
    {
        /** @var Assistant[] */
        return $this->createQueryBuilder('a')
            ->orderBy('a.updatedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 统计指定状态的助手数量
     */
    public function countByStatus(AssistantStatus $status): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.status = :status')
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * 查找有元数据的助手
     *
     * @return Assistant[]
     */
    public function findWithMetadata(): array
    {
        /** @var Assistant[] */
        return $this->createQueryBuilder('a')
            ->andWhere('a.metadata IS NOT NULL')
            ->andWhere('a.metadata NOT LIKE :emptyArray')
            ->andWhere('a.metadata NOT LIKE :emptyObject')
            ->setParameter('emptyArray', '[]')
            ->setParameter('emptyObject', '{}')
            ->orderBy('a.updatedAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找没有工具的助手
     *
     * @return Assistant[]
     */
    public function findWithoutTools(): array
    {
        /** @var Assistant[] */
        return $this->createQueryBuilder('a')
            ->andWhere('a.tools IS NULL OR a.tools LIKE :emptyArray')
            ->setParameter('emptyArray', '[]')
            ->andWhere('a.status = :status')
            ->setParameter('status', AssistantStatus::Active)
            ->orderBy('a.updatedAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找没有文件的助手
     *
     * @return Assistant[]
     */
    public function findWithoutFiles(): array
    {
        /** @var Assistant[] */
        return $this->createQueryBuilder('a')
            ->andWhere('a.fileIds IS NULL OR a.fileIds LIKE :emptyArray')
            ->setParameter('emptyArray', '[]')
            ->andWhere('a.status = :status')
            ->setParameter('status', AssistantStatus::Active)
            ->orderBy('a.updatedAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找有温度设置的助手
     *
     * @return Assistant[]
     */
    public function findWithTemperature(): array
    {
        /** @var Assistant[] */
        return $this->createQueryBuilder('a')
            ->andWhere('a.temperature IS NOT NULL')
            ->orderBy('a.temperature', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
