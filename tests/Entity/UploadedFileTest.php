<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\OpenAiApiBundle\Entity\UploadedFile;
use Tourze\OpenAiApiBundle\Enum\FileStatus;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(UploadedFile::class)]
final class UploadedFileTest extends AbstractEntityTestCase
{
    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'fileId' => ['fileId', 'file-abc123'];
        yield 'filename' => ['filename', 'document.pdf'];
        yield 'purpose' => ['purpose', 'assistants'];
        yield 'bytes' => ['bytes', 1048576];
        yield 'status' => ['status', FileStatus::Uploaded];
        yield 'mimeType' => ['mimeType', 'application/pdf'];
        yield 'expiresAt' => ['expiresAt', new \DateTimeImmutable('+1 day')];
        yield 'description' => ['description', 'Test document'];
        yield 'metadata' => ['metadata', ['key' => 'value']];
    }

    public function testEntityCreation(): void
    {
        $entity = new UploadedFile();
        $this->assertInstanceOf(UploadedFile::class, $entity);
        $this->assertNull($entity->getId());
        $this->assertSame(FileStatus::Uploaded, $entity->getStatus());
        $this->assertInstanceOf(\DateTimeImmutable::class, $entity->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $entity->getUpdatedAt());
        $this->assertSame([], $entity->getMetadata());
        $this->assertNull($entity->getExpiresAt());
        $this->assertNull($entity->getMimeType());
        $this->assertNull($entity->getDescription());
    }

    public function testConstructorSetsDateTimes(): void
    {
        $entity = new UploadedFile();

        $this->assertInstanceOf(\DateTimeImmutable::class, $entity->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $entity->getUpdatedAt());

        // 验证创建时间和更新时间相等（在构造函数中设置）
        $this->assertSame(
            $entity->getCreatedAt()->format('Y-m-d H:i:s'),
            $entity->getUpdatedAt()->format('Y-m-d H:i:s')
        );
    }

    public function testFileIdProperty(): void
    {
        $entity = new UploadedFile();
        $entity->setFileId('file-xyz789');
        $this->assertSame('file-xyz789', $entity->getFileId());
    }

    public function testFilenameProperty(): void
    {
        $entity = new UploadedFile();
        $entity->setFilename('test.txt');
        $this->assertSame('test.txt', $entity->getFilename());
    }

    public function testPurposeProperty(): void
    {
        $entity = new UploadedFile();
        $entity->setPurpose('fine-tune');
        $this->assertSame('fine-tune', $entity->getPurpose());
    }

    public function testBytesProperty(): void
    {
        $entity = new UploadedFile();
        $entity->setBytes(2048);
        $this->assertSame(2048, $entity->getBytes());
    }

    public function testStatusProperty(): void
    {
        $entity = new UploadedFile();

        // 默认状态是 Uploaded
        $this->assertSame(FileStatus::Uploaded, $entity->getStatus());

        $entity->setStatus(FileStatus::Processed);
        $this->assertSame(FileStatus::Processed, $entity->getStatus());
    }

    public function testSetStatusUpdatesTimestamp(): void
    {
        $entity = new UploadedFile();
        $originalUpdatedAt = $entity->getUpdatedAt();

        // 等待一毫秒确保时间戳不同
        usleep(1000);

        $entity->setStatus(FileStatus::Error);
        $this->assertGreaterThan($originalUpdatedAt, $entity->getUpdatedAt());
    }

    public function testMimeTypeProperty(): void
    {
        $entity = new UploadedFile();

        // 默认为 null
        $this->assertNull($entity->getMimeType());

        $entity->setMimeType('text/plain');
        $this->assertSame('text/plain', $entity->getMimeType());

        $entity->setMimeType(null);
        $this->assertNull($entity->getMimeType());
    }

    public function testExpiresAtProperty(): void
    {
        $entity = new UploadedFile();

        // 默认为 null
        $this->assertNull($entity->getExpiresAt());

        $expiresAt = new \DateTimeImmutable('+7 days');
        $entity->setExpiresAt($expiresAt);
        $this->assertSame($expiresAt, $entity->getExpiresAt());

        $entity->setExpiresAt(null);
        $this->assertNull($entity->getExpiresAt());
    }

    public function testDescriptionProperty(): void
    {
        $entity = new UploadedFile();

        // 默认为 null
        $this->assertNull($entity->getDescription());

        $entity->setDescription('Important document');
        $this->assertSame('Important document', $entity->getDescription());

        $entity->setDescription(null);
        $this->assertNull($entity->getDescription());
    }

    public function testMetadataProperty(): void
    {
        $entity = new UploadedFile();

        // 默认为空数组
        $this->assertSame([], $entity->getMetadata());

        $metadata = ['author' => 'John Doe', 'category' => 'technical'];
        $entity->setMetadata($metadata);
        $this->assertSame($metadata, $entity->getMetadata());
    }

    public function testGetHumanReadableSizeBytes(): void
    {
        $entity = new UploadedFile();
        $entity->setBytes(512);

        $this->assertSame('512 B', $entity->getHumanReadableSize());
    }

    public function testGetHumanReadableSizeKilobytes(): void
    {
        $entity = new UploadedFile();
        $entity->setBytes(1536); // 1.5 KB

        $this->assertSame('1.5 KB', $entity->getHumanReadableSize());
    }

    public function testGetHumanReadableSizeMegabytes(): void
    {
        $entity = new UploadedFile();
        $entity->setBytes(2097152); // 2 MB

        $this->assertSame('2 MB', $entity->getHumanReadableSize());
    }

    public function testGetHumanReadableSizeGigabytes(): void
    {
        $entity = new UploadedFile();
        $entity->setBytes(3221225472); // 3 GB

        $this->assertSame('3 GB', $entity->getHumanReadableSize());
    }

    public function testGetHumanReadableSizeRounding(): void
    {
        $entity = new UploadedFile();
        $entity->setBytes(1572864); // 1.5 MB

        $this->assertSame('1.5 MB', $entity->getHumanReadableSize());
    }

    public function testGetHumanReadableSizeZeroBytes(): void
    {
        $entity = new UploadedFile();
        $entity->setBytes(0);

        $this->assertSame('0 B', $entity->getHumanReadableSize());
    }

    public function testStringRepresentation(): void
    {
        $entity = new UploadedFile();
        $entity->setFilename('my-document.pdf');

        $this->assertSame('my-document.pdf', (string) $entity);
    }

    public function testMethodChaining(): void
    {
        $entity = new UploadedFile();
        $expiresAt = new \DateTimeImmutable('+30 days');

        $entity->setFileId('file-123456');
        $entity->setFilename('test-file.json');
        $entity->setPurpose('assistants');
        $entity->setBytes(4096);
        $entity->setStatus(FileStatus::Processed);
        $entity->setMimeType('application/json');
        $entity->setExpiresAt($expiresAt);
        $entity->setDescription('Test JSON file');
        $entity->setMetadata(['type' => 'test', 'version' => '1.0']);

        // 验证所有属性都已正确设置
        $this->assertSame('file-123456', $entity->getFileId());
        $this->assertSame('test-file.json', $entity->getFilename());
        $this->assertSame('assistants', $entity->getPurpose());
        $this->assertSame(4096, $entity->getBytes());
        $this->assertSame(FileStatus::Processed, $entity->getStatus());
        $this->assertSame('application/json', $entity->getMimeType());
        $this->assertSame($expiresAt, $entity->getExpiresAt());
        $this->assertSame('Test JSON file', $entity->getDescription());
        $this->assertSame(['type' => 'test', 'version' => '1.0'], $entity->getMetadata());
    }

    protected function createEntity(): UploadedFile
    {
        return new UploadedFile();
    }
}
