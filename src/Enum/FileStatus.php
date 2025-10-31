<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle\Enum;

use Tourze\EnumExtra\BadgeInterface;
use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

enum FileStatus: string implements Labelable, BadgeInterface, Itemable, Selectable
{
    use ItemTrait;
    use SelectTrait;
    case Uploaded = 'uploaded';
    case Processed = 'processed';
    case Error = 'error';
    case Deleted = 'deleted';

    public function getLabel(): string
    {
        return match ($this) {
            self::Uploaded => '已上传',
            self::Processed => '已处理',
            self::Error => '错误',
            self::Deleted => '已删除',
        };
    }

    public function getBadge(): string
    {
        return match ($this) {
            self::Uploaded => self::INFO,
            self::Processed => self::SUCCESS,
            self::Error => self::DANGER,
            self::Deleted => self::SECONDARY,
        };
    }

    public function toString(): string
    {
        return $this->value;
    }
}
