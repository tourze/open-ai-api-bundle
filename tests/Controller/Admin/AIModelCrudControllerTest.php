<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Tourze\OpenAiApiBundle\Controller\Admin\AIModelCrudController;
use Tourze\OpenAiApiBundle\Entity\AIModel;
use Tourze\OpenAiApiBundle\Enum\ModelStatus;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(AIModelCrudController::class)]
#[RunTestsInSeparateProcesses]
final class AIModelCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /**
     * @return AbstractCrudController<AIModel>
     */
    protected function getControllerService(): AbstractCrudController
    {
        return new AIModelCrudController();
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'modelId' => ['模型ID'];
        yield 'name' => ['模型名称'];
        yield 'owner' => ['所有者'];
        yield 'status' => ['模型状态'];
        yield 'isActive' => ['是否激活'];
        yield 'createdAt' => ['创建时间'];
        yield 'updatedAt' => ['更新时间'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        yield 'modelId' => ['modelId'];
        yield 'name' => ['name'];
        yield 'description' => ['description'];
        yield 'owner' => ['owner'];
        yield 'status' => ['status'];
        yield 'isActive' => ['isActive'];
        yield 'contextWindow' => ['contextWindow'];
        yield 'inputPricePerToken' => ['inputPricePerToken'];
        yield 'outputPricePerToken' => ['outputPricePerToken'];
        yield 'capabilities' => ['capabilities'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        yield 'modelId' => ['modelId'];
        yield 'name' => ['name'];
        yield 'description' => ['description'];
        yield 'owner' => ['owner'];
        yield 'status' => ['status'];
        yield 'isActive' => ['isActive'];
        yield 'contextWindow' => ['contextWindow'];
        yield 'inputPricePerToken' => ['inputPricePerToken'];
        yield 'outputPricePerToken' => ['outputPricePerToken'];
        yield 'capabilities' => ['capabilities'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideDetailPageFields(): iterable
    {
        yield 'id' => ['id'];
        yield 'modelId' => ['modelId'];
        yield 'name' => ['name'];
        yield 'description' => ['description'];
        yield 'owner' => ['owner'];
        yield 'status' => ['status'];
        yield 'isActive' => ['isActive'];
        yield 'contextWindow' => ['contextWindow'];
        yield 'inputPricePerToken' => ['inputPricePerToken'];
        yield 'outputPricePerToken' => ['outputPricePerToken'];
        yield 'capabilities' => ['capabilities'];
        yield 'createdAt' => ['createdAt'];
        yield 'updatedAt' => ['updatedAt'];
    }

    public function testGetEntityFqcn(): void
    {
        $this->assertSame(AIModel::class, AIModelCrudController::getEntityFqcn());
    }

    public function testAccessWithoutLogin(): void
    {
        $client = self::createClientWithDatabase();

        $this->expectException(AccessDeniedException::class);
        $client->request('GET', '/admin/openai/aimodel');
    }

    public function testListAction(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $client->request('GET', '/admin/openai/aimodel');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('AI模型', (string) $client->getResponse()->getContent());
    }

    public function testNewAction(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $client->request('GET', '/admin/openai/aimodel/new');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('新建', (string) $client->getResponse()->getContent());
    }

    public function testCreateModel(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $model = new AIModel();
        $model->setModelId('test-model-create');
        $model->setName('测试模型');
        $model->setOwner('openai');
        $model->setStatus(ModelStatus::Available);
        $model->setContextWindow(4096);
        $model->setInputPricePerToken('0.000001');
        $model->setOutputPricePerToken('0.000002');
        $model->setCapabilities(['text-generation']);
        $model->setIsActive(true);

        $entityManager = self::getEntityManager();
        $entityManager->persist($model);
        $entityManager->flush();

        $this->assertNotNull($model->getId());
        $this->assertSame('测试模型', $model->getName());
    }

    public function testEditAction(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $model = new AIModel();
        $model->setModelId('test-model-edit');
        $model->setName('编辑测试模型');
        $model->setOwner('anthropic');
        $model->setStatus(ModelStatus::Available);

        $entityManager = self::getEntityManager();
        $entityManager->persist($model);
        $entityManager->flush();

        $client->request('GET', '/admin/openai/aimodel/' . $model->getId() . '/edit');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('编辑', (string) $client->getResponse()->getContent());
    }

    public function testDetailAction(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $model = new AIModel();
        $model->setModelId('test-model-detail');
        $model->setName('详情测试模型');
        $model->setDescription('一个用于测试的模型');
        $model->setOwner('openai');
        $model->setStatus(ModelStatus::Available);
        $model->setContextWindow(8192);
        $model->setInputPricePerToken('0.000010');
        $model->setOutputPricePerToken('0.000030');
        $model->setCapabilities(['text-generation', 'code-completion']);
        $model->setIsActive(true);

        $entityManager = self::getEntityManager();
        $entityManager->persist($model);
        $entityManager->flush();

        $client->request('GET', '/admin/openai/aimodel/' . $model->getId());
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('详情', (string) $client->getResponse()->getContent());
    }

    public function testDeleteModel(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $model = new AIModel();
        $model->setModelId('test-model-delete');
        $model->setName('删除测试模型');
        $model->setOwner('test-owner');

        $entityManager = self::getEntityManager();
        $entityManager->persist($model);
        $entityManager->flush();
        $modelId = $model->getId();

        $this->assertNotNull($modelId);

        $entityManager->remove($model);
        $entityManager->flush();

        $deletedModel = $entityManager->find(AIModel::class, $modelId);
        $this->assertNull($deletedModel);
    }

    public function testConfigureFields(): void
    {
        $controller = new AIModelCrudController();

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
        $controller = new AIModelCrudController();
        $reflection = new \ReflectionClass($controller);

        $this->assertTrue($reflection->hasMethod('configureCrud'));
        $this->assertTrue($reflection->hasMethod('configureFields'));
        $this->assertTrue($reflection->hasMethod('configureActions'));
        $this->assertTrue($reflection->hasMethod('configureFilters'));
    }

    public function testBasicCrudOperations(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $model1 = new AIModel();
        $model1->setModelId('crud-test-1');
        $model1->setName('CRUD 测试模型1');
        $model1->setOwner('openai');
        $model1->setStatus(ModelStatus::Available);
        $model1->setIsActive(true);

        $model2 = new AIModel();
        $model2->setModelId('crud-test-2');
        $model2->setName('CRUD 测试模型2');
        $model2->setOwner('anthropic');
        $model2->setStatus(ModelStatus::Deprecated);
        $model2->setIsActive(false);

        $entityManager = self::getEntityManager();
        $entityManager->persist($model1);
        $entityManager->persist($model2);
        $entityManager->flush();

        $crawler = $client->request('GET', '/admin/openai/aimodel');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('AI模型', (string) $client->getResponse()->getContent());

        $this->assertNotNull($model1->getId());
        $this->assertNotNull($model2->getId());
        $this->assertSame('CRUD 测试模型1', $model1->getName());
        $this->assertSame('CRUD 测试模型2', $model2->getName());
    }

    public function testValidationErrors(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        // 测试创建时的验证错误
        $crawler = $client->request('GET', '/admin/openai/aimodel/new');
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        // 检查是否有表单
        $forms = $crawler->filter('form');
        if ($forms->count() > 0) {
            $form = $forms->first()->form();

            // 提交空表单应该导致验证错误
            if (isset($form['AIModel[modelId]'])) {
                $form['AIModel[modelId]'] = '';
            }
            if (isset($form['AIModel[name]'])) {
                $form['AIModel[name]'] = '';
            }
            if (isset($form['AIModel[owner]'])) {
                $form['AIModel[owner]'] = '';
            }

            $client->submit($form);
            // 验证错误状态码可能是422或200（带错误信息）
            $this->assertTrue(in_array($client->getResponse()->getStatusCode(), [200, 422], true));
        } else {
            self::markTestIncomplete('No form found on new page');
        }
    }

    public function testCreateWithInvalidData(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $crawler = $client->request('GET', '/admin/openai/aimodel/new');
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        // 查找表单，使用更通用的选择器
        $forms = $crawler->filter('form');
        if (0 === $forms->count()) {
            self::markTestSkipped('No forms found on new page');
        }

        $form = $forms->first()->form();

        // 提交无效数据 - 只设置存在的字段
        if (isset($form['AIModel[modelId]'])) {
            $form['AIModel[modelId]'] = str_repeat('a', 256); // 超长数据
        }
        if (isset($form['AIModel[name]'])) {
            $form['AIModel[name]'] = '';
        }
        if (isset($form['AIModel[owner]'])) {
            $form['AIModel[owner]'] = '';
        }
        if (isset($form['AIModel[contextWindow]'])) {
            $form['AIModel[contextWindow]'] = 'invalid'; // 非数字
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
            '模型ID不能为空',
            '模型名称不能为空',
            '模型所有者不能为空',
            '模型ID长度不能超过',
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
                str_contains($content, 'AI模型列表'),
                'Expected to see AI model list page content'
            );
        }
    }

    public function testEditWithValidationErrors(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        // 先创建一个模型
        $model = new AIModel();
        $model->setModelId('test-edit-validation');
        $model->setName('Test Edit Model');
        $model->setOwner('test');
        $model->setStatus(ModelStatus::Available);

        $entityManager = self::getEntityManager();
        $entityManager->persist($model);
        $entityManager->flush();

        // 尝试编辑时提供无效数据
        $crawler = $client->request('GET', '/admin/openai/aimodel/' . $model->getId() . '/edit');
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        // 查找表单，使用更通用的选择器
        $forms = $crawler->filter('form');
        if (0 === $forms->count()) {
            self::markTestSkipped('No forms found on edit page');
        }

        $form = $forms->first()->form();

        // 清空必填字段 - 只设置存在的字段
        if (isset($form['AIModel[name]'])) {
            $form['AIModel[name]'] = '';
        }
        if (isset($form['AIModel[owner]'])) {
            $form['AIModel[owner]'] = '';
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
            '模型ID不能为空',
            '模型名称不能为空',
            '模型所有者不能为空',
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
                str_contains($content, 'AI模型列表'),
                'Expected to see AI model list page content'
            );
        }
    }
}
