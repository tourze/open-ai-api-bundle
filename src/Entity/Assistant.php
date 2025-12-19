<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\OpenAiApiBundle\Enum\AssistantStatus;
use Tourze\OpenAiApiBundle\Repository\AssistantRepository;

#[ORM\Entity(repositoryClass: AssistantRepository::class)]
#[ORM\Table(name: 'openai_assistants', options: ['comment' => 'OpenAI 助手表'])]
class Assistant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键 ID'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 100, unique: true, options: ['comment' => 'OpenAI 助手唯一标识符'])]
    #[Assert\NotBlank(message: '助手ID不能为空')]
    #[Assert\Length(max: 100, maxMessage: '助手ID长度不能超过100个字符')]
    private string $assistantId;

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '助手名称'])]
    #[Assert\NotBlank(message: '助手名称不能为空')]
    #[Assert\Length(max: 255, maxMessage: '助手名称长度不能超过255个字符')]
    private string $name;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '助手描述'])]
    #[Assert\Length(max: 2000, maxMessage: '助手描述长度不能超过2000个字符')]
    private ?string $description = null;

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '使用的模型名称'])]
    #[Assert\NotBlank(message: '模型名称不能为空')]
    #[Assert\Length(max: 50, maxMessage: '模型名称长度不能超过50个字符')]
    private string $model;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '助手指令'])]
    #[Assert\Length(max: 10000, maxMessage: '助手指令长度不能超过10000个字符')]
    private ?string $instructions = null;

    /** @var array<mixed> */
    #[ORM\Column(type: Types::JSON, options: ['comment' => '工具配置'])]
    #[Assert\Type(type: 'array', message: '工具配置必须为数组')]
    private array $tools = [];

    /** @var array<string, mixed> */
    #[ORM\Column(type: Types::JSON, options: ['comment' => '元数据'])]
    #[Assert\Type(type: 'array', message: '元数据必须为数组')]
    private array $metadata = [];

    #[ORM\Column(type: Types::STRING, length: 20, enumType: AssistantStatus::class, options: ['comment' => '助手状态'])]
    #[Assert\NotNull(message: '助手状态不能为空')]
    #[Assert\Choice(callback: [AssistantStatus::class, 'cases'], message: '请选择有效的助手状态')]
    private AssistantStatus $status = AssistantStatus::Active;

    #[ORM\Column(type: Types::DECIMAL, precision: 3, scale: 2, nullable: true, options: ['comment' => '温度参数'])]
    #[Assert\Range(notInRangeMessage: '温度参数必须在0到2之间', min: 0, max: 2)]
    private ?string $temperature = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 3, scale: 2, nullable: true, options: ['comment' => 'Top P参数'])]
    #[Assert\Range(notInRangeMessage: 'Top P参数必须在0到1之间', min: 0, max: 1)]
    private ?string $topP = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '创建时间'])]
    #[Assert\NotNull(message: '创建时间不能为空')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '更新时间'])]
    #[Assert\NotNull(message: '更新时间不能为空')]
    private \DateTimeImmutable $updatedAt;

    /** @var array<string> */
    #[ORM\Column(type: Types::JSON, options: ['comment' => '文件ID列表'])]
    #[Assert\Type(type: 'array', message: '文件ID列表必须为数组')]
    private array $fileIds = [];

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '响应格式'])]
    #[Assert\Length(max: 1000, maxMessage: '响应格式长度不能超过1000个字符')]
    private ?string $responseFormat = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAssistantId(): string
    {
        return $this->assistantId;
    }

    public function setAssistantId(string $assistantId): void
    {
        $this->assistantId = $assistantId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function setModel(string $model): void
    {
        $this->model = $model;
    }

    public function getInstructions(): ?string
    {
        return $this->instructions;
    }

    public function setInstructions(?string $instructions): void
    {
        $this->instructions = $instructions;
    }

    /**
     * @return array<mixed>
     */
    public function getTools(): array
    {
        return $this->tools;
    }

    /**
     * @param array<mixed> $tools
     */
    public function setTools(array $tools): void
    {
        $this->tools = $tools;
        $this->updatedAt = new \DateTimeImmutable();
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

    public function getStatus(): AssistantStatus
    {
        return $this->status;
    }

    public function setStatus(AssistantStatus $status): void
    {
        $this->status = $status;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getTemperature(): ?string
    {
        return $this->temperature;
    }

    public function setTemperature(?string $temperature): void
    {
        $this->temperature = $temperature;
    }

    public function getTopP(): ?string
    {
        return $this->topP;
    }

    public function setTopP(?string $topP): void
    {
        $this->topP = $topP;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @return array<string>
     */
    public function getFileIds(): array
    {
        return $this->fileIds;
    }

    /**
     * @param array<string> $fileIds
     */
    public function setFileIds(array $fileIds): void
    {
        $this->fileIds = $fileIds;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getResponseFormat(): ?string
    {
        return $this->responseFormat;
    }

    public function setResponseFormat(?string $responseFormat): void
    {
        $this->responseFormat = $responseFormat;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
