<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle\Enum;

use Tourze\EnumExtra\BadgeInterface;
use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

enum ModelStatus: string implements Labelable, BadgeInterface, Itemable, Selectable
{
    use ItemTrait;
    use SelectTrait;
    case Available = 'available';
    case Deprecated = 'deprecated';
    case Beta = 'beta';
    case Unavailable = 'unavailable';

    public function getLabel(): string
    {
        return match ($this) {
            self::Available => '可用',
            self::Deprecated => '已弃用',
            self::Beta => '测试版',
            self::Unavailable => '不可用',
        };
    }

    public function getBadge(): string
    {
        return match ($this) {
            self::Available => self::SUCCESS,
            self::Deprecated => self::WARNING,
            self::Beta => self::INFO,
            self::Unavailable => self::DANGER,
        };
    }

    public function toString(): string
    {
        return $this->value;
    }
}
