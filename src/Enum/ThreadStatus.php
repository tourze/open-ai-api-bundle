<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle\Enum;

use Tourze\EnumExtra\BadgeInterface;
use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

enum ThreadStatus: string implements Labelable, BadgeInterface, Itemable, Selectable
{
    use ItemTrait;
    use SelectTrait;
    case Active = 'active';
    case Archived = 'archived';
    case Completed = 'completed';
    case Deleted = 'deleted';

    public function getLabel(): string
    {
        return match ($this) {
            self::Active => '活跃',
            self::Archived => '已归档',
            self::Completed => '已完成',
            self::Deleted => '已删除',
        };
    }

    public function getBadge(): string
    {
        return match ($this) {
            self::Active => self::SUCCESS,
            self::Archived => self::SECONDARY,
            self::Completed => self::PRIMARY,
            self::Deleted => self::SECONDARY,
        };
    }

    public function toString(): string
    {
        return $this->value;
    }
}
