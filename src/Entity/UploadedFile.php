<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\OpenAiApiBundle\Enum\FileStatus;
use Tourze\OpenAiApiBundle\Repository\UploadedFileRepository;

#[ORM\Entity(repositoryClass: UploadedFileRepository::class)]
#[ORM\Table(name: 'openai_uploaded_files', options: ['comment' => 'OpenAI 上传文件表'])]
class UploadedFile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键 ID'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 100, unique: true, options: ['comment' => 'OpenAI 文件唯一标识符'])]
    #[Assert\NotBlank(message: '文件ID不能为空')]
    #[Assert\Length(max: 100, maxMessage: '文件ID长度不能超过100个字符')]
    private string $fileId;

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '文件名'])]
    #[Assert\NotBlank(message: '文件名不能为空')]
    #[Assert\Length(max: 255, maxMessage: '文件名长度不能超过255个字符')]
    private string $filename;

    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '文件用途'])]
    #[Assert\NotBlank(message: '文件用途不能为空')]
    #[Assert\Length(max: 100, maxMessage: '文件用途长度不能超过100个字符')]
    private string $purpose;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '文件大小（字节）'])]
    #[Assert\NotBlank(message: '文件大小不能为空')]
    #[Assert\Range(notInRangeMessage: '文件大小必须在1字节到10GB之间', min: 1, max: 10737418240)]
    private int $bytes;

    #[ORM\Column(type: Types::STRING, length: 50, enumType: FileStatus::class, options: ['comment' => '文件状态'])]
    #[Assert\NotNull(message: '文件状态不能为空')]
    #[Assert\Choice(callback: [FileStatus::class, 'cases'], message: '请选择有效的文件状态')]
    private FileStatus $status = FileStatus::Uploaded;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '文件MIME类型'])]
    #[Assert\Length(max: 100, maxMessage: 'MIME类型长度不能超过100个字符')]
    private ?string $mimeType = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '创建时间'])]
    #[Assert\NotNull(message: '创建时间不能为空')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '更新时间'])]
    #[Assert\NotNull(message: '更新时间不能为空')]
    private \DateTimeImmutable $updatedAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '过期时间'])]
    #[Assert\GreaterThan(propertyPath: 'createdAt', message: '过期时间必须晚于创建时间')]
    private ?\DateTimeImmutable $expiresAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '文件描述'])]
    #[Assert\Length(max: 2000, maxMessage: '文件描述长度不能超过2000个字符')]
    private ?string $description = null;

    /** @var array<string, mixed> */
    #[ORM\Column(type: Types::JSON, options: ['comment' => '元数据'])]
    #[Assert\Type(type: 'array', message: '元数据必须为数组')]
    private array $metadata = [];

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFileId(): string
    {
        return $this->fileId;
    }

    public function setFileId(string $fileId): void
    {
        $this->fileId = $fileId;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): void
    {
        $this->filename = $filename;
    }

    public function getPurpose(): string
    {
        return $this->purpose;
    }

    public function setPurpose(string $purpose): void
    {
        $this->purpose = $purpose;
    }

    public function getBytes(): int
    {
        return $this->bytes;
    }

    public function setBytes(int $bytes): void
    {
        $this->bytes = $bytes;
    }

    public function getStatus(): FileStatus
    {
        return $this->status;
    }

    public function setStatus(FileStatus $status): void
    {
        $this->status = $status;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType): void
    {
        $this->mimeType = $mimeType;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?\DateTimeImmutable $expiresAt): void
    {
        $this->expiresAt = $expiresAt;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public function setMetadata(array $metadata): void
    {
        $this->metadata = $metadata;
    }

    public function getHumanReadableSize(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = $this->bytes;
        $i = 0;

        for (; $bytes > 1024 && $i < count($units) - 1; ++$i) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function __toString(): string
    {
        return $this->filename;
    }
}
