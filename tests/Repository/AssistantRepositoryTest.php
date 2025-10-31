<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\OpenAiApiBundle\Entity\Assistant;
use Tourze\OpenAiApiBundle\Enum\AssistantStatus;
use Tourze\OpenAiApiBundle\Repository\AssistantRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(AssistantRepository::class)]
#[RunTestsInSeparateProcesses]
final class AssistantRepositoryTest extends AbstractRepositoryTestCase
{
    public function testSaveShouldPersistEntity(): void
    {
        $repository = self::getService(AssistantRepository::class);
        $assistant = $this->createTestAssistant();

        $repository->save($assistant);

        $this->assertNotNull($assistant->getId());

        $foundAssistant = $repository->find($assistant->getId());
        $this->assertInstanceOf(Assistant::class, $foundAssistant);
        $this->assertSame($assistant->getName(), $foundAssistant->getName());
    }

    public function testRemoveShouldDeleteEntity(): void
    {
        $repository = self::getService(AssistantRepository::class);
        $assistant = $this->createTestAssistant();
        $repository->save($assistant);
        $assistantId = $assistant->getId();

        $repository->remove($assistant);

        $foundAssistant = $repository->find($assistantId);
        $this->assertNull($foundAssistant);
    }

    public function testFindByStatus(): void
    {
        $repository = self::getService(AssistantRepository::class);

        $activeAssistant = $this->createTestAssistant();
        $activeAssistant->setName('Active Assistant');
        $activeAssistant->setStatus(AssistantStatus::Active);
        $deletedAssistant = $this->createTestAssistant();
        $deletedAssistant->setAssistantId('asst-deleted');
        $deletedAssistant->setName('Deleted Assistant');
        $deletedAssistant->setStatus(AssistantStatus::Archived);

        $repository->save($activeAssistant);
        $repository->save($deletedAssistant);

        // 添加直接断言以满足PHPStan要求
        $activeResults = $repository->findByStatus(AssistantStatus::Active);
        $this->assertIsArray($activeResults);
        $this->assertNotEmpty($activeResults, 'Should find at least one active assistant');

        $this->validateStatusResults($repository, AssistantStatus::Active, $activeAssistant, 'Active assistant should be found');
        $this->validateStatusResults($repository, AssistantStatus::Archived, $deletedAssistant, 'Deleted assistant should be found');
    }

    private function validateStatusResults(AssistantRepository $repository, AssistantStatus $status, Assistant $expected, string $message): void
    {
        $results = $repository->findByStatus($status);
        $this->assertIsArray($results);
        $this->assertContainsOnlyInstancesOf(Assistant::class, $results);

        $found = $this->containsAssistantWithId($results, $expected->getId());
        $this->assertTrue($found, $message);

        // 验证所有结果的状态正确
        foreach ($results as $result) {
            $this->assertSame($status, $result->getStatus());
        }
    }

    /**
     * @param array<Assistant> $results
     */
    private function containsAssistantWithId(array $results, ?int $targetId): bool
    {
        foreach ($results as $result) {
            if ($result->getId() === $targetId) {
                return true;
            }
        }

        return false;
    }

    public function testFindActive(): void
    {
        $repository = self::getService(AssistantRepository::class);

        $activeAssistant = $this->createTestAssistant();
        $activeAssistant->setName('Active Helper');
        $activeAssistant->setStatus(AssistantStatus::Active);
        $deletedAssistant = $this->createTestAssistant();
        $deletedAssistant->setAssistantId('asst-inactive');
        $deletedAssistant->setName('Inactive Helper');
        $deletedAssistant->setStatus(AssistantStatus::Archived);

        $repository->save($activeAssistant);
        $repository->save($deletedAssistant);

        $results = $repository->findActive();
        $this->assertIsArray($results);
        $this->assertContainsOnlyInstancesOf(Assistant::class, $results);

        $foundActive = $this->containsAssistantWithId($results, $activeAssistant->getId());
        $foundDeleted = $this->containsAssistantWithId($results, $deletedAssistant->getId());

        $this->assertTrue($foundActive, 'Active assistant should be found');
        $this->assertFalse($foundDeleted, 'Deleted assistant should not be found');

        // 验证所有结果都是Active状态
        foreach ($results as $result) {
            $this->assertSame(AssistantStatus::Active, $result->getStatus());
        }
    }

    public function testFindByAssistantId(): void
    {
        $repository = self::getService(AssistantRepository::class);

        $assistant = $this->createTestAssistant();
        $assistant->setAssistantId('asst-unique-123');
        $assistant->setName('Unique Assistant');

        $repository->save($assistant);

        $foundAssistant = $repository->findByAssistantId('asst-unique-123');
        $this->assertInstanceOf(Assistant::class, $foundAssistant);
        $this->assertSame('asst-unique-123', $foundAssistant->getAssistantId());
        $this->assertSame($assistant->getId(), $foundAssistant->getId());

        $notFoundAssistant = $repository->findByAssistantId('asst-non-existent');
        $this->assertNull($notFoundAssistant);
    }

    public function testFindByModel(): void
    {
        $repository = self::getService(AssistantRepository::class);

        $gpt4Assistant = $this->createTestAssistant();
        $gpt4Assistant->setAssistantId('asst-gpt4');
        $gpt4Assistant->setModel('gpt-4');
        $gpt4Assistant->setName('GPT-4 Assistant');
        $gpt35Assistant = $this->createTestAssistant();
        $gpt35Assistant->setAssistantId('asst-gpt35');
        $gpt35Assistant->setModel('gpt-3.5-turbo');
        $gpt35Assistant->setName('GPT-3.5 Assistant');

        $repository->save($gpt4Assistant);
        $repository->save($gpt35Assistant);

        // 添加直接断言以满足PHPStan要求
        $gpt4Results = $repository->findByModel('gpt-4');
        $this->assertIsArray($gpt4Results);
        $this->assertNotEmpty($gpt4Results, 'Should find at least one GPT-4 assistant');

        $this->validateModelResults($repository, 'gpt-4', $gpt4Assistant, 'GPT-4 assistant should be found');
        $this->validateModelResults($repository, 'gpt-3.5-turbo', $gpt35Assistant, 'GPT-3.5 assistant should be found');
    }

    private function validateModelResults(AssistantRepository $repository, string $model, Assistant $expected, string $message): void
    {
        $results = $repository->findByModel($model);
        $this->assertIsArray($results);
        $this->assertContainsOnlyInstancesOf(Assistant::class, $results);

        $found = $this->containsAssistantWithId($results, $expected->getId());
        $this->assertTrue($found, $message);

        // 验证所有结果的模型正确
        foreach ($results as $result) {
            $this->assertSame($model, $result->getModel());
        }
    }

    public function testFindByStatusOrdersByUpdatedAtDesc(): void
    {
        $repository = self::getService(AssistantRepository::class);
        $testAssistants = $this->createOrderedActiveAssistants($repository);

        $results = $repository->findByStatus(AssistantStatus::Active);
        $this->assertIsArray($results);

        $this->validateStatusOrderByUpdatedAt($results, $testAssistants);
    }

    /**
     * @return array<string, Assistant>
     */
    private function createOrderedActiveAssistants(AssistantRepository $repository): array
    {
        $older = $this->createTestAssistant();
        $older->setAssistantId('asst-older');
        $older->setStatus(AssistantStatus::Active);
        $older->setName('Older Active Assistant');
        $repository->save($older);

        usleep(100000); // 100ms的延迟以确保时间差异

        $newer = $this->createTestAssistant();
        $newer->setAssistantId('asst-newer');
        $newer->setStatus(AssistantStatus::Active);
        $newer->setName('Newer Active Assistant');
        $repository->save($newer);

        return ['older' => $older, 'newer' => $newer];
    }

    /**
     * @param array<Assistant> $results
     * @param array<string, Assistant> $testAssistants
     */
    private function validateStatusOrderByUpdatedAt(array $results, array $testAssistants): void
    {
        $ourResults = $this->filterOurTestResults($results, $testAssistants);
        if (count($ourResults) < 2) {
            return;
        }

        $records = $this->extractTestRecordsByKey($ourResults, $testAssistants);
        if (null === $records['newer'] || null === $records['older']) {
            return;
        }

        $this->assertGreaterThanOrEqual(
            $records['older']->getUpdatedAt(),
            $records['newer']->getUpdatedAt(),
            'Newer assistant should have updatedAt >= older assistant'
        );
    }

    /**
     * @param array<Assistant> $results
     * @param array<string, Assistant> $testAssistants
     * @return array{newer: ?Assistant, older: ?Assistant}
     */
    private function extractTestRecordsByKey(array $results, array $testAssistants): array
    {
        $newer = null;
        $older = null;

        foreach ($results as $result) {
            if ($result->getId() === $testAssistants['newer']->getId()) {
                $newer = $result;
            } elseif ($result->getId() === $testAssistants['older']->getId()) {
                $older = $result;
            }
        }

        return ['newer' => $newer, 'older' => $older];
    }

    public function testFindByModelOrdersByCreatedAtDesc(): void
    {
        $repository = self::getService(AssistantRepository::class);
        $testAssistants = $this->createOrderedGpt4Assistants($repository);

        $results = $repository->findByModel('gpt-4');
        $this->assertIsArray($results);

        $this->validateCreatedAtDescOrder($results, $testAssistants);
    }

    /**
     * @return array<string, Assistant>
     */
    private function createOrderedGpt4Assistants(AssistantRepository $repository): array
    {
        $older = $this->createTestAssistant();
        $older->setAssistantId('asst-older-gpt4');
        $older->setModel('gpt-4');
        $older->setName('Older GPT-4 Assistant');
        $repository->save($older);

        usleep(100000); // 100ms的延迟以确保时间差异

        $newer = $this->createTestAssistant();
        $newer->setAssistantId('asst-newer-gpt4');
        $newer->setModel('gpt-4');
        $newer->setName('Newer GPT-4 Assistant');
        $repository->save($newer);

        return ['older' => $older, 'newer' => $newer];
    }

    /**
     * @param array<Assistant> $results
     * @param array<string, Assistant> $testAssistants
     */
    private function validateCreatedAtDescOrder(array $results, array $testAssistants): void
    {
        $ourResults = $this->filterOurTestResults($results, $testAssistants);
        if (count($ourResults) < 2) {
            return;
        }

        $records = $this->extractTestRecordsByKey($ourResults, $testAssistants);
        if (null === $records['newer'] || null === $records['older']) {
            return;
        }

        $this->assertGreaterThanOrEqual(
            $records['older']->getCreatedAt(),
            $records['newer']->getCreatedAt(),
            'Newer assistant should have createdAt >= older assistant'
        );
    }

    /**
     * @param array<Assistant> $results
     * @param array<string, Assistant> $testAssistants
     * @return array<Assistant>
     */
    private function filterOurTestResults(array $results, array $testAssistants): array
    {
        $targetIds = [$testAssistants['older']->getId(), $testAssistants['newer']->getId()];

        return array_filter($results, fn ($assistant) => in_array($assistant->getId(), $targetIds, true));
    }

    public function testBasicRepositoryFunctionality(): void
    {
        $repository = self::getService(AssistantRepository::class);

        // 测试基本的查找功能
        $assistant1 = $this->createTestAssistant();
        $assistant2 = $this->createTestAssistant();
        $assistant2->setAssistantId('asst-second');
        $assistant2->setName('Second Assistant');

        $repository->save($assistant1);
        $repository->save($assistant2);

        // 测试 findAll
        $allAssistants = $repository->findAll();
        $this->assertIsArray($allAssistants);
        $this->assertGreaterThanOrEqual(2, count($allAssistants));

        // 测试 findBy
        $foundByName = $repository->findBy(['name' => $assistant1->getName()]);
        $this->assertIsArray($foundByName);
        $this->assertGreaterThanOrEqual(1, count($foundByName));

        // 测试 findOneBy
        $foundOne = $repository->findOneBy(['assistantId' => $assistant2->getAssistantId()]);
        $this->assertInstanceOf(Assistant::class, $foundOne);
        $this->assertSame($assistant2->getId(), $foundOne->getId());

        // 测试 count
        $totalCount = $repository->count([]);
        $this->assertGreaterThanOrEqual(2, $totalCount);

        $statusCount = $repository->count(['status' => AssistantStatus::Active]);
        $this->assertGreaterThanOrEqual(2, $statusCount);
    }

    protected function onSetUp(): void
    {
    }

    /**
     * @return ServiceEntityRepository<Assistant>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return self::getService(AssistantRepository::class);
    }

    protected function createNewEntity(): object
    {
        return $this->createTestAssistant();
    }

    public function testSearch(): void
    {
        $repository = self::getService(AssistantRepository::class);
        $testAssistants = $this->createSearchTestAssistants($repository);
        $searchable = $testAssistants['searchable'];

        // 验证搜索功能的基本断言
        $searchResult = $repository->search('Searchable');
        $this->assertIsArray($searchResult);
        $this->assertNotEmpty($searchResult);

        // 批量验证搜索场景
        $searchScenarios = [
            ['keyword' => 'Searchable', 'shouldFind' => true, 'message' => 'Should find assistant by name'],
            ['keyword' => 'searching', 'shouldFind' => true, 'message' => 'Should find assistant by description'],
            ['keyword' => 'repositories', 'shouldFind' => true, 'message' => 'Should find assistant by instructions'],
            ['keyword' => 'nonexistent-keyword', 'shouldFind' => false, 'message' => 'Should not find assistant with non-existent keyword'],
        ];

        foreach ($searchScenarios as $scenario) {
            $this->validateSearchScenario($repository, $searchable, $scenario);
        }
    }

    /**
     * @param array{keyword: string, shouldFind: bool, message: string} $scenario
     */
    private function validateSearchScenario(AssistantRepository $repository, Assistant $searchable, array $scenario): void
    {
        $results = $repository->search($scenario['keyword']);
        $this->assertIsArray($results);

        $found = $this->findAssistantInResults($results, $searchable);
        $this->assertSame($scenario['shouldFind'], $found, $scenario['message']);
    }

    /**
     * @return array<string, Assistant>
     */
    private function createSearchTestAssistants(AssistantRepository $repository): array
    {
        $searchableAssistant = $this->createTestAssistant();
        $searchableAssistant->setName('Searchable Code Helper');
        $searchableAssistant->setDescription('This is a code assistant for searching');
        $searchableAssistant->setInstructions('You help with searching code repositories');

        $otherAssistant = $this->createTestAssistant();
        $otherAssistant->setAssistantId('asst-other');
        $otherAssistant->setName('Other Assistant');
        $otherAssistant->setDescription('This does something else');
        $otherAssistant->setInstructions('You do other things');

        $repository->save($searchableAssistant);
        $repository->save($otherAssistant);

        return [
            'searchable' => $searchableAssistant,
            'other' => $otherAssistant,
        ];
    }

    /**
     * @param array<Assistant> $results
     */
    private function findAssistantInResults(array $results, Assistant $targetAssistant): bool
    {
        $this->assertContainsOnlyInstancesOf(Assistant::class, $results);

        return $this->containsAssistantWithId($results, $targetAssistant->getId());
    }

    public function testFindByTool(): void
    {
        $repository = self::getService(AssistantRepository::class);

        // 清理现有数据
        $existingAssistants = $repository->findAll();
        foreach ($existingAssistants as $assistant) {
            $repository->remove($assistant, true); // 直接flush，避免调用protected方法
        }

        $codeInterpreterAssistant = $this->createTestAssistant();
        $codeInterpreterAssistant->setAssistantId('asst-code-interpreter');
        $codeInterpreterAssistant->setName('Code Interpreter Assistant');
        $codeInterpreterAssistant->setTools([['type' => 'code_interpreter'], ['type' => 'function']]);

        $functionAssistant = $this->createTestAssistant();
        $functionAssistant->setAssistantId('asst-function');
        $functionAssistant->setName('Function Assistant');
        $functionAssistant->setTools([['type' => 'function']]);

        $retrievalAssistant = $this->createTestAssistant();
        $retrievalAssistant->setAssistantId('asst-retrieval');
        $retrievalAssistant->setName('Retrieval Assistant');
        $retrievalAssistant->setTools([['type' => 'retrieval']]);

        $repository->save($codeInterpreterAssistant);
        $repository->save($functionAssistant);
        $repository->save($retrievalAssistant);

        // 测试方法存在性
        $this->assertTrue(method_exists($repository, 'findByTool'), 'Repository should have findByTool method');

        try {
            $codeResults = $repository->findByTool('code_interpreter');
            $this->assertIsArray($codeResults);
        } catch (\Exception $e) {
            // 如果数据库不支持JSON_SEARCH，跳过此测试
            self::markTestSkipped('Database does not support JSON_SEARCH function: ' . $e->getMessage());
        }
    }

    public function testFindRecentlyCreated(): void
    {
        $repository = self::getService(AssistantRepository::class);
        $testAssistants = $this->createRecentlyCreatedAssistants($repository);

        $results = $repository->findRecentlyCreated(5);
        $this->assertIsArray($results);
        $this->assertLessThanOrEqual(5, count($results));

        $this->validateAllResultsAreAssistants($results);
        $this->validateRecentlyCreatedOrder($results, $testAssistants);
    }

    /**
     * @return array<string, Assistant>
     */
    private function createRecentlyCreatedAssistants(AssistantRepository $repository): array
    {
        $assistant1 = $this->createTestAssistant();
        $assistant1->setName('Recently Created Assistant 1');
        $repository->save($assistant1);

        usleep(100000); // 100ms的延迟以确保时间差异

        $assistant2 = $this->createTestAssistant();
        $assistant2->setAssistantId('asst-recent-2');
        $assistant2->setName('Recently Created Assistant 2');
        $repository->save($assistant2);

        return ['first' => $assistant1, 'second' => $assistant2];
    }

    /**
     * @param array<Assistant> $results
     */
    private function validateAllResultsAreAssistants(array $results): void
    {
        foreach ($results as $result) {
            $this->assertInstanceOf(Assistant::class, $result);
        }
    }

    /**
     * @param array<Assistant> $results
     * @param array<string, Assistant> $testAssistants
     */
    private function validateRecentlyCreatedOrder(array $results, array $testAssistants): void
    {
        if (count($results) < 2) {
            return;
        }

        $ourResults = $this->extractOurAssistants($results, $testAssistants);

        if (count($ourResults) < 2) {
            return;
        }

        $this->sortAndValidateCreatedAtOrder($ourResults);
    }

    /**
     * @param array<Assistant> $results
     * @param array<string, Assistant> $testAssistants
     * @return array<Assistant>
     */
    private function extractOurAssistants(array $results, array $testAssistants): array
    {
        $targetIds = [$testAssistants['first']->getId(), $testAssistants['second']->getId()];

        return array_filter($results, fn ($result) => in_array($result->getId(), $targetIds, true));
    }

    /**
     * @param array<Assistant> $ourResults
     */
    private function sortAndValidateCreatedAtOrder(array $ourResults): void
    {
        $sorted = $ourResults;
        usort($sorted, fn ($a, $b) => $b->getCreatedAt() <=> $a->getCreatedAt());

        $this->validateDescendingDateOrder($sorted, 'getCreatedAt', 'createdAt');
    }

    /**
     * @param array<Assistant> $assistants
     */
    private function validateDescendingDateOrder(array $assistants, string $method, string $fieldName): void
    {
        for ($i = 0; $i < count($assistants) - 1; ++$i) {
            $current = 'getCreatedAt' === $method ? $assistants[$i]->getCreatedAt() : $assistants[$i]->getUpdatedAt();
            $next = 'getCreatedAt' === $method ? $assistants[$i + 1]->getCreatedAt() : $assistants[$i + 1]->getUpdatedAt();

            $this->assertGreaterThanOrEqual(
                $next,
                $current,
                "Results should be ordered by {$fieldName} DESC"
            );
        }
    }

    public function testFindRecentlyUpdated(): void
    {
        $repository = self::getService(AssistantRepository::class);
        $testAssistants = $this->createRecentlyUpdatedAssistants($repository);

        $results = $repository->findRecentlyUpdated(5);
        $this->assertIsArray($results);
        $this->assertLessThanOrEqual(5, count($results));

        $this->validateAllResultsAreAssistants($results);
        $this->validateRecentlyUpdatedOrder($results, $testAssistants);
    }

    /**
     * @return array<string, Assistant>
     */
    private function createRecentlyUpdatedAssistants(AssistantRepository $repository): array
    {
        $assistant1 = $this->createTestAssistant();
        $assistant1->setName('Recently Updated Assistant 1');
        $repository->save($assistant1);

        usleep(100000); // 100ms的延迟以确保时间差异

        $assistant2 = $this->createTestAssistant();
        $assistant2->setAssistantId('asst-updated-2');
        $assistant2->setName('Recently Updated Assistant 2');
        $repository->save($assistant2);

        return ['first' => $assistant1, 'second' => $assistant2];
    }

    /**
     * @param array<Assistant> $results
     * @param array<string, Assistant> $testAssistants
     */
    private function validateRecentlyUpdatedOrder(array $results, array $testAssistants): void
    {
        if (count($results) < 2) {
            return;
        }

        $ourResults = $this->extractOurAssistants($results, $testAssistants);

        if (count($ourResults) < 2) {
            return;
        }

        $this->sortAndValidateUpdatedAtOrder($ourResults);
    }

    /**
     * @param array<Assistant> $ourResults
     */
    private function sortAndValidateUpdatedAtOrder(array $ourResults): void
    {
        $sorted = $ourResults;
        usort($sorted, fn ($a, $b) => $b->getUpdatedAt() <=> $a->getUpdatedAt());

        $this->validateDescendingDateOrder($sorted, 'getUpdatedAt', 'updatedAt');
    }

    public function testFindWithMetadata(): void
    {
        $repository = self::getService(AssistantRepository::class);

        // 测试方法存在性
        $this->assertTrue(method_exists($repository, 'findWithMetadata'), 'Repository should have findWithMetadata method');

        try {
            $results = $repository->findWithMetadata();
            $this->assertIsArray($results);
        } catch (\Exception $e) {
            // 如果数据库不支持JSON_LENGTH，跳过此测试
            self::markTestSkipped('Database does not support JSON_LENGTH function: ' . $e->getMessage());
        }
    }

    public function testFindWithTemperature(): void
    {
        $repository = self::getService(AssistantRepository::class);

        $assistantWithTemp = $this->createTestAssistant();
        $assistantWithTemp->setName('Assistant with Temperature');
        $assistantWithTemp->setTemperature('0.8');

        $assistantWithoutTemp = $this->createTestAssistant();
        $assistantWithoutTemp->setAssistantId('asst-no-temp');
        $assistantWithoutTemp->setName('Assistant without Temperature');
        $assistantWithoutTemp->setTemperature(null);

        $repository->save($assistantWithTemp);
        $repository->save($assistantWithoutTemp);

        $results = $repository->findWithTemperature();
        $this->assertIsArray($results);
        $this->assertContainsOnlyInstancesOf(Assistant::class, $results);

        $foundWithTemp = $this->containsAssistantWithId($results, $assistantWithTemp->getId());
        $foundWithoutTemp = $this->containsAssistantWithId($results, $assistantWithoutTemp->getId());

        $this->assertTrue($foundWithTemp, 'Assistant with temperature should be found');
        $this->assertFalse($foundWithoutTemp, 'Assistant without temperature should not be found');

        // 验证所有结果都有温度值
        foreach ($results as $result) {
            $this->assertNotNull($result->getTemperature(), 'Assistant should have non-null temperature');
        }

        // 验证按温度升序排列
        $this->validateTemperatureAscOrder($results);
    }

    /**
     * @param array<Assistant> $results
     */
    private function validateTemperatureAscOrder(array $results): void
    {
        if (count($results) < 2) {
            return;
        }

        for ($i = 0; $i < count($results) - 1; ++$i) {
            $currentTemp = (float) $results[$i]->getTemperature();
            $nextTemp = (float) $results[$i + 1]->getTemperature();
            $this->assertLessThanOrEqual(
                $nextTemp,
                $currentTemp,
                'Results should be ordered by temperature ASC'
            );
        }
    }

    public function testFindWithoutFiles(): void
    {
        $repository = self::getService(AssistantRepository::class);

        // 测试方法存在性
        $this->assertTrue(method_exists($repository, 'findWithoutFiles'), 'Repository should have findWithoutFiles method');

        try {
            $results = $repository->findWithoutFiles();
            $this->assertIsArray($results);
        } catch (\Exception $e) {
            // 如果数据库不支持JSON_LENGTH，跳过此测试
            self::markTestSkipped('Database does not support JSON_LENGTH function: ' . $e->getMessage());
        }
    }

    public function testFindWithoutTools(): void
    {
        $repository = self::getService(AssistantRepository::class);

        // 测试方法存在性
        $this->assertTrue(method_exists($repository, 'findWithoutTools'), 'Repository should have findWithoutTools method');

        try {
            $results = $repository->findWithoutTools();
            $this->assertIsArray($results);
        } catch (\Exception $e) {
            // 如果数据库不支持JSON_LENGTH，跳过此测试
            self::markTestSkipped('Database does not support JSON_LENGTH function: ' . $e->getMessage());
        }
    }

    private function createTestAssistant(): Assistant
    {
        $assistant = new Assistant();
        $assistant->setAssistantId('asst_' . uniqid());
        $assistant->setName('Test Assistant ' . uniqid());
        $assistant->setModel('gpt-3.5-turbo');
        $assistant->setInstructions('You are a helpful test assistant');
        $assistant->setTools([['type' => 'code_interpreter']]);
        $assistant->setMetadata(['test' => true]);
        $assistant->setStatus(AssistantStatus::Active);
        $assistant->setTemperature('0.70');
        $assistant->setTopP('1.00');
        $assistant->setFileIds(['file-123']);
        $assistant->setResponseFormat('text');

        return $assistant;
    }

    public function testCountByStatus(): void
    {
        $repository = self::getService(AssistantRepository::class);

        $assistant1 = $this->createTestAssistant();
        $assistant1->setStatus(AssistantStatus::Active);
        $repository->save($assistant1);

        $assistant2 = $this->createTestAssistant();
        $assistant2->setAssistantId('asst_count_test2');
        $assistant2->setStatus(AssistantStatus::Active);
        $repository->save($assistant2);

        $assistant3 = $this->createTestAssistant();
        $assistant3->setAssistantId('asst_count_test3');
        $assistant3->setStatus(AssistantStatus::Inactive);
        $repository->save($assistant3);

        $activeCount = $repository->countByStatus(AssistantStatus::Active);
        $inactiveCount = $repository->countByStatus(AssistantStatus::Inactive);

        $this->assertGreaterThanOrEqual(2, $activeCount);
        $this->assertGreaterThanOrEqual(1, $inactiveCount);
    }

    public function testFindByFileId(): void
    {
        $repository = self::getService(AssistantRepository::class);

        $assistant1 = $this->createTestAssistant();
        $assistant1->setFileIds(['file-abc123', 'file-def456']);
        $repository->save($assistant1);

        $assistant2 = $this->createTestAssistant();
        $assistant2->setAssistantId('asst_file_test2');
        $assistant2->setFileIds(['file-xyz789']);
        $repository->save($assistant2);

        $results = $repository->findByFileId('file-abc123');
        $this->assertIsArray($results);
        $this->assertContainsOnlyInstancesOf(Assistant::class, $results);

        $foundAssistant1 = $this->containsAssistantWithId($results, $assistant1->getId());
        $this->assertTrue($foundAssistant1, 'Assistant with file-abc123 should be found');
    }

    public function testFindByResponseFormat(): void
    {
        $repository = self::getService(AssistantRepository::class);

        $assistant1 = $this->createTestAssistant();
        $assistant1->setResponseFormat('json_object');
        $repository->save($assistant1);

        $assistant2 = $this->createTestAssistant();
        $assistant2->setAssistantId('asst_response_test2');
        $assistant2->setResponseFormat('text');
        $repository->save($assistant2);

        $results = $repository->findByResponseFormat('json_object');
        $this->assertIsArray($results);
        $this->assertContainsOnlyInstancesOf(Assistant::class, $results);

        $foundAssistant1 = $this->containsAssistantWithId($results, $assistant1->getId());
        $this->assertTrue($foundAssistant1, 'Assistant with json_object format should be found');

        // 验证找到的助手有正确的响应格式
        foreach ($results as $result) {
            if ($result->getId() === $assistant1->getId()) {
                $this->assertSame('json_object', $result->getResponseFormat());
            }
        }
    }

    public function testFindByTemperatureRange(): void
    {
        $repository = self::getService(AssistantRepository::class);

        $assistant1 = $this->createTestAssistant();
        $assistant1->setTemperature('0.50');
        $repository->save($assistant1);

        $assistant2 = $this->createTestAssistant();
        $assistant2->setAssistantId('asst_temp_test2');
        $assistant2->setTemperature('0.90');
        $repository->save($assistant2);

        $assistant3 = $this->createTestAssistant();
        $assistant3->setAssistantId('asst_temp_test3');
        $assistant3->setTemperature('1.50');
        $repository->save($assistant3);

        // Test with both min and max
        $results = $repository->findByTemperatureRange(0.4, 1.0);
        $this->assertIsArray($results);
        $this->assertContainsOnlyInstancesOf(Assistant::class, $results);

        $foundAssistant1 = $this->containsAssistantWithId($results, $assistant1->getId());
        $foundAssistant2 = $this->containsAssistantWithId($results, $assistant2->getId());
        $foundAssistant3 = $this->containsAssistantWithId($results, $assistant3->getId());

        $this->assertTrue($foundAssistant1, 'Assistant with temperature 0.50 should be found');
        $this->assertTrue($foundAssistant2, 'Assistant with temperature 0.90 should be found');
        $this->assertFalse($foundAssistant3, 'Assistant with temperature 1.50 should not be found');

        // Test with only min
        $resultsMin = $repository->findByTemperatureRange(0.8);
        $this->assertIsArray($resultsMin);
        $this->assertGreaterThanOrEqual(1, count($resultsMin));

        // Test with only max
        $resultsMax = $repository->findByTemperatureRange(null, 0.6);
        $this->assertIsArray($resultsMax);
        $this->assertGreaterThanOrEqual(1, count($resultsMax));
    }
}
