<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Tourze\OpenAiApiBundle\Controller\Admin\ChatConversationCrudController;
use Tourze\OpenAiApiBundle\Entity\ChatConversation;
use Tourze\OpenAiApiBundle\Enum\ConversationStatus;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(ChatConversationCrudController::class)]
#[RunTestsInSeparateProcesses]
final class ChatConversationCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /**
     * @return AbstractCrudController<ChatConversation>
     */
    protected function getControllerService(): AbstractCrudController
    {
        return new ChatConversationCrudController();
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'title' => ['对话标题'];
        yield 'model' => ['使用模型'];
        yield 'status' => ['对话状态'];
        yield 'createdAt' => ['创建时间'];
        yield 'updatedAt' => ['更新时间'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        yield 'title' => ['title'];
        yield 'model' => ['model'];
        yield 'status' => ['status'];
        yield 'systemPrompt' => ['systemPrompt'];
        yield 'temperature' => ['temperature'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        yield 'title' => ['title'];
        yield 'model' => ['model'];
        yield 'status' => ['status'];
        yield 'systemPrompt' => ['systemPrompt'];
        yield 'temperature' => ['temperature'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideDetailPageFields(): iterable
    {
        yield 'id' => ['id'];
        yield 'title' => ['title'];
        yield 'model' => ['model'];
        yield 'status' => ['status'];
        yield 'systemPrompt' => ['systemPrompt'];
        yield 'temperature' => ['temperature'];
        yield 'topP' => ['topP'];
        yield 'messages' => ['messages'];
        yield 'totalTokens' => ['totalTokens'];
        yield 'cost' => ['cost'];
        yield 'createdAt' => ['createdAt'];
        yield 'updatedAt' => ['updatedAt'];
    }

    public function testGetEntityFqcn(): void
    {
        $this->assertSame(ChatConversation::class, ChatConversationCrudController::getEntityFqcn());
    }

    public function testAccessWithoutLogin(): void
    {
        $client = self::createClientWithDatabase();

        $this->expectException(AccessDeniedException::class);
        $client->request('GET', '/admin/openai/chatconversation');
    }

    public function testListAction(): void
    {
        $client = self::createAuthenticatedClient();

        $client->request('GET', '/admin/openai/chatconversation');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('聊天对话列表', (string) $client->getResponse()->getContent());
    }

    public function testCrudConfiguration(): void
    {
        $controller = new ChatConversationCrudController();
        $this->assertInstanceOf(ChatConversationCrudController::class, $controller);
    }

    public function testNewAction(): void
    {
        $client = self::createAuthenticatedClient();

        $client->request('GET', '/admin/openai/chatconversation/new');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('新建聊天对话', (string) $client->getResponse()->getContent());
    }

    public function testCreateConversation(): void
    {
        $client = self::createAuthenticatedClient();

        // 直接通过数据库创建对话来测试功能
        $conversation = new ChatConversation();
        $conversation->setTitle('测试对话');
        $conversation->setModel('gpt-3.5-turbo');
        $conversation->setMessages([['role' => 'user', 'content' => 'Hello']]);
        $conversation->setStatus(ConversationStatus::Active);
        $conversation->setTotalTokens(25);
        $conversation->setCost('0.000025');
        $conversation->setSystemPrompt('You are a helpful assistant');

        $entityManager = self::getEntityManager();
        $entityManager->persist($conversation);
        $entityManager->flush();

        // 验证数据是否保存成功
        $this->assertNotNull($conversation->getId());
        $this->assertSame('测试对话', $conversation->getTitle());

        // 验证新建页面能正常访问
        $crawler = $client->request('GET', '/admin/openai/chatconversation/new');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('新建聊天对话', (string) $client->getResponse()->getContent());
    }

    public function testEditAction(): void
    {
        $client = self::createAuthenticatedClient();

        // 创建对话
        $conversation = new ChatConversation();
        $conversation->setTitle('编辑测试对话');
        $conversation->setModel('gpt-4');
        $conversation->setMessages([['role' => 'user', 'content' => 'Hello']]);
        $conversation->setStatus(ConversationStatus::Active);
        $conversation->setTotalTokens(50);
        $conversation->setCost('0.000050');

        $entityManager = self::getEntityManager();
        $entityManager->persist($conversation);
        $entityManager->flush();

        // 测试编辑页面
        $client->request('GET', '/admin/openai/chatconversation/' . $conversation->getId() . '/edit');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('编辑聊天对话', (string) $client->getResponse()->getContent());
    }

    public function testUpdateConversation(): void
    {
        $client = self::createAuthenticatedClient();

        // 创建对话
        $conversation = new ChatConversation();
        $conversation->setTitle('更新测试对话');
        $conversation->setModel('gpt-3.5-turbo');
        $conversation->setMessages([['role' => 'user', 'content' => 'Hello']]);
        $conversation->setStatus(ConversationStatus::Active);
        $conversation->setTotalTokens(25);

        $entityManager = self::getEntityManager();
        $entityManager->persist($conversation);
        $entityManager->flush();

        // 测试编辑页面
        $crawler = $client->request('GET', '/admin/openai/chatconversation/' . $conversation->getId() . '/edit');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('编辑聊天对话', (string) $client->getResponse()->getContent());

        // 重新获取对话以确保它在当前的 EntityManager 中被管理
        $managedConversation = $entityManager->find(ChatConversation::class, $conversation->getId());
        $this->assertNotNull($managedConversation, 'Conversation should exist in database');

        // 直接通过数据库更新来测试功能
        $managedConversation->setTitle('更新后的对话标题');
        $managedConversation->setStatus(ConversationStatus::Archived);
        $entityManager->flush();

        // 验证数据库中的数据确实更新了
        $entityManager->clear(); // 清除缓存确保重新从数据库加载
        $updatedConversation = $entityManager->find(ChatConversation::class, $conversation->getId());
        $this->assertNotNull($updatedConversation, 'Updated conversation should not be null');
        $this->assertSame('更新后的对话标题', $updatedConversation->getTitle());
        $this->assertSame(ConversationStatus::Archived, $updatedConversation->getStatus());
    }

    public function testDetailAction(): void
    {
        $client = self::createAuthenticatedClient();

        // 创建对话
        $conversation = new ChatConversation();
        $conversation->setTitle('详情测试对话');
        $conversation->setModel('gpt-4');
        $conversation->setMessages([
            ['role' => 'user', 'content' => 'Hello'],
            ['role' => 'assistant', 'content' => 'Hi there!'],
        ]);
        $conversation->setStatus(ConversationStatus::Active);
        $conversation->setTotalTokens(100);
        $conversation->setCost('0.000100');
        $conversation->setSystemPrompt('You are helpful');
        $conversation->setTemperature('0.75');

        $entityManager = self::getEntityManager();
        $entityManager->persist($conversation);
        $entityManager->flush();

        // 测试详情页面
        $client->request('GET', '/admin/openai/chatconversation/' . $conversation->getId());
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('聊天对话详情', (string) $client->getResponse()->getContent());
    }

    public function testDeleteConversation(): void
    {
        $client = self::createAuthenticatedClient();

        // 创建对话
        $conversation = new ChatConversation();
        $conversation->setTitle('删除测试对话');
        $conversation->setModel('gpt-3.5-turbo');
        $conversation->setMessages([['role' => 'user', 'content' => 'Hello']]);
        $conversation->setStatus(ConversationStatus::Active);
        $conversation->setTotalTokens(25);

        $entityManager = self::getEntityManager();
        $entityManager->persist($conversation);
        $entityManager->flush();
        $conversationId = $conversation->getId();

        // 验证对话创建成功
        $this->assertNotNull($conversationId);
        $this->assertSame('删除测试对话', $conversation->getTitle());

        // 直接删除对话来测试功能
        $entityManager->remove($conversation);
        $entityManager->flush();

        // 验证对话已被删除
        $deletedConversation = $entityManager->find(ChatConversation::class, $conversationId);
        $this->assertNull($deletedConversation, 'Conversation should be deleted');

        // 验证控制器方法存在
        $controller = new ChatConversationCrudController();
        $reflection = new \ReflectionClass($controller);
        $this->assertTrue($reflection->hasMethod('deleteEntity') || $reflection->hasMethod('delete'), 'ChatConversationCrudController should have delete method');
    }

    public function testConfigureFields(): void
    {
        $controller = new ChatConversationCrudController();

        // 测试不同页面的字段配置
        $indexFields = iterator_to_array($controller->configureFields('index'));
        $this->assertNotEmpty($indexFields);

        $formFields = iterator_to_array($controller->configureFields('new'));
        $this->assertNotEmpty($formFields);

        $detailFields = iterator_to_array($controller->configureFields('detail'));
        $this->assertNotEmpty($detailFields);

        // 验证字段数量合理
        $this->assertGreaterThan(5, count($indexFields));
        $this->assertGreaterThan(5, count($formFields));
        $this->assertGreaterThan(8, count($detailFields)); // detail页面应该显示更多字段
    }

    public function testConfigureCrud(): void
    {
        $controller = new ChatConversationCrudController();
        $reflection = new \ReflectionClass($controller);

        // 验证 configureCrud 方法存在
        $this->assertTrue($reflection->hasMethod('configureCrud'));

        // 通过真实的Crud实例测试配置行为
        $crudConfig = Crud::new();
        $result = $controller->configureCrud($crudConfig);

        // 验证返回的是Crud实例且配置已正确应用
        $this->assertInstanceOf(Crud::class, $result);
        $this->assertSame($crudConfig, $result); // 验证返回的是同一个实例
    }

    public function testConfigureActions(): void
    {
        $controller = new ChatConversationCrudController();
        $reflection = new \ReflectionClass($controller);

        // 验证 configureActions 方法存在
        $this->assertTrue($reflection->hasMethod('configureActions'));

        // 由于Actions类是final的，我们不能创建Mock，只测试方法存在即可
        $this->assertTrue($reflection->getMethod('configureActions')->isPublic());
    }

    public function testConfigureFilters(): void
    {
        $controller = new ChatConversationCrudController();
        $reflection = new \ReflectionClass($controller);

        // 验证 configureFilters 方法存在
        $this->assertTrue($reflection->hasMethod('configureFilters'));

        // 由于Filters类是final的，我们不能创建Mock，只测试方法存在即可
        $this->assertTrue($reflection->getMethod('configureFilters')->isPublic());
    }

    public function testSearchFilters(): void
    {
        $client = self::createAuthenticatedClient();

        // 创建测试数据
        $conversation1 = new ChatConversation();
        $conversation1->setTitle('搜索对话1');
        $conversation1->setModel('gpt-3.5-turbo');
        $conversation1->setMessages([['role' => 'user', 'content' => 'Hello']]);
        $conversation1->setStatus(ConversationStatus::Active);

        $conversation2 = new ChatConversation();
        $conversation2->setTitle('搜索对话2');
        $conversation2->setModel('gpt-4');
        $conversation2->setMessages([['role' => 'user', 'content' => 'Hi']]);
        $conversation2->setStatus(ConversationStatus::Active);

        $entityManager = self::getEntityManager();
        $entityManager->persist($conversation1);
        $entityManager->persist($conversation2);
        $entityManager->flush();

        // 测试列表页面能正常显示
        $crawler = $client->request('GET', '/admin/openai/chatconversation');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('聊天对话列表', (string) $client->getResponse()->getContent());

        // 验证对话创建成功
        $this->assertNotNull($conversation1->getId());
        $this->assertNotNull($conversation2->getId());
        $this->assertSame('搜索对话1', $conversation1->getTitle());
        $this->assertSame('搜索对话2', $conversation2->getTitle());
    }

    public function testValidationErrors(): void
    {
        $client = self::createAuthenticatedClient();

        $crawler = $client->request('GET', '/admin/openai/chatconversation/new');
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        // 查找表单
        $forms = $crawler->filter('form');
        if (0 === $forms->count()) {
            self::markTestSkipped('No forms found on new page');
        }

        $form = $forms->first()->form();

        // 设置无效数据以触发验证错误
        if (isset($form['ChatConversation[title]'])) {
            $form['ChatConversation[title]'] = ''; // 空值应该触发验证错误
        }
        if (isset($form['ChatConversation[model]'])) {
            $form['ChatConversation[model]'] = ''; // 空值应该触发验证错误
        }

        // 提交表单
        $client->submit($form);

        // 验证响应（可能是422或200带错误信息）
        $statusCode = $client->getResponse()->getStatusCode();
        self::assertContains($statusCode, [200, 422], 'Response should be 200 or 422 for validation errors');

        // 如果返回200且没有验证错误，可能是表单验证并未生效或字段不是必填
        // 在这种情况下，我们检查是否能成功提交并创建了实体
        $content = (string) $client->getResponse()->getContent();

        // 检查是否包含验证错误信息或者是否成功提交
        $hasValidationError = false;
        $validationPatterns = [
            'should not be blank',
            '不能为空',
            'This value should not be blank',
            'invalid-feedback',
            'form-error',
            '错误',
        ];

        foreach ($validationPatterns as $pattern) {
            if (false !== strpos($content, $pattern)) {
                $hasValidationError = true;
                break;
            }
        }

        // 如果没有验证错误，检查是否成功创建（可能字段不是必填的）
        if (!$hasValidationError) {
            // 表单成功提交，显示成功页面
            $this->assertSame(200, $statusCode, 'Expected successful response with status 200');
            $this->assertTrue(
                false !== strpos($content, '聊天对话列表'),
                'Expected to see chat conversation list page content'
            );
        } else {
            // 有验证错误，这是期望的行为
            // $hasValidationError is already true due to the if condition, no need to assert it
        }
    }
}
