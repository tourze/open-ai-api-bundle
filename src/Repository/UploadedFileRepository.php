<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\OpenAiApiBundle\Entity\UploadedFile;
use Tourze\OpenAiApiBundle\Enum\FileStatus;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * @extends ServiceEntityRepository<UploadedFile>
 */
#[AsRepository(entityClass: UploadedFile::class)]
class UploadedFileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UploadedFile::class);
    }

    /**
     * 根据状态查找文件
     *
     * @return UploadedFile[]
     */
    public function findByStatus(FileStatus $status): array
    {
        /** @var UploadedFile[] */
        return $this->createQueryBuilder('f')
            ->andWhere('f.status = :status')
            ->setParameter('status', $status)
            ->orderBy('f.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 根据目的查找文件
     *
     * @return UploadedFile[]
     */
    public function findByPurpose(string $purpose): array
    {
        /** @var UploadedFile[] */
        return $this->createQueryBuilder('f')
            ->andWhere('f.purpose = :purpose')
            ->setParameter('purpose', $purpose)
            ->orderBy('f.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 根据文件ID查找文件
     */
    public function findByFileId(string $fileId): ?UploadedFile
    {
        /** @var UploadedFile|null */
        return $this->createQueryBuilder('f')
            ->andWhere('f.fileId = :fileId')
            ->setParameter('fileId', $fileId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * 根据MIME类型查找文件
     *
     * @return UploadedFile[]
     */
    public function findByMimeType(string $mimeType): array
    {
        /** @var UploadedFile[] */
        return $this->createQueryBuilder('f')
            ->andWhere('f.mimeType = :mimeType')
            ->setParameter('mimeType', $mimeType)
            ->orderBy('f.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找即将过期的文件
     *
     * @return UploadedFile[]
     */
    public function findExpiring(\DateTimeImmutable $beforeDate): array
    {
        /** @var UploadedFile[] */
        return $this->createQueryBuilder('f')
            ->andWhere('f.expiresAt IS NOT NULL')
            ->andWhere('f.expiresAt <= :beforeDate')
            ->andWhere('f.status != :deletedStatus')
            ->setParameter('beforeDate', $beforeDate)
            ->setParameter('deletedStatus', FileStatus::Deleted)
            ->orderBy('f.expiresAt', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 根据文件大小范围查找文件
     *
     * @return UploadedFile[]
     */
    public function findBySizeRange(?int $minBytes = null, ?int $maxBytes = null): array
    {
        $qb = $this->createQueryBuilder('f');

        if (null !== $minBytes) {
            $qb->andWhere('f.bytes >= :minBytes')
                ->setParameter('minBytes', $minBytes)
            ;
        }

        if (null !== $maxBytes) {
            $qb->andWhere('f.bytes <= :maxBytes')
                ->setParameter('maxBytes', $maxBytes)
            ;
        }

        /** @var UploadedFile[] */
        return $qb->orderBy('f.bytes', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 根据日期范围查找文件
     *
     * @return UploadedFile[]
     */
    public function findByDateRange(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): array
    {
        /** @var UploadedFile[] */
        return $this->createQueryBuilder('f')
            ->andWhere('f.createdAt >= :startDate')
            ->andWhere('f.createdAt <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('f.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 搜索文件（按文件名或描述）
     *
     * @return UploadedFile[]
     */
    public function search(string $query): array
    {
        /** @var UploadedFile[] */
        return $this->createQueryBuilder('f')
            ->andWhere('f.filename LIKE :query OR f.description LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('f.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 保存文件实体
     */
    public function save(UploadedFile $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * 删除文件实体
     */
    public function remove(UploadedFile $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * 获取文件统计信息
     *
     * @return array<string, mixed>
     */
    public function getFileStatistics(): array
    {
        return [
            'total' => $this->createQueryBuilder('f')
                ->select('COUNT(f.id)')
                ->getQuery()
                ->getSingleScalarResult(),
            'total_size' => $this->createQueryBuilder('f')
                ->select('SUM(f.bytes)')
                ->getQuery()
                ->getSingleScalarResult(),
            'by_status' => $this->createQueryBuilder('f')
                ->select('f.status as status, COUNT(f.id) as count, SUM(f.bytes) as totalBytes')
                ->groupBy('f.status')
                ->getQuery()
                ->getResult(),
            'by_purpose' => $this->createQueryBuilder('f')
                ->select('f.purpose as purpose, COUNT(f.id) as count, SUM(f.bytes) as totalBytes')
                ->groupBy('f.purpose')
                ->orderBy('count', 'DESC')
                ->getQuery()
                ->getResult(),
            'by_mime_type' => $this->createQueryBuilder('f')
                ->select('f.mimeType as mimeType, COUNT(f.id) as count')
                ->andWhere('f.mimeType IS NOT NULL')
                ->groupBy('f.mimeType')
                ->orderBy('count', 'DESC')
                ->getQuery()
                ->getResult(),
        ];
    }

    /**
     * 查找大文件（超过指定大小）
     *
     * @return UploadedFile[]
     */
    public function findLargeFiles(int $sizeThresholdBytes = 10485760): array // 默认10MB
    {
        /** @var UploadedFile[] */
        return $this->createQueryBuilder('f')
            ->andWhere('f.bytes >= :threshold')
            ->setParameter('threshold', $sizeThresholdBytes)
            ->orderBy('f.bytes', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 获取最近上传的文件
     *
     * @return UploadedFile[]
     */
    public function findRecentUploads(int $limit = 10): array
    {
        /** @var UploadedFile[] */
        return $this->createQueryBuilder('f')
            ->orderBy('f.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 统计指定状态的文件数量
     */
    public function countByStatus(FileStatus $status): int
    {
        return (int) $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->andWhere('f.status = :status')
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * 获取指定目的的总文件大小
     */
    public function getTotalSizeByPurpose(string $purpose): ?int
    {
        $result = $this->createQueryBuilder('f')
            ->select('SUM(f.bytes)')
            ->andWhere('f.purpose = :purpose')
            ->setParameter('purpose', $purpose)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return null !== $result ? (int) $result : null;
    }

    /**
     * 查找有元数据的文件
     *
     * @return UploadedFile[]
     */
    public function findWithMetadata(): array
    {
        /** @var UploadedFile[] */
        return $this->createQueryBuilder('f')
            ->andWhere('f.metadata IS NOT NULL')
            ->andWhere('f.metadata NOT LIKE :emptyArray')
            ->andWhere('f.metadata NOT LIKE :emptyObject')
            ->setParameter('emptyArray', '[]')
            ->setParameter('emptyObject', '{}')
            ->orderBy('f.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
