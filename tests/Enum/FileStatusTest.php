<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle\Tests\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use Tourze\EnumExtra\BadgeInterface;
use Tourze\OpenAiApiBundle\Enum\FileStatus;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;

/**
 * FileStatus枚举测试类
 *
 * @internal
 */
#[CoversClass(FileStatus::class)]
final class FileStatusTest extends AbstractEnumTestCase
{
    /**
     * 测试枚举值和标签的正确性
     */
    #[TestWith([FileStatus::Uploaded, 'uploaded', '已上传'])]
    #[TestWith([FileStatus::Processed, 'processed', '已处理'])]
    #[TestWith([FileStatus::Error, 'error', '错误'])]
    #[TestWith([FileStatus::Deleted, 'deleted', '已删除'])]
    public function testValueAndLabel(FileStatus $enum, string $expectedValue, string $expectedLabel): void
    {
        $this->assertSame($expectedValue, $enum->value);
        $this->assertSame($expectedLabel, $enum->getLabel());
    }

    /**
     * 测试枚举值的唯一性
     */
    public function testValueUniqueness(): void
    {
        $values = array_map(fn (FileStatus $enum) => $enum->value, FileStatus::cases());
        $this->assertSame($values, array_unique($values));
    }

    /**
     * 测试标签的唯一性
     */
    public function testLabelUniqueness(): void
    {
        $labels = array_map(fn (FileStatus $enum) => $enum->getLabel(), FileStatus::cases());
        $this->assertSame($labels, array_unique($labels));
    }

    /**
     * 测试getBadge方法返回正确的徽章类型
     */
    #[TestWith([FileStatus::Uploaded, BadgeInterface::INFO])]
    #[TestWith([FileStatus::Processed, BadgeInterface::SUCCESS])]
    #[TestWith([FileStatus::Error, BadgeInterface::DANGER])]
    #[TestWith([FileStatus::Deleted, BadgeInterface::SECONDARY])]
    public function testGetBadgeReturnsCorrectBadgeType(FileStatus $enum, string $expectedBadge): void
    {
        $this->assertSame($expectedBadge, $enum->getBadge());
    }

    /**
     * 测试toString方法返回正确的字符串值
     */
    public function testToStringReturnsCorrectStringValue(): void
    {
        $this->assertSame('uploaded', FileStatus::Uploaded->toString());
        $this->assertSame('processed', FileStatus::Processed->toString());
        $this->assertSame('error', FileStatus::Error->toString());
        $this->assertSame('deleted', FileStatus::Deleted->toString());
    }

    /**
     * 测试from方法能正确创建枚举实例
     */
    public function testFromWithValidValue(): void
    {
        $this->assertSame(FileStatus::Uploaded, FileStatus::from('uploaded'));
        $this->assertSame(FileStatus::Processed, FileStatus::from('processed'));
        $this->assertSame(FileStatus::Error, FileStatus::from('error'));
        $this->assertSame(FileStatus::Deleted, FileStatus::from('deleted'));
    }

    /**
     * 测试tryFrom方法能正确创建枚举实例
     */
    public function testTryFromWithValidValue(): void
    {
        $this->assertSame(FileStatus::Uploaded, FileStatus::tryFrom('uploaded'));
        $this->assertSame(FileStatus::Processed, FileStatus::tryFrom('processed'));
        $this->assertSame(FileStatus::Error, FileStatus::tryFrom('error'));
        $this->assertSame(FileStatus::Deleted, FileStatus::tryFrom('deleted'));
    }

    /**
     * 测试tryFrom方法处理无效值
     */
    public function testTryFromWithInvalidValue(): void
    {
        $this->assertNull(FileStatus::tryFrom('invalid'));
        $this->assertNull(FileStatus::tryFrom(''));
        $this->assertNull(FileStatus::tryFrom('UPLOADED')); // 大小写敏感
    }

    /**
     * 测试from方法处理无效值时抛出异常
     */
    public function testFromThrowsExceptionForInvalidValue(): void
    {
        $this->expectException(\ValueError::class);
        FileStatus::from('invalid');
    }

    /**
     * 测试toArray方法返回正确的数组格式
     */
    public function testToArrayReturnsCorrectArrayFormat(): void
    {
        $result = FileStatus::Uploaded->toArray();

        $expectedResult = [
            'value' => 'uploaded',
            'label' => '已上传',
        ];

        $this->assertSame($expectedResult, $result);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('value', $result);
        $this->assertArrayHasKey('label', $result);
    }

    /**
     * 测试toSelectItem方法返回正确的选择项格式
     */
    public function testToSelectItemReturnsCorrectSelectItemFormat(): void
    {
        $result = FileStatus::Uploaded->toSelectItem();

        $expectedResult = [
            'label' => '已上传',
            'text' => '已上传',
            'value' => 'uploaded',
            'name' => '已上传',
        ];

        $this->assertSame($expectedResult, $result);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('value', $result);
        $this->assertArrayHasKey('label', $result);
        $this->assertArrayHasKey('text', $result);
        $this->assertArrayHasKey('name', $result);
    }

    /**
     * 测试genOptions静态方法返回正确的选项数组
     */
    public function testGenOptionsReturnsCorrectOptionsArray(): void
    {
        $options = FileStatus::genOptions();

        $expectedOptions = [
            [
                'label' => '已上传',
                'text' => '已上传',
                'value' => 'uploaded',
                'name' => '已上传',
            ],
            [
                'label' => '已处理',
                'text' => '已处理',
                'value' => 'processed',
                'name' => '已处理',
            ],
            [
                'label' => '错误',
                'text' => '错误',
                'value' => 'error',
                'name' => '错误',
            ],
            [
                'label' => '已删除',
                'text' => '已删除',
                'value' => 'deleted',
                'name' => '已删除',
            ],
        ];

        $this->assertSame($expectedOptions, $options);
        $this->assertCount(4, $options);
    }

    /**
     * 测试所有枚举cases的数量
     */
    public function testEnumCasesCount(): void
    {
        $this->assertCount(4, FileStatus::cases());
    }
}
