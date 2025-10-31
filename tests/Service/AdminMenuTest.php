<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle\Tests\Service;

use Knp\Menu\ItemInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use Tourze\OpenAiApiBundle\Entity\AIModel;
use Tourze\OpenAiApiBundle\Entity\Assistant;
use Tourze\OpenAiApiBundle\Entity\ChatConversation;
use Tourze\OpenAiApiBundle\Entity\Thread;
use Tourze\OpenAiApiBundle\Entity\UploadedFile;
use Tourze\OpenAiApiBundle\Service\AdminMenu;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;

/**
 * @internal
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses]
final class AdminMenuTest extends AbstractEasyAdminMenuTestCase
{
    private LinkGeneratorInterface $linkGenerator;

    private AdminMenu $adminMenu;

    protected function onSetUp(): void
    {
        $this->linkGenerator = $this->createMock(LinkGeneratorInterface::class);
        self::getContainer()->set(LinkGeneratorInterface::class, $this->linkGenerator);
        $this->adminMenu = self::getService(AdminMenu::class);
    }

    public function testImplementsMenuProviderInterface(): void
    {
        $this->assertInstanceOf(MenuProviderInterface::class, $this->adminMenu);
    }

    public function testInvokeCreatesOpenAIMenu(): void
    {
        $mainMenu = $this->createMock(ItemInterface::class);
        $openaiMenu = $this->createMock(ItemInterface::class);

        $mainMenu->expects(self::once())
            ->method('getChild')
            ->with('OpenAI API')
            ->willReturn(null)
        ;

        $mainMenu->expects(self::once())
            ->method('addChild')
            ->with('OpenAI API')
            ->willReturn($openaiMenu)
        ;

        $linkGeneratorExpects = $this->linkGenerator->expects(self::exactly(5));
        /** @phpstan-var InvocationMocker $linkGeneratorExpects */
        $linkGeneratorWithMethod = $linkGeneratorExpects->method('getCurdListPage');
        /** @phpstan-var InvocationMocker $linkGeneratorWithMethod */
        $linkGeneratorWithMethod->willReturnMap([
            [ChatConversation::class, '/admin/openai/conversation'],
            [AIModel::class, '/admin/openai/aimodel'],
            [UploadedFile::class, '/admin/openai/file'],
            [Assistant::class, '/admin/openai/assistant'],
            [Thread::class, '/admin/openai/thread'],
        ]);

        $openaiMenu->expects(self::exactly(5))
            ->method('addChild')
            ->willReturnCallback(function ($name) {
                $childMenu = $this->createMock(ItemInterface::class);
                $childMenu->method('setUri')->willReturnSelf();
                $childMenu->method('setAttribute')->willReturnSelf();

                return $childMenu;
            })
        ;

        $this->adminMenu->__invoke($mainMenu);
    }

    public function testInvokeHandlesExistingMenu(): void
    {
        $mainMenu = $this->createMock(ItemInterface::class);
        $existingOpenaiMenu = $this->createMock(ItemInterface::class);

        $mainMenu->expects(self::once())
            ->method('getChild')
            ->with('OpenAI API')
            ->willReturn($existingOpenaiMenu)
        ;

        $mainMenu->expects(self::never())
            ->method('addChild')
        ;

        $linkGeneratorExpects = $this->linkGenerator->expects(self::exactly(5));
        /** @phpstan-var InvocationMocker $linkGeneratorExpects */
        $linkGeneratorWithMethod = $linkGeneratorExpects->method('getCurdListPage');
        /** @phpstan-var InvocationMocker $linkGeneratorWithMethod */
        $linkGeneratorWithMethod->willReturnMap([
            [ChatConversation::class, '/admin/openai/conversation'],
            [AIModel::class, '/admin/openai/aimodel'],
            [UploadedFile::class, '/admin/openai/file'],
            [Assistant::class, '/admin/openai/assistant'],
            [Thread::class, '/admin/openai/thread'],
        ]);

        $existingOpenaiMenu->expects(self::exactly(5))
            ->method('addChild')
            ->willReturnCallback(function ($name) {
                $childMenu = $this->createMock(ItemInterface::class);
                $childMenu->method('setUri')->willReturnSelf();
                $childMenu->method('setAttribute')->willReturnSelf();

                return $childMenu;
            })
        ;

        $this->adminMenu->__invoke($mainMenu);
    }

    public function testAdminMenuServiceExists(): void
    {
        $this->assertInstanceOf(AdminMenu::class, $this->adminMenu);
        $this->assertInstanceOf(MenuProviderInterface::class, $this->adminMenu);
    }
}
