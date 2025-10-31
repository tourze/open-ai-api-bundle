<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\OpenAiApiBundle\Enum\ModelStatus;
use Tourze\OpenAiApiBundle\Repository\AIModelRepository;

#[ORM\Entity(repositoryClass: AIModelRepository::class)]
#[ORM\Table(name: 'openai_ai_models', options: ['comment' => 'OpenAI 模型配置表'])]
class AIModel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键 ID'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 100, unique: true, options: ['comment' => 'OpenAI 模型唯一标识符'])]
    #[Assert\NotBlank(message: '模型ID不能为空')]
    #[Assert\Length(max: 100, maxMessage: '模型ID长度不能超过100个字符')]
    private string $modelId;

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '模型显示名称'])]
    #[Assert\NotBlank(message: '模型名称不能为空')]
    #[Assert\Length(max: 255, maxMessage: '模型名称长度不能超过255个字符')]
    private string $name;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '模型描述'])]
    #[Assert\Length(max: 2000, maxMessage: '模型描述长度不能超过2000个字符')]
    private ?string $description = null;

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '模型所有者'])]
    #[Assert\NotBlank(message: '模型所有者不能为空')]
    #[Assert\Length(max: 50, maxMessage: '模型所有者长度不能超过50个字符')]
    private string $owner;

    #[ORM\Column(type: Types::STRING, length: 20, enumType: ModelStatus::class, options: ['comment' => '模型状态'])]
    #[Assert\NotNull(message: '模型状态不能为空')]
    #[Assert\Choice(callback: [ModelStatus::class, 'cases'], message: '请选择有效的模型状态')]
    private ModelStatus $status = ModelStatus::Available;

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '上下文窗口大小'])]
    #[Assert\Range(min: 1, max: 1000000, minMessage: '上下文窗口大小至少为1', maxMessage: '上下文窗口大小不能超过1000000')]
    private ?int $contextWindow = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 6, nullable: true, options: ['comment' => '输入Token价格'])]
    #[Assert\Range(min: 0, max: 999999.999999, minMessage: '输入Token价格不能为负数', maxMessage: '输入Token价格超出范围')]
    private ?string $inputPricePerToken = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 6, nullable: true, options: ['comment' => '输出Token价格'])]
    #[Assert\Range(min: 0, max: 999999.999999, minMessage: '输出Token价格不能为负数', maxMessage: '输出Token价格超出范围')]
    private ?string $outputPricePerToken = null;

    /** @var array<string> */
    #[ORM\Column(type: Types::JSON, options: ['comment' => '模型能力配置'])]
    #[Assert\Type(type: 'array', message: '模型能力配置必须为数组')]
    private array $capabilities = [];

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '创建时间'])]
    #[Assert\NotNull(message: '创建时间不能为空')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '更新时间'])]
    #[Assert\NotNull(message: '更新时间不能为空')]
    private \DateTimeImmutable $updatedAt;

    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否激活'])]
    #[Assert\NotNull(message: '激活状态不能为空')]
    #[Assert\Type(type: 'bool', message: '激活状态必须为布尔值')]
    private bool $isActive = true;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getModelId(): string
    {
        return $this->modelId;
    }

    public function setModelId(string $modelId): void
    {
        $this->modelId = $modelId;
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

    public function getOwner(): string
    {
        return $this->owner;
    }

    public function setOwner(string $owner): void
    {
        $this->owner = $owner;
    }

    public function getStatus(): ModelStatus
    {
        return $this->status;
    }

    public function setStatus(ModelStatus $status): void
    {
        $this->status = $status;
    }

    public function getContextWindow(): ?int
    {
        return $this->contextWindow;
    }

    public function setContextWindow(?int $contextWindow): void
    {
        $this->contextWindow = $contextWindow;
    }

    public function getInputPricePerToken(): ?string
    {
        return $this->inputPricePerToken;
    }

    public function setInputPricePerToken(?string $inputPricePerToken): void
    {
        $this->inputPricePerToken = $inputPricePerToken;
    }

    public function getOutputPricePerToken(): ?string
    {
        return $this->outputPricePerToken;
    }

    public function setOutputPricePerToken(?string $outputPricePerToken): void
    {
        $this->outputPricePerToken = $outputPricePerToken;
    }

    /**
     * @return array<string>
     */
    public function getCapabilities(): array
    {
        return $this->capabilities;
    }

    /**
     * @param array<string> $capabilities
     */
    public function setCapabilities(array $capabilities): void
    {
        $this->capabilities = $capabilities;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getIsActive(): bool
    {
        return $this->isActive();
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
