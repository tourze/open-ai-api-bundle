<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle\Enum;

use Tourze\EnumExtra\BadgeInterface;
use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

enum ConversationStatus: string implements Labelable, BadgeInterface, Itemable, Selectable
{
    use ItemTrait;
    use SelectTrait;
    case Active = 'active';
    case Completed = 'completed';
    case Archived = 'archived';
    case Failed = 'failed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Active => '活跃',
            self::Completed => '已完成',
            self::Archived => '已归档',
            self::Failed => '失败',
        };
    }

    public function getBadge(): string
    {
        return match ($this) {
            self::Active => self::SUCCESS,
            self::Completed => self::PRIMARY,
            self::Archived => self::SECONDARY,
            self::Failed => self::DANGER,
        };
    }

    public function toString(): string
    {
        return $this->value;
    }
}
