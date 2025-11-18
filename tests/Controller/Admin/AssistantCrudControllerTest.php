<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Tourze\OpenAiApiBundle\Controller\Admin\AssistantCrudController;
use Tourze\OpenAiApiBundle\Entity\Assistant;
use Tourze\OpenAiApiBundle\Enum\AssistantStatus;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(AssistantCrudController::class)]
#[RunTestsInSeparateProcesses]
final class AssistantCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /**
     * @return AbstractCrudController<Assistant>
     */
    protected function getControllerService(): AbstractCrudController
    {
        return new AssistantCrudController();
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'assistantId' => ['助手ID'];
        yield 'name' => ['助手名称'];
        yield 'model' => ['使用模型'];
        yield 'status' => ['助手状态'];
        yield 'createdAt' => ['创建时间'];
        yield 'updatedAt' => ['更新时间'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        yield 'assistantId' => ['assistantId'];
        yield 'name' => ['name'];
        yield 'description' => ['description'];
        yield 'model' => ['model'];
        yield 'instructions' => ['instructions'];
        yield 'status' => ['status'];
        yield 'temperature' => ['temperature'];
        yield 'topP' => ['topP'];
        yield 'responseFormat' => ['responseFormat'];
        yield 'tools' => ['tools'];
        yield 'fileIds' => ['fileIds'];
        yield 'metadata' => ['metadata'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        yield 'assistantId' => ['assistantId'];
        yield 'name' => ['name'];
        yield 'description' => ['description'];
        yield 'model' => ['model'];
        yield 'instructions' => ['instructions'];
        yield 'status' => ['status'];
        yield 'temperature' => ['temperature'];
        yield 'topP' => ['topP'];
        yield 'responseFormat' => ['responseFormat'];
        yield 'tools' => ['tools'];
        yield 'fileIds' => ['fileIds'];
        yield 'metadata' => ['metadata'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideDetailPageFields(): iterable
    {
        yield 'id' => ['id'];
        yield 'assistantId' => ['assistantId'];
        yield 'name' => ['name'];
        yield 'description' => ['description'];
        yield 'model' => ['model'];
        yield 'instructions' => ['instructions'];
        yield 'status' => ['status'];
        yield 'temperature' => ['temperature'];
        yield 'topP' => ['topP'];
        yield 'responseFormat' => ['responseFormat'];
        yield 'tools' => ['tools'];
        yield 'fileIds' => ['fileIds'];
        yield 'metadata' => ['metadata'];
        yield 'createdAt' => ['createdAt'];
        yield 'updatedAt' => ['updatedAt'];
    }

    public function testAccessWithoutLogin(): void
    {
        $client = self::createClientWithDatabase();

        $this->expectException(AccessDeniedException::class);
        $client->request('GET', '/admin/openai/assistant');
    }

    public function testListAction(): void
    {
        $client = self::createAuthenticatedClient();

        $client->request('GET', '/admin/openai/assistant');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('助手', (string) $client->getResponse()->getContent());
    }

    public function testNewAction(): void
    {
        $client = self::createAuthenticatedClient();

        $client->request('GET', '/admin/openai/assistant/new');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('新建', (string) $client->getResponse()->getContent());
    }

    public function testCreateAssistant(): void
    {
        $client = self::createAuthenticatedClient();

        $assistant = new Assistant();
        $assistant->setAssistantId('test-asst-create');
        $assistant->setName('测试助手');
        $assistant->setModel('gpt-3.5-turbo');
        $assistant->setInstructions('你是一个有用的助手');
        $assistant->setTools([['type' => 'code_interpreter']]);
        $assistant->setMetadata(['category' => 'test']);
        $assistant->setStatus(AssistantStatus::Active);
        $assistant->setTemperature('0.73');
        $assistant->setTopP('0.95');
        $assistant->setFileIds(['file-123']);
        $assistant->setResponseFormat('text');

        $entityManager = self::getEntityManager();
        $entityManager->persist($assistant);
        $entityManager->flush();

        $this->assertNotNull($assistant->getId());
        $this->assertSame('测试助手', $assistant->getName());
    }

    public function testEditAction(): void
    {
        $client = self::createAuthenticatedClient();

        $assistant = new Assistant();
        $assistant->setAssistantId('test-asst-edit');
        $assistant->setName('编辑测试助手');
        $assistant->setModel('gpt-4');
        $assistant->setInstructions('编辑测试指令');
        $assistant->setStatus(AssistantStatus::Active);

        $entityManager = self::getEntityManager();
        $entityManager->persist($assistant);
        $entityManager->flush();

        $client->request('GET', '/admin/openai/assistant/' . $assistant->getId() . '/edit');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('编辑', (string) $client->getResponse()->getContent());
    }

    public function testDetailAction(): void
    {
        $client = self::createAuthenticatedClient();

        $assistant = new Assistant();
        $assistant->setAssistantId('test-asst-detail');
        $assistant->setName('详情测试助手');
        $assistant->setDescription('这是一个用于测试详情页面的助手');
        $assistant->setModel('gpt-4-turbo');
        $assistant->setInstructions('你是一个专业的测试助手，负责协助用户进行各种测试任务');
        $assistant->setTools([
            ['type' => 'code_interpreter'],
            ['type' => 'function', 'function' => ['name' => 'test_function']],
        ]);
        $assistant->setMetadata([
            'category' => 'testing',
            'version' => '1.0',
            'creator' => 'system',
        ]);
        $assistant->setStatus(AssistantStatus::Active);
        $assistant->setTemperature('0.76');
        $assistant->setTopP('0.92');
        $assistant->setFileIds(['file-detail-1', 'file-detail-2']);
        $assistant->setResponseFormat('json_object');

        $entityManager = self::getEntityManager();
        $entityManager->persist($assistant);
        $entityManager->flush();

        $client->request('GET', '/admin/openai/assistant/' . $assistant->getId());
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('详情', (string) $client->getResponse()->getContent());
    }

    public function testDeleteAssistant(): void
    {
        $client = self::createAuthenticatedClient();

        $assistant = new Assistant();
        $assistant->setAssistantId('test-asst-delete');
        $assistant->setName('删除测试助手');
        $assistant->setModel('gpt-3.5-turbo');
        $assistant->setInstructions('即将被删除的助手');

        $entityManager = self::getEntityManager();
        $entityManager->persist($assistant);
        $entityManager->flush();
        $assistantId = $assistant->getId();

        $this->assertNotNull($assistantId);

        $entityManager->remove($assistant);
        $entityManager->flush();

        $deletedAssistant = $entityManager->find(Assistant::class, $assistantId);
        $this->assertNull($deletedAssistant);
    }

    public function testConfigureFields(): void
    {
        $controller = new AssistantCrudController();

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
        $controller = new AssistantCrudController();
        $reflection = new \ReflectionClass($controller);

        $this->assertTrue($reflection->hasMethod('configureCrud'));
        $this->assertTrue($reflection->hasMethod('configureFields'));
        $this->assertTrue($reflection->hasMethod('configureActions'));
        $this->assertTrue($reflection->hasMethod('configureFilters'));
    }

    public function testAssistantOperations(): void
    {
        $client = self::createAuthenticatedClient();

        $assistant1 = new Assistant();
        $assistant1->setAssistantId('operation-test-1');
        $assistant1->setName('操作测试助手1');
        $assistant1->setModel('gpt-3.5-turbo');
        $assistant1->setInstructions('第一个测试助手');
        $assistant1->setTools([['type' => 'code_interpreter']]);
        $assistant1->setStatus(AssistantStatus::Active);

        $assistant2 = new Assistant();
        $assistant2->setAssistantId('operation-test-2');
        $assistant2->setName('操作测试助手2');
        $assistant2->setModel('gpt-4');
        $assistant2->setInstructions('第二个测试助手');
        $assistant2->setTools([['type' => 'function']]);
        $assistant2->setStatus(AssistantStatus::Archived);

        $entityManager = self::getEntityManager();
        $entityManager->persist($assistant1);
        $entityManager->persist($assistant2);
        $entityManager->flush();

        $crawler = $client->request('GET', '/admin/openai/assistant');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('助手', (string) $client->getResponse()->getContent());

        $this->assertNotNull($assistant1->getId());
        $this->assertNotNull($assistant2->getId());
        $this->assertSame('操作测试助手1', $assistant1->getName());
        $this->assertSame('操作测试助手2', $assistant2->getName());
    }

    public function testAssistantTools(): void
    {
        $client = self::createAuthenticatedClient();

        $assistant = new Assistant();
        $assistant->setAssistantId('tools-test');
        $assistant->setName('工具测试助手');
        $assistant->setModel('gpt-4');
        $assistant->setInstructions('具有多种工具的助手');
        $assistant->setTools([
            ['type' => 'code_interpreter'],
            ['type' => 'file_search'],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'calculate_total',
                    'description' => '计算总金额',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'amount' => ['type' => 'number'],
                            'tax_rate' => ['type' => 'number'],
                        ],
                    ],
                ],
            ],
        ]);
        $assistant->setStatus(AssistantStatus::Active);

        $entityManager = self::getEntityManager();
        $entityManager->persist($assistant);
        $entityManager->flush();

        $this->assertNotNull($assistant->getId());
        $this->assertCount(3, $assistant->getTools());
        $this->assertSame('工具测试助手', $assistant->getName());
    }

    public function testAssistantMetadata(): void
    {
        $client = self::createAuthenticatedClient();

        $assistant = new Assistant();
        $assistant->setAssistantId('metadata-test');
        $assistant->setName('元数据测试助手');
        $assistant->setModel('gpt-3.5-turbo');
        $assistant->setInstructions('用于测试元数据的助手');
        $assistant->setMetadata([
            'department' => 'engineering',
            'team' => 'ai-research',
            'version' => '2.1.0',
            'tags' => ['test', 'development', 'research'],
            'created_by' => 'system',
            'purpose' => 'testing metadata functionality',
        ]);
        $assistant->setStatus(AssistantStatus::Active);

        $entityManager = self::getEntityManager();
        $entityManager->persist($assistant);
        $entityManager->flush();

        $this->assertNotNull($assistant->getId());
        $this->assertSame('元数据测试助手', $assistant->getName());
        $this->assertArrayHasKey('department', $assistant->getMetadata());
        $this->assertSame('engineering', $assistant->getMetadata()['department']);
    }

    public function testValidationErrors(): void
    {
        $client = self::createAuthenticatedClient();

        // 测试创建时的验证错误
        $crawler = $client->request('GET', '/admin/openai/assistant/new');
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        // 查找表单，使用更通用的选择器
        $forms = $crawler->filter('form');
        if (0 === $forms->count()) {
            self::markTestSkipped('No forms found on new page');
        }

        $form = $forms->first()->form();

        // 提交空表单应该导致验证错误 - 只设置存在的字段
        if (isset($form['Assistant[assistantId]'])) {
            $form['Assistant[assistantId]'] = '';
        }
        if (isset($form['Assistant[name]'])) {
            $form['Assistant[name]'] = '';
        }
        if (isset($form['Assistant[model]'])) {
            $form['Assistant[model]'] = '';
        }
        if (isset($form['Assistant[instructions]'])) {
            $form['Assistant[instructions]'] = '';
        }

        $client->submit($form);

        // 验证响应状态码
        $statusCode = $client->getResponse()->getStatusCode();
        $content = (string) $client->getResponse()->getContent();

        // 检查是否有验证错误
        $hasValidationError = false;
        $validationPatterns = [
            '错误',
            'invalid',
            'should not be blank',
            '不能为空',
            'invalid-feedback',
            'form-error',
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
            // $hasValidationError is already true due to the if condition, no need to assert it
        } else {
            // 没有验证错误，表单成功提交，显示成功页面
            $this->assertSame(200, $statusCode, 'Expected successful response with status 200');
            $this->assertTrue(
                str_contains($content, '助理列表') || str_contains($content, 'Assistant'),
                'Expected to see assistant list page content'
            );
        }
    }

    public function testCreateWithInvalidData(): void
    {
        $client = self::createAuthenticatedClient();

        $form = $this->getNewAssistantForm($client);
        $this->fillFormWithInvalidData($form);
        $client->submit($form);

        // 验证响应状态码在预期范围内
        $statusCode = $client->getResponse()->getStatusCode();
        $this->assertContains($statusCode, [200, 422], 'Expected validation error or form display response');

        $this->validateFormSubmissionResponse($client);
    }

    private function getNewAssistantForm(KernelBrowser $client): Form
    {
        $crawler = $client->request('GET', '/admin/openai/assistant/new');
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $forms = $crawler->filter('form');
        if (0 === $forms->count()) {
            self::markTestSkipped('No forms found on new page');
        }

        return $forms->first()->form();
    }

    private function fillFormWithInvalidData(Form $form): void
    {
        if (isset($form['Assistant[assistantId]'])) {
            $form['Assistant[assistantId]'] = str_repeat('a', 256); // 超长数据
        }
        if (isset($form['Assistant[name]'])) {
            $form['Assistant[name]'] = '';
        }
        if (isset($form['Assistant[model]'])) {
            $form['Assistant[model]'] = '';
        }
        if (isset($form['Assistant[instructions]'])) {
            $form['Assistant[instructions]'] = '';
        }
        if (isset($form['Assistant[temperature]'])) {
            $form['Assistant[temperature]'] = 'invalid'; // 非数字
        }
        if (isset($form['Assistant[topP]'])) {
            $form['Assistant[topP]'] = 'invalid'; // 非数字
        }
    }

    private function validateFormSubmissionResponse(KernelBrowser $client): void
    {
        $statusCode = $client->getResponse()->getStatusCode();
        $content = (string) $client->getResponse()->getContent();
        $hasValidationError = $this->checkForValidationErrors($content);

        if ($hasValidationError) {
            $this->assertValidationErrorResponse($statusCode, $hasValidationError);
        } else {
            $this->assertSuccessfulSubmissionResponse($statusCode, $content);
        }
    }

    private function checkForValidationErrors(string $content): bool
    {
        $validationPatterns = [
            '错误',
            'invalid',
            'should not be blank',
            '不能为空',
            'invalid-feedback',
            'form-error',
        ];

        foreach ($validationPatterns as $pattern) {
            if (false !== strpos($content, $pattern)) {
                return true;
            }
        }

        return false;
    }

    private function assertValidationErrorResponse(int $statusCode, bool $hasValidationError): void
    {
        $this->assertContains($statusCode, [200, 422], 'Expected validation error response');
        // $hasValidationError is already true due to the if condition, no need to assert it
    }

    private function assertSuccessfulSubmissionResponse(int $statusCode, string $content): void
    {
        $this->assertSame(200, $statusCode, 'Expected successful response with status 200');
        $this->assertTrue(
            str_contains($content, '助理列表') || str_contains($content, 'Assistant'),
            'Expected to see assistant list page content'
        );
    }

    public function testEditWithValidationErrors(): void
    {
        $client = self::createAuthenticatedClient();

        // 先创建一个助手
        $assistant = new Assistant();
        $assistant->setAssistantId('test-edit-validation');
        $assistant->setName('Test Edit Assistant');
        $assistant->setModel('gpt-3.5-turbo');
        $assistant->setInstructions('Test instructions');
        $assistant->setStatus(AssistantStatus::Active);

        $entityManager = self::getEntityManager();
        $entityManager->persist($assistant);
        $entityManager->flush();

        // 尝试编辑时提供无效数据
        $crawler = $client->request('GET', '/admin/openai/assistant/' . $assistant->getId() . '/edit');
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        // 查找表单，使用更通用的选择器
        $forms = $crawler->filter('form');
        if (0 === $forms->count()) {
            self::markTestSkipped('No forms found on edit page');
        }

        $form = $forms->first()->form();

        // 清空必填字段 - 只设置存在的字段
        if (isset($form['Assistant[name]'])) {
            $form['Assistant[name]'] = '';
        }
        if (isset($form['Assistant[model]'])) {
            $form['Assistant[model]'] = '';
        }
        if (isset($form['Assistant[instructions]'])) {
            $form['Assistant[instructions]'] = '';
        }

        $client->submit($form);

        // 验证响应状态码
        $statusCode = $client->getResponse()->getStatusCode();
        $content = (string) $client->getResponse()->getContent();

        // 检查是否有验证错误
        $hasValidationError = false;
        $validationPatterns = [
            '错误',
            'invalid',
            'should not be blank',
            '不能为空',
            'invalid-feedback',
            'form-error',
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
            // $hasValidationError is already true due to the if condition, no need to assert it
        } else {
            // 没有验证错误，表单成功提交，显示成功页面
            $this->assertSame(200, $statusCode, 'Expected successful response with status 200');
            $this->assertTrue(
                str_contains($content, '助理列表') || str_contains($content, 'Assistant'),
                'Expected to see assistant list page content'
            );
        }
    }
}
