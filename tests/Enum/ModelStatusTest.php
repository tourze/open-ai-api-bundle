<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle\Tests\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use Tourze\EnumExtra\BadgeInterface;
use Tourze\OpenAiApiBundle\Enum\ModelStatus;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;

/**
 * ModelStatus枚举测试类
 *
 * @internal
 */
#[CoversClass(ModelStatus::class)]
final class ModelStatusTest extends AbstractEnumTestCase
{
    /**
     * 测试枚举值和标签的正确性
     */
    #[TestWith([ModelStatus::Available, 'available', '可用'])]
    #[TestWith([ModelStatus::Deprecated, 'deprecated', '已弃用'])]
    #[TestWith([ModelStatus::Beta, 'beta', '测试版'])]
    #[TestWith([ModelStatus::Unavailable, 'unavailable', '不可用'])]
    public function testValueAndLabel(ModelStatus $enum, string $expectedValue, string $expectedLabel): void
    {
        $this->assertSame($expectedValue, $enum->value);
        $this->assertSame($expectedLabel, $enum->getLabel());
    }

    /**
     * 测试枚举值的唯一性
     */
    public function testValueUniqueness(): void
    {
        $values = array_map(fn (ModelStatus $enum) => $enum->value, ModelStatus::cases());
        $this->assertSame($values, array_unique($values));
    }

    /**
     * 测试标签的唯一性
     */
    public function testLabelUniqueness(): void
    {
        $labels = array_map(fn (ModelStatus $enum) => $enum->getLabel(), ModelStatus::cases());
        $this->assertSame($labels, array_unique($labels));
    }

    /**
     * 测试getBadge方法返回正确的徽章类型
     */
    #[TestWith([ModelStatus::Available, BadgeInterface::SUCCESS])]
    #[TestWith([ModelStatus::Deprecated, BadgeInterface::WARNING])]
    #[TestWith([ModelStatus::Beta, BadgeInterface::INFO])]
    #[TestWith([ModelStatus::Unavailable, BadgeInterface::DANGER])]
    public function testGetBadgeReturnsCorrectBadgeType(ModelStatus $enum, string $expectedBadge): void
    {
        $this->assertSame($expectedBadge, $enum->getBadge());
    }

    /**
     * 测试toString方法返回正确的字符串值
     */
    public function testToStringReturnsCorrectStringValue(): void
    {
        $this->assertSame('available', ModelStatus::Available->toString());
        $this->assertSame('deprecated', ModelStatus::Deprecated->toString());
        $this->assertSame('beta', ModelStatus::Beta->toString());
        $this->assertSame('unavailable', ModelStatus::Unavailable->toString());
    }

    /**
     * 测试from方法能正确创建枚举实例
     */
    public function testFromWithValidValue(): void
    {
        $this->assertSame(ModelStatus::Available, ModelStatus::from('available'));
        $this->assertSame(ModelStatus::Deprecated, ModelStatus::from('deprecated'));
        $this->assertSame(ModelStatus::Beta, ModelStatus::from('beta'));
        $this->assertSame(ModelStatus::Unavailable, ModelStatus::from('unavailable'));
    }

    /**
     * 测试tryFrom方法能正确创建枚举实例
     */
    public function testTryFromWithValidValue(): void
    {
        $this->assertSame(ModelStatus::Available, ModelStatus::tryFrom('available'));
        $this->assertSame(ModelStatus::Deprecated, ModelStatus::tryFrom('deprecated'));
        $this->assertSame(ModelStatus::Beta, ModelStatus::tryFrom('beta'));
        $this->assertSame(ModelStatus::Unavailable, ModelStatus::tryFrom('unavailable'));
    }

    /**
     * 测试tryFrom方法处理无效值
     */
    public function testTryFromWithInvalidValue(): void
    {
        $this->assertNull(ModelStatus::tryFrom('invalid'));
        $this->assertNull(ModelStatus::tryFrom(''));
        $this->assertNull(ModelStatus::tryFrom('AVAILABLE')); // 大小写敏感
    }

    /**
     * 测试from方法处理无效值时抛出异常
     */
    public function testFromThrowsExceptionForInvalidValue(): void
    {
        $this->expectException(\ValueError::class);
        ModelStatus::from('invalid');
    }

    /**
     * 测试toArray方法返回正确的数组格式
     */
    public function testToArrayReturnsCorrectArrayFormat(): void
    {
        $result = ModelStatus::Available->toArray();

        $expectedResult = [
            'value' => 'available',
            'label' => '可用',
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
        $result = ModelStatus::Available->toSelectItem();

        $expectedResult = [
            'label' => '可用',
            'text' => '可用',
            'value' => 'available',
            'name' => '可用',
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
        $options = ModelStatus::genOptions();

        $expectedOptions = [
            [
                'label' => '可用',
                'text' => '可用',
                'value' => 'available',
                'name' => '可用',
            ],
            [
                'label' => '已弃用',
                'text' => '已弃用',
                'value' => 'deprecated',
                'name' => '已弃用',
            ],
            [
                'label' => '测试版',
                'text' => '测试版',
                'value' => 'beta',
                'name' => '测试版',
            ],
            [
                'label' => '不可用',
                'text' => '不可用',
                'value' => 'unavailable',
                'name' => '不可用',
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
        $this->assertCount(4, ModelStatus::cases());
    }
}
