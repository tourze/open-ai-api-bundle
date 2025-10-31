<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\OpenAiApiBundle\Enum\ConversationStatus;
use Tourze\OpenAiApiBundle\Repository\ChatConversationRepository;

#[ORM\Entity(repositoryClass: ChatConversationRepository::class)]
#[ORM\Table(name: 'openai_chat_conversations', options: ['comment' => 'OpenAI 聊天对话表'])]
class ChatConversation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键 ID'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '对话标题'])]
    #[Assert\NotBlank(message: '对话标题不能为空')]
    #[Assert\Length(max: 255, maxMessage: '对话标题长度不能超过255个字符')]
    private string $title;

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '使用的模型'])]
    #[Assert\NotBlank(message: '模型不能为空')]
    #[Assert\Length(max: 50, maxMessage: '模型名称长度不能超过50个字符')]
    private string $model;

    /** @var array<array<string, mixed>> */
    #[ORM\Column(type: Types::JSON, options: ['comment' => '消息列表'])]
    #[Assert\Type(type: 'array', message: '消息列表必须为数组')]
    #[Assert\Count(max: 1000, maxMessage: '消息数量不能超过1000条')]
    private array $messages = [];

    #[ORM\Column(type: Types::STRING, length: 20, enumType: ConversationStatus::class, options: ['comment' => '对话状态'])]
    #[Assert\NotNull(message: '对话状态不能为空')]
    #[Assert\Choice(callback: [ConversationStatus::class, 'cases'], message: '请选择有效的对话状态')]
    private ConversationStatus $status = ConversationStatus::Active;

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '总Token数量'])]
    #[Assert\Range(min: 1, max: 10000000, minMessage: 'Token数量至少为1', maxMessage: 'Token数量不能超过10000000')]
    private ?int $totalTokens = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 6, nullable: true, options: ['comment' => '费用'])]
    #[Assert\Range(min: 0, max: 999999.999999, minMessage: '费用不能为负数', maxMessage: '费用超出范围')]
    private ?string $cost = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '创建时间'])]
    #[Assert\NotNull(message: '创建时间不能为空')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '更新时间'])]
    #[Assert\NotNull(message: '更新时间不能为空')]
    private \DateTimeImmutable $updatedAt;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '系统提示'])]
    #[Assert\Length(max: 10000, maxMessage: '系统提示长度不能超过10000个字符')]
    private ?string $systemPrompt = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 3, scale: 2, nullable: true, options: ['comment' => '温度参数'])]
    #[Assert\Range(min: 0, max: 2, minMessage: '温度参数不能小于0', maxMessage: '温度参数不能大于2')]
    private ?string $temperature = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function setModel(string $model): void
    {
        $this->model = $model;
    }

    /**
     * @return array<array<string, mixed>>
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @param array<array<string, mixed>> $messages
     */
    public function setMessages(array $messages): void
    {
        $this->messages = $messages;
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * @param array<string, mixed> $message
     */
    public function addMessage(array $message): void
    {
        $this->messages[] = $message;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getStatus(): ConversationStatus
    {
        return $this->status;
    }

    public function setStatus(ConversationStatus $status): void
    {
        $this->status = $status;
    }

    public function getTotalTokens(): ?int
    {
        return $this->totalTokens;
    }

    public function setTotalTokens(?int $totalTokens): void
    {
        $this->totalTokens = $totalTokens;
    }

    public function getCost(): ?string
    {
        return $this->cost;
    }

    public function setCost(?string $cost): void
    {
        $this->cost = $cost;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getSystemPrompt(): ?string
    {
        return $this->systemPrompt;
    }

    public function setSystemPrompt(?string $systemPrompt): void
    {
        $this->systemPrompt = $systemPrompt;
    }

    public function getTemperature(): ?string
    {
        return $this->temperature;
    }

    public function setTemperature(?string $temperature): void
    {
        $this->temperature = $temperature;
    }

    public function __toString(): string
    {
        return $this->title;
    }
}
