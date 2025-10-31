<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\OpenAiApiBundle\Entity\Thread;
use Tourze\OpenAiApiBundle\Enum\ThreadStatus;

final class ThreadFixtures extends Fixture implements DependentFixtureInterface
{
    public const THREAD_CODE_REVIEW_REFERENCE = 'thread-code-review';
    public const THREAD_PROJECT_PLANNING_REFERENCE = 'thread-project-planning';
    public const THREAD_CUSTOMER_SUPPORT_REFERENCE = 'thread-customer-support';

    public function load(ObjectManager $manager): void
    {
        // 代码审查线程
        $codeReviewThread = new Thread();
        $codeReviewThread->setThreadId('thread_code_review_001');
        $codeReviewThread->setTitle('PHP代码审查与优化建议');
        $codeReviewThread->setMetadata([
            'project' => 'ecommerce-platform',
            'priority' => 'high',
            'repository' => 'https://github.com/company/ecommerce',
            'reviewer' => 'ai-assistant',
        ]);
        $codeReviewThread->setStatus(ThreadStatus::Active);
        $codeReviewThread->setMessageCount(8);
        $codeReviewThread->setAssistantId('asst_code_helper_001');
        $codeReviewThread->setDescription('针对电商平台核心模块进行代码审查，提供性能优化和安全性改进建议');
        $codeReviewThread->setToolResources([
            'code_interpreter' => [
                'enabled' => true,
                'language_support' => ['php', 'javascript', 'sql'],
            ],
            'file_search' => [
                'enabled' => true,
                'file_ids' => ['file_codebase_001', 'file_standards_002'],
            ],
        ]);

        $manager->persist($codeReviewThread);
        $this->addReference(self::THREAD_CODE_REVIEW_REFERENCE, $codeReviewThread);

        // 项目规划线程
        $projectPlanningThread = new Thread();
        $projectPlanningThread->setThreadId('thread_project_planning_002');
        $projectPlanningThread->setTitle('新功能开发计划讨论');
        $projectPlanningThread->setMetadata([
            'feature' => 'user-recommendation-system',
            'status' => 'planning',
            'deadline' => '2024-12-31',
            'stakeholders' => ['product', 'engineering', 'design'],
        ]);
        $projectPlanningThread->setStatus(ThreadStatus::Active);
        $projectPlanningThread->setMessageCount(15);
        $projectPlanningThread->setAssistantId('asst_code_helper_001');
        $projectPlanningThread->setDescription('讨论用户推荐系统的技术架构设计、开发计划和里程碑');
        $projectPlanningThread->setToolResources([
            'function_calling' => [
                'enabled' => true,
                'functions' => ['create_timeline', 'estimate_effort', 'analyze_dependencies'],
            ],
        ]);

        $manager->persist($projectPlanningThread);
        $this->addReference(self::THREAD_PROJECT_PLANNING_REFERENCE, $projectPlanningThread);

        // 客户支持线程
        $customerSupportThread = new Thread();
        $customerSupportThread->setThreadId('thread_customer_support_003');
        $customerSupportThread->setTitle('订单处理问题解决');
        $customerSupportThread->setMetadata([
            'ticket_id' => 'CS-2024-001589',
            'customer_id' => 'user_12345',
            'issue_type' => 'order_processing',
            'severity' => 'medium',
        ]);
        $customerSupportThread->setStatus(ThreadStatus::Completed);
        $customerSupportThread->setMessageCount(6);
        $customerSupportThread->setAssistantId('asst_customer_service_003');
        $customerSupportThread->setDescription('协助客户解决订单处理异常问题，提供解决方案和后续跟进');
        $customerSupportThread->setToolResources([
            'knowledge_base' => [
                'enabled' => true,
                'categories' => ['order_management', 'payment_issues', 'shipping_policies'],
            ],
            'escalation' => [
                'enabled' => true,
                'threshold_response_time' => 300,
                'human_agent_pool' => ['support_tier_2'],
            ],
        ]);

        $manager->persist($customerSupportThread);
        $this->addReference(self::THREAD_CUSTOMER_SUPPORT_REFERENCE, $customerSupportThread);

        $manager->flush();
    }

    /**
     * @return array<class-string<FixtureInterface>>
     */
    public function getDependencies(): array
    {
        return [
            AssistantFixtures::class,
        ];
    }
}
