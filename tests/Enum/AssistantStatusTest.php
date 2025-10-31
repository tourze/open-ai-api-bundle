<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle\Tests\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use Tourze\EnumExtra\BadgeInterface;
use Tourze\OpenAiApiBundle\Enum\AssistantStatus;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;

/**
 * AssistantStatus枚举测试类
 *
 * @internal
 */
#[CoversClass(AssistantStatus::class)]
final class AssistantStatusTest extends AbstractEnumTestCase
{
    /**
     * 测试枚举值和标签的正确性
     */
    #[TestWith([AssistantStatus::Active, 'active', '活跃'])]
    #[TestWith([AssistantStatus::Inactive, 'inactive', '非活跃'])]
    #[TestWith([AssistantStatus::Archived, 'archived', '已归档'])]
    public function testValueAndLabel(AssistantStatus $enum, string $expectedValue, string $expectedLabel): void
    {
        $this->assertSame($expectedValue, $enum->value);
        $this->assertSame($expectedLabel, $enum->getLabel());
    }

    /**
     * 测试枚举值的唯一性
     */
    public function testValueUniqueness(): void
    {
        $values = array_map(fn (AssistantStatus $enum) => $enum->value, AssistantStatus::cases());
        $this->assertSame($values, array_unique($values));
    }

    /**
     * 测试标签的唯一性
     */
    public function testLabelUniqueness(): void
    {
        $labels = array_map(fn (AssistantStatus $enum) => $enum->getLabel(), AssistantStatus::cases());
        $this->assertSame($labels, array_unique($labels));
    }

    /**
     * 测试getBadge方法返回正确的徽章类型
     */
    #[TestWith([AssistantStatus::Active, BadgeInterface::SUCCESS])]
    #[TestWith([AssistantStatus::Inactive, BadgeInterface::WARNING])]
    #[TestWith([AssistantStatus::Archived, BadgeInterface::SECONDARY])]
    public function testGetBadgeReturnsCorrectBadgeType(AssistantStatus $enum, string $expectedBadge): void
    {
        $this->assertSame($expectedBadge, $enum->getBadge());
    }

    /**
     * 测试toString方法返回正确的字符串值
     */
    public function testToStringReturnsCorrectStringValue(): void
    {
        $this->assertSame('active', AssistantStatus::Active->toString());
        $this->assertSame('inactive', AssistantStatus::Inactive->toString());
        $this->assertSame('archived', AssistantStatus::Archived->toString());
    }

    /**
     * 测试from方法能正确创建枚举实例
     */
    public function testFromWithValidValue(): void
    {
        $this->assertSame(AssistantStatus::Active, AssistantStatus::from('active'));
        $this->assertSame(AssistantStatus::Inactive, AssistantStatus::from('inactive'));
        $this->assertSame(AssistantStatus::Archived, AssistantStatus::from('archived'));
    }

    /**
     * 测试tryFrom方法能正确创建枚举实例
     */
    public function testTryFromWithValidValue(): void
    {
        $this->assertSame(AssistantStatus::Active, AssistantStatus::tryFrom('active'));
        $this->assertSame(AssistantStatus::Inactive, AssistantStatus::tryFrom('inactive'));
        $this->assertSame(AssistantStatus::Archived, AssistantStatus::tryFrom('archived'));
    }

    /**
     * 测试tryFrom方法处理无效值
     */
    public function testTryFromWithInvalidValue(): void
    {
        $this->assertNull(AssistantStatus::tryFrom('invalid'));
        $this->assertNull(AssistantStatus::tryFrom(''));
        $this->assertNull(AssistantStatus::tryFrom('ACTIVE')); // 大小写敏感
    }

    /**
     * 测试from方法处理无效值时抛出异常
     */
    public function testFromThrowsExceptionForInvalidValue(): void
    {
        $this->expectException(\ValueError::class);
        AssistantStatus::from('invalid');
    }

    /**
     * 测试toArray方法返回正确的数组格式
     */
    public function testToArrayReturnsCorrectArrayFormat(): void
    {
        $result = AssistantStatus::Active->toArray();

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
        $result = AssistantStatus::Active->toSelectItem();

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
        $options = AssistantStatus::genOptions();

        $expectedOptions = [
            [
                'label' => '活跃',
                'text' => '活跃',
                'value' => 'active',
                'name' => '活跃',
            ],
            [
                'label' => '非活跃',
                'text' => '非活跃',
                'value' => 'inactive',
                'name' => '非活跃',
            ],
            [
                'label' => '已归档',
                'text' => '已归档',
                'value' => 'archived',
                'name' => '已归档',
            ],
        ];

        $this->assertSame($expectedOptions, $options);
        $this->assertCount(3, $options);
    }

    /**
     * 测试所有枚举cases的数量
     */
    public function testEnumCasesCount(): void
    {
        $this->assertCount(3, AssistantStatus::cases());
    }
}
