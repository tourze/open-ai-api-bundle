<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception\InvalidFieldNameException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\OpenAiApiBundle\Entity\UploadedFile;
use Tourze\OpenAiApiBundle\Enum\FileStatus;
use Tourze\OpenAiApiBundle\Repository\UploadedFileRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @template TEntity of UploadedFile
 * @internal
 */
#[CoversClass(UploadedFileRepository::class)]
#[RunTestsInSeparateProcesses]
final class UploadedFileRepositoryTest extends AbstractRepositoryTestCase
{
    public function testSaveShouldPersistEntity(): void
    {
        $repository = self::getService(UploadedFileRepository::class);
        $file = $this->createTestFile();

        $repository->save($file);

        $this->assertNotNull($file->getId());

        $foundFile = $repository->find($file->getId());
        $this->assertInstanceOf(UploadedFile::class, $foundFile);
        $this->assertSame($file->getFilename(), $foundFile->getFilename());
    }

    public function testRemoveShouldDeleteEntity(): void
    {
        $repository = self::getService(UploadedFileRepository::class);
        $file = $this->createTestFile();
        $repository->save($file);
        $fileId = $file->getId();

        $repository->remove($file);

        $foundFile = $repository->find($fileId);
        $this->assertNull($foundFile);
    }

    public function testFindByStatus(): void
    {
        $repository = self::getService(UploadedFileRepository::class);
        $testData = $this->createStatusTestData($repository);

        // 验证 Uploaded 状态
        $uploadedResults = $repository->findByStatus(FileStatus::Uploaded);
        $this->assertNotEmpty($uploadedResults);
        $this->assertAllInstancesMatch($uploadedResults, UploadedFile::class);
        $this->assertAllStatusMatch($uploadedResults, FileStatus::Uploaded);
        $this->assertContainsFile($uploadedResults, $testData['uploaded'], 'Uploaded file should be found');

        // 验证 Processed 状态
        $processedResults = $repository->findByStatus(FileStatus::Processed);
        $this->assertNotEmpty($processedResults);
        $this->assertAllInstancesMatch($processedResults, UploadedFile::class);
        $this->assertAllStatusMatch($processedResults, FileStatus::Processed);
        $this->assertContainsFile($processedResults, $testData['processed'], 'Processed file should be found');
    }

    public function testFindByPurpose(): void
    {
        $repository = self::getService(UploadedFileRepository::class);
        $testData = $this->createPurposeTestData($repository);

        // 验证 assistants 用途
        $assistantsResults = $repository->findByPurpose('assistants');
        $this->assertNotEmpty($assistantsResults);
        $this->assertAllInstancesMatch($assistantsResults, UploadedFile::class);
        $this->assertAllPurposeMatch($assistantsResults, 'assistants');
        $this->assertContainsFile($assistantsResults, $testData['assistants'], 'Assistants file should be found');

        // 验证 fine-tune 用途
        $fineTuneResults = $repository->findByPurpose('fine-tune');
        $this->assertNotEmpty($fineTuneResults);
        $this->assertAllInstancesMatch($fineTuneResults, UploadedFile::class);
        $this->assertAllPurposeMatch($fineTuneResults, 'fine-tune');
        $this->assertContainsFile($fineTuneResults, $testData['fineTune'], 'Fine-tune file should be found');
    }

    public function testFindByFileId(): void
    {
        $repository = self::getService(UploadedFileRepository::class);

        $file = $this->createTestFile();
        $file->setFileId('unique-file-id');
        $file->setFilename('unique-file.json');

        $repository->save($file);

        $foundFile = $repository->findByFileId('unique-file-id');
        $this->assertInstanceOf(UploadedFile::class, $foundFile);
        $this->assertSame('unique-file-id', $foundFile->getFileId());
        $this->assertSame($file->getId(), $foundFile->getId());

        $notFoundFile = $repository->findByFileId('non-existent-file');
        $this->assertNull($notFoundFile);
    }

    public function testFindByMimeType(): void
    {
        $repository = self::getService(UploadedFileRepository::class);
        $testData = $this->createMimeTypeTestData($repository);

        // 验证 PDF 类型
        $pdfResults = $repository->findByMimeType('application/pdf');
        $this->assertNotEmpty($pdfResults);
        $this->assertAllInstancesMatch($pdfResults, UploadedFile::class);
        $this->assertAllMimeTypeMatch($pdfResults, 'application/pdf');
        $this->assertContainsFile($pdfResults, $testData['pdf'], 'PDF file should be found');

        // 验证 text/plain 类型
        $textResults = $repository->findByMimeType('text/plain');
        $this->assertNotEmpty($textResults);
        $this->assertAllInstancesMatch($textResults, UploadedFile::class);
        $this->assertAllMimeTypeMatch($textResults, 'text/plain');
        $this->assertContainsFile($textResults, $testData['text'], 'Text file should be found');
    }

    public function testFindExpiring(): void
    {
        $repository = self::getService(UploadedFileRepository::class);
        $testData = $this->createExpiringTestData($repository);

        $beforeDate = new \DateTimeImmutable('+2 days');
        $results = $repository->findExpiring($beforeDate);

        // 简化验证
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
        $this->assertAllInstancesMatch($results, UploadedFile::class);
        $this->assertAllExpiresAtValid($results, $beforeDate);
        $this->assertContainsFile($results, $testData['expiring'], 'Expiring file should be found');
    }

    public function testFindBySizeRange(): void
    {
        $repository = self::getService(UploadedFileRepository::class);

        $smallFile = $this->createTestFile();
        $smallFile->setFileId('small-file');
        $smallFile->setBytes(1024);
        $mediumFile = $this->createTestFile();
        $mediumFile->setFileId('medium-file');
        $mediumFile->setBytes(1048576);
        $largeFile = $this->createTestFile();
        $largeFile->setFileId('large-file');
        $largeFile->setBytes(10485760);

        $repository->save($smallFile);
        $repository->save($mediumFile);
        $repository->save($largeFile);

        // 验证最小尺寸范围
        $largeResults = $repository->findBySizeRange(500000, null);
        $this->assertNotEmpty($largeResults);
        $this->assertSizeRangeResults($largeResults, 500000, null);

        // 验证最大尺寸范围
        $smallResults = $repository->findBySizeRange(null, 2000000);
        $this->assertNotEmpty($smallResults);
        $this->assertSizeRangeResults($smallResults, null, 2000000);

        // 验证尺寸区间范围
        $mediumResults = $repository->findBySizeRange(500000, 2000000);
        $this->assertIsArray($mediumResults);
        $this->assertSizeRangeResults($mediumResults, 500000, 2000000);
    }

    public function testFindByDateRange(): void
    {
        $repository = self::getService(UploadedFileRepository::class);

        $startDate = new \DateTimeImmutable('2024-01-01');
        $endDate = new \DateTimeImmutable('2024-01-31');

        $file1 = $this->createTestFile();
        $file1->setFileId('date-test-1');
        $file2 = $this->createTestFile();
        $file2->setFileId('date-test-2');

        $repository->save($file1);
        $repository->save($file2);

        $results = $repository->findByDateRange($startDate, $endDate);

        // 简化验证
        $this->assertIsArray($results);
        $this->assertDateRangeResults($results, $startDate, $endDate);
    }

    public function testSearch(): void
    {
        $repository = self::getService(UploadedFileRepository::class);
        $testData = $this->createSearchTestData($repository);

        $results = $repository->search('important');

        // 简化验证
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
        $this->assertAllInstancesMatch($results, UploadedFile::class);
        $this->assertAllContainsKeyword($results, 'important');

        // 验证预期的文件
        $this->assertContainsFile($results, $testData['file1'], 'File 1 should be found by filename');
        $this->assertContainsFile($results, $testData['file2'], 'File 2 should be found by description');
        $this->assertNotContainsFile($results, $testData['file3'], 'File 3 should not be found');
    }

    public function testCountByStatus(): void
    {
        $repository = self::getService(UploadedFileRepository::class);

        $uploadedFile = $this->createTestFile();
        $uploadedFile->setStatus(FileStatus::Uploaded);
        $processedFile = $this->createTestFile();
        $processedFile->setFileId('processed-count');
        $processedFile->setStatus(FileStatus::Processed);

        $repository->save($uploadedFile);
        $repository->save($processedFile);

        $uploadedCount = $repository->countByStatus(FileStatus::Uploaded);
        $this->assertIsInt($uploadedCount);
        $this->assertGreaterThanOrEqual(1, $uploadedCount);

        $processedCount = $repository->countByStatus(FileStatus::Processed);
        $this->assertIsInt($processedCount);
        $this->assertGreaterThanOrEqual(1, $processedCount);

        $errorCount = $repository->countByStatus(FileStatus::Error);
        $this->assertIsInt($errorCount);
        $this->assertGreaterThanOrEqual(0, $errorCount);
    }

    public function testGetFileStatistics(): void
    {
        $repository = self::getService(UploadedFileRepository::class);

        $file1 = $this->createTestFile();
        $file1->setFileId('stats-file-1');
        $file1->setPurpose('assistants');
        $file1->setStatus(FileStatus::Uploaded);
        $file1->setMimeType('application/pdf');
        $file1->setBytes(1024);
        $file2 = $this->createTestFile();
        $file2->setFileId('stats-file-2');
        $file2->setPurpose('fine-tune');
        $file2->setStatus(FileStatus::Processed);
        $file2->setMimeType('text/plain');
        $file2->setBytes(2048);

        $repository->save($file1);
        $repository->save($file2);

        $stats = $repository->getFileStatistics();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total', $stats);
        $this->assertArrayHasKey('total_size', $stats);
        $this->assertArrayHasKey('by_status', $stats);
        $this->assertArrayHasKey('by_purpose', $stats);
        $this->assertArrayHasKey('by_mime_type', $stats);

        $this->assertIsInt($stats['total']);
        $this->assertIsInt($stats['total_size']);
        $this->assertIsArray($stats['by_status']);
        $this->assertIsArray($stats['by_purpose']);
        $this->assertIsArray($stats['by_mime_type']);

        $this->assertGreaterThanOrEqual(2, $stats['total']);
        $this->assertGreaterThanOrEqual(3072, $stats['total_size']);
    }

    public function testFindRecentUploads(): void
    {
        $repository = self::getService(UploadedFileRepository::class);

        $file1 = $this->createTestFile();
        $file1->setFileId('recent-1');
        $repository->save($file1);

        usleep(10000); // 增加睡眠时间，确保时间差异

        $file2 = $this->createTestFile();
        $file2->setFileId('recent-2');
        $repository->save($file2);

        $results = $repository->findRecentUploads(10);
        $this->assertIsArray($results);
        $this->assertLessThanOrEqual(10, count($results));

        foreach ($results as $result) {
            $this->assertInstanceOf(UploadedFile::class, $result);
        }

        $this->assertResultsOrderedByCreatedAtDesc($results);
    }

    protected function onSetUp(): void
    {
    }

    /**
     * @return ServiceEntityRepository<UploadedFile>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return self::getService(UploadedFileRepository::class);
    }

    protected function createNewEntity(): object
    {
        return $this->createTestFile();
    }

    public function testFindLargeFiles(): void
    {
        $repository = self::getService(UploadedFileRepository::class);
        [$smallFile, $mediumFile, $largeFile] = $this->createTestFilesForSizeTest($repository);

        // 验证默认阈值（10MB）
        $defaultResults = $repository->findLargeFiles();
        $this->assertIsArray($defaultResults);
        $this->assertAllBytesGreaterThan($defaultResults, 10485760);
        $this->assertContainsFile($defaultResults, $largeFile, 'Large file should be found');
        $this->assertNotContainsFile($defaultResults, $mediumFile, 'Medium file should not be found');
        $this->assertNotContainsFile($defaultResults, $smallFile, 'Small file should not be found');

        // 验证自定义阈值（3MB）
        $customResults = $repository->findLargeFiles(3145728);
        $this->assertIsArray($customResults);
        $this->assertAllBytesGreaterThan($customResults, 3145728);
        $this->assertContainsFile($customResults, $largeFile, 'Large file should be found');
        $this->assertContainsFile($customResults, $mediumFile, 'Medium file should be found');
        $this->assertNotContainsFile($customResults, $smallFile, 'Small file should not be found');
        $this->assertResultsOrderedByBytesDesc($customResults);
    }

    /**
     * @return UploadedFile[]
     */
    private function createTestFilesForSizeTest(UploadedFileRepository $repository): array
    {
        $smallFile = $this->createTestFile();
        $smallFile->setFileId('small-file-test');
        $smallFile->setBytes(1024); // 1KB

        $mediumFile = $this->createTestFile();
        $mediumFile->setFileId('medium-file-test');
        $mediumFile->setBytes(5242880); // 5MB

        $largeFile = $this->createTestFile();
        $largeFile->setFileId('large-file-test');
        $largeFile->setBytes(15728640); // 15MB

        $repository->save($smallFile);
        $repository->save($mediumFile);
        $repository->save($largeFile);

        return [$smallFile, $mediumFile, $largeFile];
    }

    /**
     * @param UploadedFile[] $results
     */
    private function assertResultsOrderedByBytesDesc(array $results): void
    {
        if (count($results) >= 2) {
            for ($i = 0; $i < count($results) - 1; ++$i) {
                $this->assertGreaterThanOrEqual(
                    $results[$i + 1]->getBytes(),
                    $results[$i]->getBytes(),
                    'Results should be ordered by bytes DESC'
                );
            }
        }
    }

    public function testFindWithMetadata(): void
    {
        $repository = self::getService(UploadedFileRepository::class);

        $fileWithMetadata = $this->createTestFile();
        $fileWithMetadata->setFileId('file-with-metadata');
        $fileWithMetadata->setMetadata(['category' => 'documents', 'priority' => 'high', 'version' => '1.0']);
        $fileWithEmptyMetadata = $this->createTestFile();
        $fileWithEmptyMetadata->setFileId('file-empty-metadata');
        $fileWithEmptyMetadata->setMetadata([]);
        $fileWithMinimalMetadata = $this->createTestFile();
        $fileWithMinimalMetadata->setFileId('file-minimal-metadata');
        $fileWithMinimalMetadata->setMetadata(['test' => true]);

        $repository->save($fileWithMetadata);
        $repository->save($fileWithEmptyMetadata);
        $repository->save($fileWithMinimalMetadata);

        try {
            $results = $repository->findWithMetadata();
        } catch (InvalidFieldNameException $e) {
            if (str_contains($e->getMessage(), 'JSON_LENGTH')) {
                self::markTestSkipped('Database does not support JSON_LENGTH function: ' . $e->getMessage());
            }
            throw $e;
        }

        // 简化验证
        $this->assertAllInstancesMatch($results, UploadedFile::class);
        $this->assertAllMetadataNotEmpty($results);
        $this->assertContainsFile($results, $fileWithMetadata, 'File with rich metadata should be found');
        $this->assertNotContainsFile($results, $fileWithEmptyMetadata, 'File with empty metadata should not be found');
        $this->assertContainsFile($results, $fileWithMinimalMetadata, 'File with minimal metadata should be found');
        $this->assertResultsOrderedByCreatedAtDesc($results);
    }

    private function createTestFile(): UploadedFile
    {
        $file = new UploadedFile();
        $file->setFileId('test_file_' . uniqid());
        $file->setFilename('test_file_' . uniqid() . '.txt');
        $file->setPurpose('assistants');
        $file->setBytes(1024);
        $file->setStatus(FileStatus::Uploaded);
        $file->setMimeType('text/plain');
        $file->setDescription('Test file');
        $file->setMetadata(['test' => true]);

        return $file;
    }

    /**
     * @return array{uploaded: UploadedFile, processed: UploadedFile}
     */
    private function createStatusTestData(UploadedFileRepository $repository): array
    {
        $uploadedFile = $this->createTestFile();
        $uploadedFile->setFilename('uploaded.pdf');
        $uploadedFile->setStatus(FileStatus::Uploaded);

        $processedFile = $this->createTestFile();
        $processedFile->setFileId('file-processed');
        $processedFile->setFilename('processed.txt');
        $processedFile->setStatus(FileStatus::Processed);

        $repository->save($uploadedFile);
        $repository->save($processedFile);

        return ['uploaded' => $uploadedFile, 'processed' => $processedFile];
    }

    /**
     * @return array{assistants: UploadedFile, fineTune: UploadedFile}
     */
    private function createPurposeTestData(UploadedFileRepository $repository): array
    {
        $assistantsFile = $this->createTestFile();
        $assistantsFile->setFileId('file-assistants');
        $assistantsFile->setPurpose('assistants');

        $fineTuneFile = $this->createTestFile();
        $fineTuneFile->setFileId('file-fine-tune');
        $fineTuneFile->setPurpose('fine-tune');

        $repository->save($assistantsFile);
        $repository->save($fineTuneFile);

        return ['assistants' => $assistantsFile, 'fineTune' => $fineTuneFile];
    }

    /**
     * @return array{pdf: UploadedFile, text: UploadedFile}
     */
    private function createMimeTypeTestData(UploadedFileRepository $repository): array
    {
        $pdfFile = $this->createTestFile();
        $pdfFile->setFileId('pdf-file');
        $pdfFile->setMimeType('application/pdf');

        $textFile = $this->createTestFile();
        $textFile->setFileId('text-file');
        $textFile->setMimeType('text/plain');

        $repository->save($pdfFile);
        $repository->save($textFile);

        return ['pdf' => $pdfFile, 'text' => $textFile];
    }

    /**
     * @return array{expiring: UploadedFile, notExpiring: UploadedFile, deleted: UploadedFile}
     */
    private function createExpiringTestData(UploadedFileRepository $repository): array
    {
        $expiringFile = $this->createTestFile();
        $expiringFile->setFileId('expiring-file');
        $expiringFile->setExpiresAt(new \DateTimeImmutable('+1 day'));
        $expiringFile->setStatus(FileStatus::Uploaded);

        $notExpiringFile = $this->createTestFile();
        $notExpiringFile->setFileId('not-expiring-file');
        $notExpiringFile->setExpiresAt(new \DateTimeImmutable('+30 days'));
        $notExpiringFile->setStatus(FileStatus::Uploaded);

        $deletedFile = $this->createTestFile();
        $deletedFile->setFileId('deleted-expiring-file');
        $deletedFile->setExpiresAt(new \DateTimeImmutable('+1 day'));
        $deletedFile->setStatus(FileStatus::Deleted);

        $repository->save($expiringFile);
        $repository->save($notExpiringFile);
        $repository->save($deletedFile);

        return [
            'expiring' => $expiringFile,
            'notExpiring' => $notExpiringFile,
            'deleted' => $deletedFile,
        ];
    }

    /**
     * @param UploadedFile[] $results
     */
    private function assertSizeRangeResults(array $results, ?int $minSize, ?int $maxSize): void
    {
        $this->assertIsArray($results);

        foreach ($results as $result) {
            $this->assertInstanceOf(UploadedFile::class, $result);
            if (null !== $minSize) {
                $this->assertGreaterThanOrEqual($minSize, $result->getBytes());
            }
            if (null !== $maxSize) {
                $this->assertLessThanOrEqual($maxSize, $result->getBytes());
            }
        }
    }

    /**
     * @param UploadedFile[] $results
     */
    private function assertDateRangeResults(array $results, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate): void
    {
        $this->assertIsArray($results);

        foreach ($results as $result) {
            $this->assertInstanceOf(UploadedFile::class, $result);
            $this->assertGreaterThanOrEqual($startDate, $result->getCreatedAt());
            $this->assertLessThanOrEqual($endDate, $result->getCreatedAt());
        }
    }

    /**
     * @return array{file1: UploadedFile, file2: UploadedFile, file3: UploadedFile}
     */
    private function createSearchTestData(UploadedFileRepository $repository): array
    {
        $file1 = $this->createTestFile();
        $file1->setFileId('search-file-1');
        $file1->setFilename('important-document.pdf');
        $file1->setDescription('Contract details');

        $file2 = $this->createTestFile();
        $file2->setFileId('search-file-2');
        $file2->setFilename('report.txt');
        $file2->setDescription('Important analysis results');

        $file3 = $this->createTestFile();
        $file3->setFileId('search-file-3');
        $file3->setFilename('data.json');
        $file3->setDescription('User preferences');

        $repository->save($file1);
        $repository->save($file2);
        $repository->save($file3);

        return ['file1' => $file1, 'file2' => $file2, 'file3' => $file3];
    }

    /**
     * @param UploadedFile[] $results
     */
    private function assertResultsOrderedByCreatedAtDesc(array $results): void
    {
        if (count($results) >= 2) {
            for ($i = 0; $i < count($results) - 1; ++$i) {
                $currentTime = $results[$i]->getCreatedAt();
                $nextTime = $results[$i + 1]->getCreatedAt();

                // 使用时间戳比较以处理精度问题
                $this->assertGreaterThanOrEqual(
                    $nextTime->getTimestamp(),
                    $currentTime->getTimestamp(),
                    'Results should be ordered by createdAt DESC'
                );
            }
        }
    }

    // ========== 通用断言助手方法 ==========

    /**
     * @param UploadedFile[] $results
     * @param class-string $expectedClass
     */
    private function assertAllInstancesMatch(array $results, string $expectedClass): void
    {
        foreach ($results as $result) {
            $this->assertInstanceOf($expectedClass, $result);
        }
    }

    /**
     * @param UploadedFile[] $results
     */
    private function assertAllStatusMatch(array $results, FileStatus $expectedStatus): void
    {
        foreach ($results as $result) {
            $this->assertSame($expectedStatus, $result->getStatus());
        }
    }

    /**
     * @param UploadedFile[] $results
     */
    private function assertAllPurposeMatch(array $results, string $expectedPurpose): void
    {
        foreach ($results as $result) {
            $this->assertSame($expectedPurpose, $result->getPurpose());
        }
    }

    /**
     * @param UploadedFile[] $results
     */
    private function assertAllMimeTypeMatch(array $results, string $expectedMimeType): void
    {
        foreach ($results as $result) {
            $this->assertSame($expectedMimeType, $result->getMimeType());
        }
    }

    /**
     * @param UploadedFile[] $results
     */
    private function assertAllExpiresAtValid(array $results, \DateTimeImmutable $beforeDate): void
    {
        foreach ($results as $result) {
            $this->assertNotNull($result->getExpiresAt());
            $this->assertLessThanOrEqual($beforeDate, $result->getExpiresAt());
            $this->assertNotSame(FileStatus::Deleted, $result->getStatus());
        }
    }

    /**
     * @param UploadedFile[] $results
     */
    private function assertAllBytesGreaterThan(array $results, int $minBytes): void
    {
        $this->assertIsArray($results);
        foreach ($results as $result) {
            $this->assertInstanceOf(UploadedFile::class, $result);
            $this->assertGreaterThanOrEqual($minBytes, $result->getBytes());
        }
    }

    /**
     * @param UploadedFile[] $results
     */
    private function assertAllMetadataNotEmpty(array $results): void
    {
        foreach ($results as $result) {
            $metadata = $result->getMetadata();
            $this->assertNotEmpty($metadata, 'File should have non-empty metadata');
        }
    }

    /**
     * @param UploadedFile[] $results
     */
    private function assertAllContainsKeyword(array $results, string $keyword): void
    {
        $lowerKeyword = strtolower($keyword);
        foreach ($results as $result) {
            $containsInFilename = str_contains(
                strtolower($result->getFilename()),
                $lowerKeyword
            );
            $containsInDescription = null !== $result->getDescription()
            && str_contains(
                strtolower($result->getDescription()),
                $lowerKeyword
            );
            $this->assertTrue(
                $containsInFilename || $containsInDescription,
                "Search result should contain \"{$keyword}\" in filename or description"
            );
        }
    }

    /**
     * @param UploadedFile[] $results
     */
    private function assertContainsFile(array $results, UploadedFile $targetFile, string $message): void
    {
        $found = false;
        foreach ($results as $result) {
            if ($result->getId() === $targetFile->getId()) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, $message);
    }

    /**
     * @param UploadedFile[] $results
     */
    private function assertNotContainsFile(array $results, UploadedFile $targetFile, string $message): void
    {
        $found = false;
        foreach ($results as $result) {
            if ($result->getId() === $targetFile->getId()) {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found, $message);
    }
}
