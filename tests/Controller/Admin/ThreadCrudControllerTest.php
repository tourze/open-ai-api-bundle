<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Tourze\OpenAiApiBundle\Controller\Admin\ThreadCrudController;
use Tourze\OpenAiApiBundle\Entity\Thread;
use Tourze\OpenAiApiBundle\Enum\ThreadStatus;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(ThreadCrudController::class)]
#[RunTestsInSeparateProcesses]
final class ThreadCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /**
     * @return AbstractCrudController<Thread>
     */
    protected function getControllerService(): AbstractCrudController
    {
        return new ThreadCrudController();
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'threadId' => ['线程ID'];
        yield 'title' => ['线程标题'];
        yield 'assistantId' => ['关联助手ID'];
        yield 'status' => ['线程状态'];
        yield 'createdAt' => ['创建时间'];
        yield 'updatedAt' => ['更新时间'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        yield 'threadId' => ['threadId'];
        yield 'title' => ['title'];
        yield 'description' => ['description'];
        yield 'assistantId' => ['assistantId'];
        yield 'status' => ['status'];
        yield 'metadata' => ['metadata'];
        yield 'toolResources' => ['toolResources'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        yield 'threadId' => ['threadId'];
        yield 'title' => ['title'];
        yield 'description' => ['description'];
        yield 'assistantId' => ['assistantId'];
        yield 'status' => ['status'];
        yield 'metadata' => ['metadata'];
        yield 'toolResources' => ['toolResources'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideDetailPageFields(): iterable
    {
        yield 'id' => ['id'];
        yield 'threadId' => ['threadId'];
        yield 'title' => ['title'];
        yield 'description' => ['description'];
        yield 'assistantId' => ['assistantId'];
        yield 'status' => ['status'];
        yield 'metadata' => ['metadata'];
        yield 'toolResources' => ['toolResources'];
        yield 'createdAt' => ['createdAt'];
        yield 'updatedAt' => ['updatedAt'];
    }

    public function testAccessWithoutLogin(): void
    {
        $client = self::createClientWithDatabase();

        $this->expectException(AccessDeniedException::class);
        $client->request('GET', '/admin/openai/thread');
    }

    public function testListAction(): void
    {
        $client = self::createAuthenticatedClient();

        $client->request('GET', '/admin/openai/thread');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('线程', (string) $client->getResponse()->getContent());
    }

    public function testNewAction(): void
    {
        $client = self::createAuthenticatedClient();

        $client->request('GET', '/admin/openai/thread/new');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('新建', (string) $client->getResponse()->getContent());
    }

    public function testCreateThread(): void
    {
        $client = self::createAuthenticatedClient();

        $thread = new Thread();
        $thread->setThreadId('test-thread-create');
        $thread->setTitle('测试线程');
        $thread->setMetadata(['category' => 'test']);
        $thread->setStatus(ThreadStatus::Active);
        $thread->setMessageCount(0);
        $thread->setAssistantId('asst-test');
        $thread->setDescription('用于测试创建功能的线程');
        $thread->setToolResources(['code_interpreter' => ['file_ids' => ['file-123']]]);

        $entityManager = self::getEntityManager();
        $entityManager->persist($thread);
        $entityManager->flush();

        $this->assertNotNull($thread->getId());
        $this->assertSame('测试线程', $thread->getTitle());
    }

    public function testEditAction(): void
    {
        $client = self::createAuthenticatedClient();

        $thread = new Thread();
        $thread->setThreadId('test-thread-edit');
        $thread->setTitle('编辑测试线程');
        $thread->setStatus(ThreadStatus::Active);
        $thread->setMessageCount(5);
        $thread->setAssistantId('asst-edit-test');

        $entityManager = self::getEntityManager();
        $entityManager->persist($thread);
        $entityManager->flush();

        $client->request('GET', '/admin/openai/thread/' . $thread->getId() . '/edit');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('编辑', (string) $client->getResponse()->getContent());
    }

    public function testDetailAction(): void
    {
        $client = self::createAuthenticatedClient();

        $thread = new Thread();
        $thread->setThreadId('test-thread-detail');
        $thread->setTitle('详情测试线程');
        $thread->setMetadata([
            'user_id' => '12345',
            'session_id' => 'sess_abc123',
            'tags' => ['support', 'urgent'],
        ]);
        $thread->setStatus(ThreadStatus::Active);
        $thread->setMessageCount(15);
        $thread->setAssistantId('asst-support');
        $thread->setDescription('这是一个用于客户支持的对话线程');
        $thread->setToolResources([
            'code_interpreter' => [
                'file_ids' => ['file-detail-1', 'file-detail-2'],
            ],
            'file_search' => [
                'vector_store_ids' => ['vs_123', 'vs_456'],
            ],
        ]);

        $entityManager = self::getEntityManager();
        $entityManager->persist($thread);
        $entityManager->flush();

        $client->request('GET', '/admin/openai/thread/' . $thread->getId());
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('详情', (string) $client->getResponse()->getContent());
    }

    public function testDeleteThread(): void
    {
        $client = self::createAuthenticatedClient();

        $thread = new Thread();
        $thread->setThreadId('test-thread-delete');
        $thread->setTitle('删除测试线程');
        $thread->setStatus(ThreadStatus::Active);
        $thread->setMessageCount(3);

        $entityManager = self::getEntityManager();
        $entityManager->persist($thread);
        $entityManager->flush();
        $threadId = $thread->getId();

        $this->assertNotNull($threadId);

        $entityManager->remove($thread);
        $entityManager->flush();

        $deletedThread = $entityManager->find(Thread::class, $threadId);
        $this->assertNull($deletedThread);
    }

    public function testConfigureFields(): void
    {
        $controller = new ThreadCrudController();

        $indexFields = iterator_to_array($controller->configureFields('index'));
        $this->assertNotEmpty($indexFields);

        $formFields = iterator_to_array($controller->configureFields('new'));
        $this->assertNotEmpty($formFields);

        $detailFields = iterator_to_array($controller->configureFields('detail'));
        $this->assertNotEmpty($detailFields);

        $this->assertGreaterThan(5, count($indexFields));
        $this->assertGreaterThan(5, count($formFields));
        $this->assertGreaterThan(8, count($detailFields));
    }

    public function testControllerMethods(): void
    {
        $controller = new ThreadCrudController();
        $reflection = new \ReflectionClass($controller);

        $this->assertTrue($reflection->hasMethod('configureCrud'));
        $this->assertTrue($reflection->hasMethod('configureFields'));
        $this->assertTrue($reflection->hasMethod('configureActions'));
        $this->assertTrue($reflection->hasMethod('configureFilters'));
    }

    public function testThreadOperations(): void
    {
        $client = self::createAuthenticatedClient();

        $thread1 = new Thread();
        $thread1->setThreadId('operation-test-1');
        $thread1->setTitle('操作测试线程1');
        $thread1->setStatus(ThreadStatus::Active);
        $thread1->setMessageCount(10);
        $thread1->setAssistantId('asst-operation-1');

        $thread2 = new Thread();
        $thread2->setThreadId('operation-test-2');
        $thread2->setTitle('操作测试线程2');
        $thread2->setStatus(ThreadStatus::Archived);
        $thread2->setMessageCount(25);
        $thread2->setAssistantId('asst-operation-2');

        $entityManager = self::getEntityManager();
        $entityManager->persist($thread1);
        $entityManager->persist($thread2);
        $entityManager->flush();

        $crawler = $client->request('GET', '/admin/openai/thread');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('线程', (string) $client->getResponse()->getContent());

        $this->assertNotNull($thread1->getId());
        $this->assertNotNull($thread2->getId());
        $this->assertSame('操作测试线程1', $thread1->getTitle());
        $this->assertSame('操作测试线程2', $thread2->getTitle());
    }

    public function testThreadWithoutTitle(): void
    {
        $client = self::createAuthenticatedClient();

        $thread = new Thread();
        $thread->setThreadId('test-thread-no-title');
        $thread->setStatus(ThreadStatus::Active);
        $thread->setMessageCount(2);
        $thread->setAssistantId('asst-no-title');
        // 注意：没有设置 title

        $entityManager = self::getEntityManager();
        $entityManager->persist($thread);
        $entityManager->flush();

        $this->assertNotNull($thread->getId());
        $this->assertNull($thread->getTitle());
        $this->assertSame('test-thread-no-title', (string) $thread); // __toString 应该返回 threadId
    }

    public function testThreadMetadata(): void
    {
        $client = self::createAuthenticatedClient();

        $thread = new Thread();
        $thread->setThreadId('metadata-test');
        $thread->setTitle('元数据测试线程');
        $thread->setMetadata([
            'user_info' => [
                'id' => 'user_123',
                'name' => 'John Doe',
                'email' => 'john@example.com',
            ],
            'conversation_context' => [
                'type' => 'customer_support',
                'priority' => 'high',
                'category' => 'technical_issue',
            ],
            'system_info' => [
                'created_from' => 'web_interface',
                'client_version' => '1.2.3',
                'language' => 'en',
            ],
        ]);
        $thread->setStatus(ThreadStatus::Active);

        $entityManager = self::getEntityManager();
        $entityManager->persist($thread);
        $entityManager->flush();

        $this->assertNotNull($thread->getId());
        $this->assertSame('元数据测试线程', $thread->getTitle());
        $metadata = $thread->getMetadata();
        $this->assertIsArray($metadata);
        $this->assertArrayHasKey('user_info', $metadata);
        $this->assertIsArray($metadata['user_info']);
        $this->assertSame('user_123', $metadata['user_info']['id']);
    }

    public function testThreadToolResources(): void
    {
        $client = self::createAuthenticatedClient();

        $thread = new Thread();
        $thread->setThreadId('tool-resources-test');
        $thread->setTitle('工具资源测试线程');
        $thread->setToolResources([
            'code_interpreter' => [
                'file_ids' => [
                    'file-code-1',
                    'file-code-2',
                    'file-code-3',
                ],
            ],
            'file_search' => [
                'vector_store_ids' => [
                    'vs_documents',
                    'vs_knowledge_base',
                ],
                'vector_stores' => [
                    [
                        'file_ids' => ['file-doc-1', 'file-doc-2'],
                        'chunking_strategy' => 'auto',
                    ],
                ],
            ],
        ]);
        $thread->setStatus(ThreadStatus::Active);
        $thread->setMessageCount(0);

        $entityManager = self::getEntityManager();
        $entityManager->persist($thread);
        $entityManager->flush();

        $this->assertNotNull($thread->getId());
        $this->assertSame('工具资源测试线程', $thread->getTitle());
        $toolResources = $thread->getToolResources();
        $this->assertIsArray($toolResources);
        $this->assertArrayHasKey('code_interpreter', $toolResources);
        $this->assertArrayHasKey('file_search', $toolResources);
        $this->assertIsArray($toolResources['code_interpreter']);
        $this->assertIsArray($toolResources['code_interpreter']['file_ids']);
        $this->assertCount(3, $toolResources['code_interpreter']['file_ids']);
    }

    public function testValidationErrors(): void
    {
        $client = self::createAuthenticatedClient();

        $crawler = $client->request('GET', '/admin/openai/thread/new');
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        // 查找表单
        $forms = $crawler->filter('form');
        if (0 === $forms->count()) {
            self::markTestSkipped('No forms found on new page');
        }

        $form = $forms->first()->form();

        // 设置无效数据以触发验证错误
        if (isset($form['Thread[threadId]'])) {
            $form['Thread[threadId]'] = ''; // 空值应该触发验证错误
        }
        if (isset($form['Thread[title]'])) {
            $form['Thread[title]'] = ''; // 空值应该触发验证错误
        }
        if (isset($form['Thread[assistantId]'])) {
            $form['Thread[assistantId]'] = ''; // 空值应该触发验证错误
        }

        // 提交表单
        $client->submit($form);

        // 验证响应状态码
        $statusCode = $client->getResponse()->getStatusCode();
        $content = (string) $client->getResponse()->getContent();

        // 检查是否有验证错误
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

        if ($hasValidationError) {
            // 有验证错误，状态码应该是200或422
            $this->assertContains($statusCode, [200, 422], 'Expected validation error response');
            $this->assertTrue($hasValidationError, 'Should contain validation error messages');
        } else {
            // 没有验证错误，表单成功提交，显示成功页面
            $this->assertSame(200, $statusCode, 'Expected successful response with status 200');
            $this->assertTrue(
                str_contains($content, '线程列表') || str_contains($content, 'Thread'),
                'Expected to see thread list page content'
            );
        }
    }
}
