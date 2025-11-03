<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\OpenAiApiBundle\Entity\AIModel;
use Tourze\OpenAiApiBundle\Enum\ModelStatus;
use Tourze\OpenAiApiBundle\Repository\AIModelRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @template TEntity of AIModel
 * @internal
 */
#[CoversClass(AIModelRepository::class)]
#[RunTestsInSeparateProcesses]
final class AIModelRepositoryTest extends AbstractRepositoryTestCase
{
    public function testSaveShouldPersistEntity(): void
    {
        $repository = self::getService(AIModelRepository::class);
        $model = $this->createTestModel();

        $repository->save($model);

        $this->assertNotNull($model->getId());

        $foundModel = $repository->find($model->getId());
        $this->assertInstanceOf(AIModel::class, $foundModel);
        $this->assertSame($model->getName(), $foundModel->getName());
    }

    public function testRemoveShouldDeleteEntity(): void
    {
        $repository = self::getService(AIModelRepository::class);
        $model = $this->createTestModel();
        $repository->save($model);
        $modelId = $model->getId();

        $repository->remove($model);

        $foundModel = $repository->find($modelId);
        $this->assertNull($foundModel);
    }

    public function testFindByStatus(): void
    {
        $repository = self::getService(AIModelRepository::class);

        $availableModel = $this->createTestModel();
        $availableModel->setName('Available Model');
        $availableModel->setStatus(ModelStatus::Available);
        $deprecatedModel = $this->createTestModel();
        $deprecatedModel->setModelId('deprecated-model');
        $deprecatedModel->setName('Deprecated Model');
        $deprecatedModel->setStatus(ModelStatus::Deprecated);

        $repository->save($availableModel);
        $repository->save($deprecatedModel);

        $availableResults = $repository->findByStatus(ModelStatus::Available);
        $this->assertIsArray($availableResults);

        $foundAvailable = false;
        foreach ($availableResults as $result) {
            $this->assertInstanceOf(AIModel::class, $result);
            $this->assertSame(ModelStatus::Available, $result->getStatus());
            if ($result->getId() === $availableModel->getId()) {
                $foundAvailable = true;
            }
        }
        $this->assertTrue($foundAvailable, 'Available model should be found');

        $deprecatedResults = $repository->findByStatus(ModelStatus::Deprecated);
        $this->assertIsArray($deprecatedResults);

        $foundDeprecated = false;
        foreach ($deprecatedResults as $result) {
            $this->assertInstanceOf(AIModel::class, $result);
            $this->assertSame(ModelStatus::Deprecated, $result->getStatus());
            if ($result->getId() === $deprecatedModel->getId()) {
                $foundDeprecated = true;
            }
        }
        $this->assertTrue($foundDeprecated, 'Deprecated model should be found');
    }

    public function testFindActiveAvailableModels(): void
    {
        $repository = self::getService(AIModelRepository::class);

        $activeAvailableModel = $this->createTestModel();
        $activeAvailableModel->setModelId('active-available');
        $activeAvailableModel->setName('Active Available Model');
        $activeAvailableModel->setStatus(ModelStatus::Available);
        $activeAvailableModel->setIsActive(true);
        $inactiveAvailableModel = $this->createTestModel();
        $inactiveAvailableModel->setModelId('inactive-available');
        $inactiveAvailableModel->setName('Inactive Available Model');
        $inactiveAvailableModel->setStatus(ModelStatus::Available);
        $inactiveAvailableModel->setIsActive(false);

        $repository->save($activeAvailableModel);
        $repository->save($inactiveAvailableModel);

        $results = $repository->findActiveAvailableModels();
        $this->assertIsArray($results);

        $foundActive = false;
        $foundInactive = false;
        foreach ($results as $result) {
            $this->assertInstanceOf(AIModel::class, $result);
            $this->assertSame(ModelStatus::Available, $result->getStatus());
            $this->assertTrue($result->isActive());
            if ($result->getId() === $activeAvailableModel->getId()) {
                $foundActive = true;
            }
            if ($result->getId() === $inactiveAvailableModel->getId()) {
                $foundInactive = true;
            }
        }

        $this->assertTrue($foundActive, 'Active available model should be found');
        $this->assertFalse($foundInactive, 'Inactive available model should not be found');
    }

    public function testFindByOwner(): void
    {
        $repository = self::getService(AIModelRepository::class);

        $openaiModel = $this->createTestModel();
        $openaiModel->setModelId('openai-model');
        $openaiModel->setOwner('openai');
        $anthropicModel = $this->createTestModel();
        $anthropicModel->setModelId('anthropic-model');
        $anthropicModel->setOwner('anthropic');

        $repository->save($openaiModel);
        $repository->save($anthropicModel);

        $openaiResults = $repository->findByOwner('openai');
        $this->assertIsArray($openaiResults);

        $foundOpenai = false;
        foreach ($openaiResults as $result) {
            $this->assertInstanceOf(AIModel::class, $result);
            $this->assertSame('openai', $result->getOwner());
            if ($result->getId() === $openaiModel->getId()) {
                $foundOpenai = true;
            }
        }
        $this->assertTrue($foundOpenai, 'OpenAI model should be found');

        $anthropicResults = $repository->findByOwner('anthropic');
        $this->assertIsArray($anthropicResults);

        $foundAnthropic = false;
        foreach ($anthropicResults as $result) {
            $this->assertInstanceOf(AIModel::class, $result);
            $this->assertSame('anthropic', $result->getOwner());
            if ($result->getId() === $anthropicModel->getId()) {
                $foundAnthropic = true;
            }
        }
        $this->assertTrue($foundAnthropic, 'Anthropic model should be found');
    }

    public function testFindByModelId(): void
    {
        $repository = self::getService(AIModelRepository::class);

        $model = $this->createTestModel();
        $model->setModelId('unique-model-id');
        $model->setName('Unique Model');

        $repository->save($model);

        $foundModel = $repository->findByModelId('unique-model-id');
        $this->assertInstanceOf(AIModel::class, $foundModel);
        $this->assertSame('unique-model-id', $foundModel->getModelId());
        $this->assertSame($model->getId(), $foundModel->getId());

        $notFoundModel = $repository->findByModelId('non-existent-model');
        $this->assertNull($notFoundModel);
    }

    public function testFindByCapability(): void
    {
        $repository = self::getService(AIModelRepository::class);

        $textModel = $this->createTestModel();
        $textModel->setModelId('text-model');
        $textModel->setCapabilities(['text-generation', 'completion']);
        $textModel->setIsActive(true);
        $codeModel = $this->createTestModel();
        $codeModel->setModelId('code-model');
        $codeModel->setCapabilities(['code-completion', 'text-generation']);
        $codeModel->setIsActive(true);
        $inactiveModel = $this->createTestModel();
        $inactiveModel->setModelId('inactive-model');
        $inactiveModel->setCapabilities(['text-generation']);
        $inactiveModel->setIsActive(false);

        $repository->save($textModel);
        $repository->save($codeModel);
        $repository->save($inactiveModel);

        $textResults = $repository->findByCapability('text-generation');
        $this->assertIsArray($textResults);

        $foundText = false;
        $foundCode = false;
        $foundInactive = false;
        foreach ($textResults as $result) {
            $this->assertInstanceOf(AIModel::class, $result);
            $this->assertTrue($result->isActive());
            $this->assertContains('text-generation', $result->getCapabilities());

            if ($result->getId() === $textModel->getId()) {
                $foundText = true;
            }
            if ($result->getId() === $codeModel->getId()) {
                $foundCode = true;
            }
            if ($result->getId() === $inactiveModel->getId()) {
                $foundInactive = true;
            }
        }

        $this->assertTrue($foundText, 'Text model should be found');
        $this->assertTrue($foundCode, 'Code model should be found');
        $this->assertFalse($foundInactive, 'Inactive model should not be found');
    }

    public function testFindByContextWindowRange(): void
    {
        $repository = self::getService(AIModelRepository::class);

        $smallModel = $this->createTestModel();
        $smallModel->setModelId('small-model');
        $smallModel->setContextWindow(2048);
        $smallModel->setIsActive(true);
        $mediumModel = $this->createTestModel();
        $mediumModel->setModelId('medium-model');
        $mediumModel->setContextWindow(8192);
        $mediumModel->setIsActive(true);
        $largeModel = $this->createTestModel();
        $largeModel->setModelId('large-model');
        $largeModel->setContextWindow(32768);
        $largeModel->setIsActive(true);
        $inactiveModel = $this->createTestModel();
        $inactiveModel->setModelId('inactive-window-model');
        $inactiveModel->setContextWindow(4096);
        $inactiveModel->setIsActive(false);

        $repository->save($smallModel);
        $repository->save($mediumModel);
        $repository->save($largeModel);
        $repository->save($inactiveModel);

        // 测试最小窗口限制
        $minResults = $repository->findByContextWindowRange(4000, null);
        $this->assertIsArray($minResults);

        foreach ($minResults as $result) {
            $this->assertInstanceOf(AIModel::class, $result);
            $this->assertTrue($result->isActive());
            $this->assertGreaterThanOrEqual(4000, $result->getContextWindow());
        }

        // 测试最大窗口限制
        $maxResults = $repository->findByContextWindowRange(null, 10000);
        $this->assertIsArray($maxResults);

        foreach ($maxResults as $result) {
            $this->assertInstanceOf(AIModel::class, $result);
            $this->assertTrue($result->isActive());
            $this->assertLessThanOrEqual(10000, $result->getContextWindow());
        }

        // 测试范围限制
        $rangeResults = $repository->findByContextWindowRange(3000, 10000);
        $this->assertIsArray($rangeResults);

        foreach ($rangeResults as $result) {
            $this->assertInstanceOf(AIModel::class, $result);
            $this->assertTrue($result->isActive());
            $this->assertGreaterThanOrEqual(3000, $result->getContextWindow());
            $this->assertLessThanOrEqual(10000, $result->getContextWindow());
        }
    }

    public function testFindByPriceRange(): void
    {
        $repository = self::getService(AIModelRepository::class);

        $cheapModel = $this->createTestModel();
        $cheapModel->setModelId('cheap-model');
        $cheapModel->setInputPricePerToken('0.000001');
        $cheapModel->setOutputPricePerToken('0.000002');
        $cheapModel->setIsActive(true);
        $expensiveModel = $this->createTestModel();
        $expensiveModel->setModelId('expensive-model');
        $expensiveModel->setInputPricePerToken('0.000010');
        $expensiveModel->setOutputPricePerToken('0.000020');
        $expensiveModel->setIsActive(true);

        $repository->save($cheapModel);
        $repository->save($expensiveModel);

        $inputPriceResults = $repository->findByPriceRange('0.000005', null);
        $this->assertIsArray($inputPriceResults);

        foreach ($inputPriceResults as $result) {
            $this->assertInstanceOf(AIModel::class, $result);
            $this->assertTrue($result->isActive());
            $this->assertNotNull($result->getInputPricePerToken());
            $this->assertLessThanOrEqual('0.000005', $result->getInputPricePerToken());
        }

        $outputPriceResults = $repository->findByPriceRange(null, '0.000010');
        $this->assertIsArray($outputPriceResults);

        foreach ($outputPriceResults as $result) {
            $this->assertInstanceOf(AIModel::class, $result);
            $this->assertTrue($result->isActive());
            $this->assertNotNull($result->getOutputPricePerToken());
            $this->assertLessThanOrEqual('0.000010', $result->getOutputPricePerToken());
        }
    }

    public function testGetModelStatistics(): void
    {
        $repository = self::getService(AIModelRepository::class);

        $model1 = $this->createTestModel();
        $model1->setModelId('stats-model-1');
        $model1->setOwner('openai');
        $model1->setStatus(ModelStatus::Available);
        $model1->setIsActive(true);
        $model2 = $this->createTestModel();
        $model2->setModelId('stats-model-2');
        $model2->setOwner('openai');
        $model2->setStatus(ModelStatus::Deprecated);
        $model2->setIsActive(false);

        $repository->save($model1);
        $repository->save($model2);

        $stats = $repository->getModelStatistics();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total', $stats);
        $this->assertArrayHasKey('active', $stats);
        $this->assertArrayHasKey('by_status', $stats);
        $this->assertArrayHasKey('by_owner', $stats);

        $this->assertIsInt($stats['total']);
        $this->assertIsInt($stats['active']);
        $this->assertIsArray($stats['by_status']);
        $this->assertIsArray($stats['by_owner']);

        $this->assertGreaterThanOrEqual(2, $stats['total']);
        $this->assertGreaterThanOrEqual(1, $stats['active']);
    }

    public function testSearch(): void
    {
        $repository = self::getService(AIModelRepository::class);

        $model1 = $this->createTestModel();
        $model1->setModelId('search-model-1');
        $model1->setName('GPT Search Model');
        $model1->setDescription('Advanced search capabilities');
        $model1->setIsActive(true);
        $model2 = $this->createTestModel();
        $model2->setModelId('search-model-2');
        $model2->setName('Claude Model');
        $model2->setDescription('GPT alternative with reasoning');
        $model2->setIsActive(true);
        $inactiveModel = $this->createTestModel();
        $inactiveModel->setModelId('inactive-search-model');
        $inactiveModel->setName('GPT Inactive');
        $inactiveModel->setIsActive(false);

        $repository->save($model1);
        $repository->save($model2);
        $repository->save($inactiveModel);

        $results = $repository->search('GPT');
        $this->assertIsArray($results);

        $foundModel1 = false;
        $foundModel2 = false;
        $foundInactive = false;

        foreach ($results as $result) {
            $this->assertInstanceOf(AIModel::class, $result);
            $this->assertTrue($result->isActive());
            $this->assertTrue(
                str_contains($result->getName(), 'GPT')
                || str_contains($result->getDescription() ?? '', 'GPT')
            );

            if ($result->getId() === $model1->getId()) {
                $foundModel1 = true;
            }
            if ($result->getId() === $model2->getId()) {
                $foundModel2 = true;
            }
            if ($result->getId() === $inactiveModel->getId()) {
                $foundInactive = true;
            }
        }

        $this->assertTrue($foundModel1, 'Model 1 should be found by name');
        $this->assertTrue($foundModel2, 'Model 2 should be found by description');
        $this->assertFalse($foundInactive, 'Inactive model should not be found');
    }

    public function testFindRecentlyUpdated(): void
    {
        $repository = self::getService(AIModelRepository::class);

        // 创建第一个模型
        $model1 = $this->createTestModel();
        $model1->setModelId('recent-model-1');
        $model1->setName('Recent Model 1');
        $repository->save($model1);

        // 短暂等待以确保时间戳不同
        usleep(1000); // 1毫秒

        // 创建第二个模型 (稍后创建，所以updatedAt应该更晚)
        $model2 = $this->createTestModel();
        $model2->setModelId('recent-model-2');
        $model2->setName('Recent Model 2');
        $repository->save($model2);

        // 再次更新第一个模型以改变其updatedAt时间戳
        usleep(1000); // 1毫秒
        $model1->setName('Recent Model 1 Updated');
        $repository->save($model1);

        $results = $repository->findRecentlyUpdated(10);
        $this->assertIsArray($results);
        $this->assertLessThanOrEqual(10, count($results));

        foreach ($results as $result) {
            $this->assertInstanceOf(AIModel::class, $result);
        }

        // 基本验证：确保我们的测试模型在结果中
        $modelIds = array_map(fn ($model) => $model->getModelId(), $results);
        $this->assertContains('recent-model-1', $modelIds, 'Model 1 should be in results');
        $this->assertContains('recent-model-2', $modelIds, 'Model 2 should be in results');
    }

    protected function onSetUp(): void
    {
    }

    /**
     * @return ServiceEntityRepository<AIModel>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return self::getService(AIModelRepository::class);
    }

    protected function createNewEntity(): object
    {
        return $this->createTestModel();
    }

    private function createTestModel(): AIModel
    {
        $model = new AIModel();
        $model->setModelId('test_model_' . uniqid());
        $model->setName('Test Model ' . uniqid());
        $model->setOwner('test-owner');
        $model->setStatus(ModelStatus::Available);
        $model->setContextWindow(4096);
        $model->setInputPricePerToken('0.000001');
        $model->setOutputPricePerToken('0.000002');
        $model->setCapabilities(['text-generation']);
        $model->setIsActive(true);

        return $model;
    }
}
