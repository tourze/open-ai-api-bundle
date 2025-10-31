<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle\Tests\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use Tourze\EnumExtra\BadgeInterface;
use Tourze\OpenAiApiBundle\Enum\ThreadStatus;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;

/**
 * ThreadStatus枚举测试类
 *
 * @internal
 */
#[CoversClass(ThreadStatus::class)]
final class ThreadStatusTest extends AbstractEnumTestCase
{
    /**
     * 测试枚举值和标签的正确性
     */
    #[TestWith([ThreadStatus::Active, 'active', '活跃'])]
    #[TestWith([ThreadStatus::Archived, 'archived', '已归档'])]
    #[TestWith([ThreadStatus::Completed, 'completed', '已完成'])]
    #[TestWith([ThreadStatus::Deleted, 'deleted', '已删除'])]
    public function testValueAndLabel(ThreadStatus $enum, string $expectedValue, string $expectedLabel): void
    {
        $this->assertSame($expectedValue, $enum->value);
        $this->assertSame($expectedLabel, $enum->getLabel());
    }

    /**
     * 测试枚举值的唯一性
     */
    public function testValueUniqueness(): void
    {
        $values = array_map(fn (ThreadStatus $enum) => $enum->value, ThreadStatus::cases());
        $this->assertSame($values, array_unique($values));
    }

    /**
     * 测试标签的唯一性
     */
    public function testLabelUniqueness(): void
    {
        $labels = array_map(fn (ThreadStatus $enum) => $enum->getLabel(), ThreadStatus::cases());
        $this->assertSame($labels, array_unique($labels));
    }

    /**
     * 测试getBadge方法返回正确的徽章类型
     */
    #[TestWith([ThreadStatus::Active, BadgeInterface::SUCCESS])]
    #[TestWith([ThreadStatus::Archived, BadgeInterface::SECONDARY])]
    #[TestWith([ThreadStatus::Completed, BadgeInterface::PRIMARY])]
    #[TestWith([ThreadStatus::Deleted, BadgeInterface::SECONDARY])]
    public function testGetBadgeReturnsCorrectBadgeType(ThreadStatus $enum, string $expectedBadge): void
    {
        $this->assertSame($expectedBadge, $enum->getBadge());
    }

    /**
     * 测试toString方法返回正确的字符串值
     */
    public function testToStringReturnsCorrectStringValue(): void
    {
        $this->assertSame('active', ThreadStatus::Active->toString());
        $this->assertSame('archived', ThreadStatus::Archived->toString());
        $this->assertSame('completed', ThreadStatus::Completed->toString());
        $this->assertSame('deleted', ThreadStatus::Deleted->toString());
    }

    /**
     * 测试from方法能正确创建枚举实例
     */
    public function testFromWithValidValue(): void
    {
        $this->assertSame(ThreadStatus::Active, ThreadStatus::from('active'));
        $this->assertSame(ThreadStatus::Archived, ThreadStatus::from('archived'));
        $this->assertSame(ThreadStatus::Completed, ThreadStatus::from('completed'));
        $this->assertSame(ThreadStatus::Deleted, ThreadStatus::from('deleted'));
    }

    /**
     * 测试tryFrom方法能正确创建枚举实例
     */
    public function testTryFromWithValidValue(): void
    {
        $this->assertSame(ThreadStatus::Active, ThreadStatus::tryFrom('active'));
        $this->assertSame(ThreadStatus::Archived, ThreadStatus::tryFrom('archived'));
        $this->assertSame(ThreadStatus::Completed, ThreadStatus::tryFrom('completed'));
        $this->assertSame(ThreadStatus::Deleted, ThreadStatus::tryFrom('deleted'));
    }

    /**
     * 测试tryFrom方法处理无效值
     */
    public function testTryFromWithInvalidValue(): void
    {
        $this->assertNull(ThreadStatus::tryFrom('invalid'));
        $this->assertNull(ThreadStatus::tryFrom(''));
        $this->assertNull(ThreadStatus::tryFrom('ACTIVE')); // 大小写敏感
    }

    /**
     * 测试from方法处理无效值时抛出异常
     */
    public function testFromThrowsExceptionForInvalidValue(): void
    {
        $this->expectException(\ValueError::class);
        ThreadStatus::from('invalid');
    }

    /**
     * 测试toArray方法返回正确的数组格式
     */
    public function testToArrayReturnsCorrectArrayFormat(): void
    {
        $result = ThreadStatus::Active->toArray();

        $expectedResult = [
            'value' => 'active',
            'label' => '活跃',
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
        $result = ThreadStatus::Active->toSelectItem();

        $expectedResult = [
            'label' => '活跃',
            'text' => '活跃',
            'value' => 'active',
            'name' => '活跃',
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
        $options = ThreadStatus::genOptions();

        $expectedOptions = [
            [
                'label' => '活跃',
                'text' => '活跃',
                'value' => 'active',
                'name' => '活跃',
            ],
            [
                'label' => '已归档',
                'text' => '已归档',
                'value' => 'archived',
                'name' => '已归档',
            ],
            [
                'label' => '已完成',
                'text' => '已完成',
                'value' => 'completed',
                'name' => '已完成',
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
        $this->assertCount(4, ThreadStatus::cases());
    }
}
