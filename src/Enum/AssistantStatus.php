<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle\Enum;

use Tourze\EnumExtra\BadgeInterface;
use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

enum AssistantStatus: string implements Labelable, BadgeInterface, Itemable, Selectable
{
    use ItemTrait;
    use SelectTrait;
    case Active = 'active';
    case Inactive = 'inactive';
    case Archived = 'archived';

    public function getLabel(): string
    {
        return match ($this) {
            self::Active => '活跃',
            self::Inactive => '非活跃',
            self::Archived => '已归档',
        };
    }

    public function getBadge(): string
    {
        return match ($this) {
            self::Active => self::SUCCESS,
            self::Inactive => self::WARNING,
            self::Archived => self::SECONDARY,
        };
    }

    public function toString(): string
    {
        return $this->value;
    }
}
