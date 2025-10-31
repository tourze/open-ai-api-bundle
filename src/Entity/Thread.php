<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\OpenAiApiBundle\Enum\ThreadStatus;
use Tourze\OpenAiApiBundle\Repository\ThreadRepository;

#[ORM\Entity(repositoryClass: ThreadRepository::class)]
#[ORM\Table(name: 'openai_threads', options: ['comment' => 'OpenAI 线程表'])]
class Thread
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键 ID'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 100, unique: true, options: ['comment' => 'OpenAI 线程唯一标识符'])]
    #[Assert\NotBlank(message: '线程ID不能为空')]
    #[Assert\Length(max: 100, maxMessage: '线程ID长度不能超过100个字符')]
    private string $threadId;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '线程标题'])]
    #[Assert\Length(max: 255, maxMessage: '线程标题长度不能超过255个字符')]
    private ?string $title = null;

    /** @var array<string, mixed> */
    #[ORM\Column(type: Types::JSON, options: ['comment' => '元数据'])]
    #[Assert\Type(type: 'array', message: '元数据必须为数组')]
    private array $metadata = [];

    #[ORM\Column(type: Types::STRING, length: 20, enumType: ThreadStatus::class, options: ['comment' => '线程状态'])]
    #[Assert\NotNull(message: '线程状态不能为空')]
    #[Assert\Choice(callback: [ThreadStatus::class, 'cases'], message: '请选择有效的线程状态')]
    private ThreadStatus $status = ThreadStatus::Active;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '创建时间'])]
    #[Assert\NotNull(message: '创建时间不能为空')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '更新时间'])]
    #[Assert\NotNull(message: '更新时间不能为空')]
    private \DateTimeImmutable $updatedAt;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '消息数量'])]
    #[Assert\Range(min: 0, max: 10000, minMessage: '消息数量不能为负数', maxMessage: '消息数量不能超过10000条')]
    private int $messageCount = 0;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '关联的助手ID'])]
    #[Assert\Length(max: 100, maxMessage: '助手ID长度不能超过100个字符')]
    private ?string $assistantId = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '线程描述'])]
    #[Assert\Length(max: 2000, maxMessage: '线程描述长度不能超过2000个字符')]
    private ?string $description = null;

    /** @var array<string, mixed> */
    #[ORM\Column(type: Types::JSON, options: ['comment' => '工具资源'])]
    #[Assert\Type(type: 'array', message: '工具资源必须为数组')]
    private array $toolResources = [];

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getThreadId(): string
    {
        return $this->threadId;
    }

    public function setThreadId(string $threadId): void
    {
        $this->threadId = $threadId;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
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

    public function getStatus(): ThreadStatus
    {
        return $this->status;
    }

    public function setStatus(ThreadStatus $status): void
    {
        $this->status = $status;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getMessageCount(): int
    {
        return $this->messageCount;
    }

    public function setMessageCount(int $messageCount): void
    {
        $this->messageCount = $messageCount;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getAssistantId(): ?string
    {
        return $this->assistantId;
    }

    public function setAssistantId(?string $assistantId): void
    {
        $this->assistantId = $assistantId;
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
    public function getToolResources(): array
    {
        return $this->toolResources;
    }

    /**
     * @param array<string, mixed> $toolResources
     */
    public function setToolResources(array $toolResources): void
    {
        $this->toolResources = $toolResources;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return null !== $this->title && '' !== $this->title ? $this->title : $this->threadId;
    }
}
