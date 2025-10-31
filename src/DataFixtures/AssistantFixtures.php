<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Tourze\OpenAiApiBundle\Entity\Assistant;
use Tourze\OpenAiApiBundle\Enum\AssistantStatus;

final class AssistantFixtures extends Fixture
{
    public const CODE_ASSISTANT_REFERENCE = 'code-assistant';
    public const TRANSLATION_ASSISTANT_REFERENCE = 'translation-assistant';
    public const CUSTOMER_SERVICE_REFERENCE = 'customer-service-assistant';

    public function load(ObjectManager $manager): void
    {
        // 代码助手
        $codeAssistant = new Assistant();
        $codeAssistant->setAssistantId('asst_code_helper_001');
        $codeAssistant->setName('代码助手');
        $codeAssistant->setDescription('专门帮助开发者编写、调试和优化代码的AI助手');
        $codeAssistant->setModel('gpt-4-turbo');
        $codeAssistant->setInstructions('你是一个专业的代码助手，擅长多种编程语言，能够帮助用户编写高质量的代码、调试错误、优化性能，并提供最佳实践建议。请始终提供清晰、易懂的解释。');
        $codeAssistant->setTools([
            ['type' => 'code_interpreter'],
            ['type' => 'function', 'function' => ['name' => 'execute_code', 'description' => '执行代码片段']],
        ]);
        $codeAssistant->setMetadata([
            'category' => 'development',
            'expertise' => ['php', 'javascript', 'python', 'sql'],
            'created_by' => 'system',
        ]);
        $codeAssistant->setStatus(AssistantStatus::Active);
        $codeAssistant->setTemperature('0.3');
        $codeAssistant->setTopP('0.9');
        $codeAssistant->setFileIds(['file_code_examples_001']);
        $codeAssistant->setResponseFormat('{"type": "text"}');

        $manager->persist($codeAssistant);
        $this->addReference(self::CODE_ASSISTANT_REFERENCE, $codeAssistant);

        // 翻译助手
        $translationAssistant = new Assistant();
        $translationAssistant->setAssistantId('asst_translation_002');
        $translationAssistant->setName('多语言翻译助手');
        $translationAssistant->setDescription('专业的多语言翻译助手，支持中英日韩等多种语言互译');
        $translationAssistant->setModel('gpt-4-turbo');
        $translationAssistant->setInstructions('你是一个专业的翻译助手，精通多种语言的翻译工作。请提供准确、自然、符合目标语言习惯的翻译结果，并在必要时提供文化背景解释。');
        $translationAssistant->setTools([
            ['type' => 'function', 'function' => ['name' => 'detect_language', 'description' => '检测输入文本的语言']],
        ]);
        $translationAssistant->setMetadata([
            'category' => 'translation',
            'languages' => ['zh-CN', 'en-US', 'ja-JP', 'ko-KR'],
            'specialty' => 'technical_translation',
        ]);
        $translationAssistant->setStatus(AssistantStatus::Active);
        $translationAssistant->setTemperature('0.5');
        $translationAssistant->setTopP('0.8');
        $translationAssistant->setFileIds([]);

        $manager->persist($translationAssistant);
        $this->addReference(self::TRANSLATION_ASSISTANT_REFERENCE, $translationAssistant);

        // 客服助手
        $customerServiceAssistant = new Assistant();
        $customerServiceAssistant->setAssistantId('asst_customer_service_003');
        $customerServiceAssistant->setName('智能客服助手');
        $customerServiceAssistant->setDescription('专业的客户服务助手，能够处理常见问题、投诉建议和售后支持');
        $customerServiceAssistant->setModel('gpt-3.5-turbo');
        $customerServiceAssistant->setInstructions('你是一个友善、专业的客服代表，始终以客户为中心，耐心解答问题，提供有用的解决方案。保持礼貌和专业的语调，在无法解决问题时主动转接人工客服。');
        $customerServiceAssistant->setTools([
            ['type' => 'function', 'function' => ['name' => 'search_knowledge_base', 'description' => '搜索知识库']],
            ['type' => 'function', 'function' => ['name' => 'escalate_to_human', 'description' => '转接人工客服']],
        ]);
        $customerServiceAssistant->setMetadata([
            'category' => 'customer_service',
            'department' => 'support',
            'response_time_target' => '< 30 seconds',
        ]);
        $customerServiceAssistant->setStatus(AssistantStatus::Active);
        $customerServiceAssistant->setTemperature('0.7');
        $customerServiceAssistant->setTopP('0.9');
        $customerServiceAssistant->setFileIds(['file_faq_database_001', 'file_policy_manual_002']);
        $customerServiceAssistant->setResponseFormat('{"type": "text"}');

        $manager->persist($customerServiceAssistant);
        $this->addReference(self::CUSTOMER_SERVICE_REFERENCE, $customerServiceAssistant);

        $manager->flush();
    }
}
