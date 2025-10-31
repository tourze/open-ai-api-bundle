<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle\Tests\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use Tourze\EnumExtra\BadgeInterface;
use Tourze\OpenAiApiBundle\Enum\ConversationStatus;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;

/**
 * ConversationStatus枚举测试类
 *
 * @internal
 */
#[CoversClass(ConversationStatus::class)]
final class ConversationStatusTest extends AbstractEnumTestCase
{
    /**
     * 测试枚举值和标签的正确性
     */
    #[TestWith([ConversationStatus::Active, 'active', '活跃'])]
    #[TestWith([ConversationStatus::Completed, 'completed', '已完成'])]
    #[TestWith([ConversationStatus::Archived, 'archived', '已归档'])]
    #[TestWith([ConversationStatus::Failed, 'failed', '失败'])]
    public function testValueAndLabel(ConversationStatus $enum, string $expectedValue, string $expectedLabel): void
    {
        $this->assertSame($expectedValue, $enum->value);
        $this->assertSame($expectedLabel, $enum->getLabel());
    }

    /**
     * 测试枚举值的唯一性
     */
    public function testValueUniqueness(): void
    {
        $values = array_map(fn (ConversationStatus $enum) => $enum->value, ConversationStatus::cases());
        $this->assertSame($values, array_unique($values));
    }

    /**
     * 测试标签的唯一性
     */
    public function testLabelUniqueness(): void
    {
        $labels = array_map(fn (ConversationStatus $enum) => $enum->getLabel(), ConversationStatus::cases());
        $this->assertSame($labels, array_unique($labels));
    }

    /**
     * 测试getBadge方法返回正确的徽章类型
     */
    #[TestWith([ConversationStatus::Active, BadgeInterface::SUCCESS])]
    #[TestWith([ConversationStatus::Completed, BadgeInterface::PRIMARY])]
    #[TestWith([ConversationStatus::Archived, BadgeInterface::SECONDARY])]
    #[TestWith([ConversationStatus::Failed, BadgeInterface::DANGER])]
    public function testGetBadgeReturnsCorrectBadgeType(ConversationStatus $enum, string $expectedBadge): void
    {
        $this->assertSame($expectedBadge, $enum->getBadge());
    }

    /**
     * 测试toString方法返回正确的字符串值
     */
    public function testToStringReturnsCorrectStringValue(): void
    {
        $this->assertSame('active', ConversationStatus::Active->toString());
        $this->assertSame('completed', ConversationStatus::Completed->toString());
        $this->assertSame('archived', ConversationStatus::Archived->toString());
        $this->assertSame('failed', ConversationStatus::Failed->toString());
    }

    /**
     * 测试from方法能正确创建枚举实例
     */
    public function testFromWithValidValue(): void
    {
        $this->assertSame(ConversationStatus::Active, ConversationStatus::from('active'));
        $this->assertSame(ConversationStatus::Completed, ConversationStatus::from('completed'));
        $this->assertSame(ConversationStatus::Archived, ConversationStatus::from('archived'));
        $this->assertSame(ConversationStatus::Failed, ConversationStatus::from('failed'));
    }

    /**
     * 测试tryFrom方法能正确创建枚举实例
     */
    public function testTryFromWithValidValue(): void
    {
        $this->assertSame(ConversationStatus::Active, ConversationStatus::tryFrom('active'));
        $this->assertSame(ConversationStatus::Completed, ConversationStatus::tryFrom('completed'));
        $this->assertSame(ConversationStatus::Archived, ConversationStatus::tryFrom('archived'));
        $this->assertSame(ConversationStatus::Failed, ConversationStatus::tryFrom('failed'));
    }

    /**
     * 测试tryFrom方法处理无效值
     */
    public function testTryFromWithInvalidValue(): void
    {
        $this->assertNull(ConversationStatus::tryFrom('invalid'));
        $this->assertNull(ConversationStatus::tryFrom(''));
        $this->assertNull(ConversationStatus::tryFrom('ACTIVE')); // 大小写敏感
    }

    /**
     * 测试from方法处理无效值时抛出异常
     */
    public function testFromThrowsExceptionForInvalidValue(): void
    {
        $this->expectException(\ValueError::class);
        ConversationStatus::from('invalid');
    }

    /**
     * 测试toArray方法返回正确的数组格式
     */
    public function testToArrayReturnsCorrectArrayFormat(): void
    {
        $result = ConversationStatus::Active->toArray();

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
        $result = ConversationStatus::Active->toSelectItem();

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
        $options = ConversationStatus::genOptions();

        $expectedOptions = [
            [
                'label' => '活跃',
                'text' => '活跃',
                'value' => 'active',
                'name' => '活跃',
            ],
            [
                'label' => '已完成',
                'text' => '已完成',
                'value' => 'completed',
                'name' => '已完成',
            ],
            [
                'label' => '已归档',
                'text' => '已归档',
                'value' => 'archived',
                'name' => '已归档',
            ],
            [
                'label' => '失败',
                'text' => '失败',
                'value' => 'failed',
                'name' => '失败',
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
        $this->assertCount(4, ConversationStatus::cases());
    }
}
