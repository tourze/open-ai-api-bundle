<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Tourze\OpenAiApiBundle\Entity\UploadedFile;
use Tourze\OpenAiApiBundle\Enum\FileStatus;

final class UploadedFileFixtures extends Fixture
{
    public const FILE_CODE_EXAMPLES_REFERENCE = 'file-code-examples';
    public const FILE_FAQ_DATABASE_REFERENCE = 'file-faq-database';
    public const FILE_POLICY_MANUAL_REFERENCE = 'file-policy-manual';
    public const FILE_TRAINING_DATASET_REFERENCE = 'file-training-dataset';

    public function load(ObjectManager $manager): void
    {
        // 代码示例文件
        $codeExamplesFile = new UploadedFile();
        $codeExamplesFile->setFileId('file_code_examples_001');
        $codeExamplesFile->setFilename('php_best_practices_examples.pdf');
        $codeExamplesFile->setPurpose('assistants');
        $codeExamplesFile->setBytes(2048576); // 2MB
        $codeExamplesFile->setStatus(FileStatus::Processed);
        $codeExamplesFile->setMimeType('application/pdf');
        $codeExamplesFile->setDescription('PHP最佳实践代码示例集合，包含常见设计模式和优化技巧');
        $codeExamplesFile->setExpiresAt(new \DateTimeImmutable('+1 year'));
        $codeExamplesFile->setMetadata([
            'category' => 'documentation',
            'language' => 'php',
            'version' => '8.3',
            'topics' => ['design_patterns', 'performance', 'security', 'testing'],
        ]);

        $manager->persist($codeExamplesFile);
        $this->addReference(self::FILE_CODE_EXAMPLES_REFERENCE, $codeExamplesFile);

        // FAQ数据库文件
        $faqDatabaseFile = new UploadedFile();
        $faqDatabaseFile->setFileId('file_faq_database_001');
        $faqDatabaseFile->setFilename('customer_service_faq.json');
        $faqDatabaseFile->setPurpose('assistants');
        $faqDatabaseFile->setBytes(512000); // 500KB
        $faqDatabaseFile->setStatus(FileStatus::Processed);
        $faqDatabaseFile->setMimeType('application/json');
        $faqDatabaseFile->setDescription('客户服务常见问题数据库，包含产品、订单、支付等各类问题解答');
        $faqDatabaseFile->setExpiresAt(new \DateTimeImmutable('+6 months'));
        $faqDatabaseFile->setMetadata([
            'category' => 'knowledge_base',
            'department' => 'customer_service',
            'last_updated' => '2024-09-20',
            'question_count' => 150,
        ]);

        $manager->persist($faqDatabaseFile);
        $this->addReference(self::FILE_FAQ_DATABASE_REFERENCE, $faqDatabaseFile);

        // 政策手册文件
        $policyManualFile = new UploadedFile();
        $policyManualFile->setFileId('file_policy_manual_002');
        $policyManualFile->setFilename('company_policies_v2.4.docx');
        $policyManualFile->setPurpose('assistants');
        $policyManualFile->setBytes(1536000); // 1.5MB
        $policyManualFile->setStatus(FileStatus::Processed);
        $policyManualFile->setMimeType('application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $policyManualFile->setDescription('公司政策手册，包含退换货政策、隐私政策、服务条款等重要文档');
        $policyManualFile->setExpiresAt(new \DateTimeImmutable('+2 years'));
        $policyManualFile->setMetadata([
            'category' => 'policy',
            'version' => '2.4',
            'effective_date' => '2024-01-01',
            'review_cycle' => 'annual',
        ]);

        $manager->persist($policyManualFile);
        $this->addReference(self::FILE_POLICY_MANUAL_REFERENCE, $policyManualFile);

        // 训练数据集文件
        $trainingDatasetFile = new UploadedFile();
        $trainingDatasetFile->setFileId('file_training_dataset_003');
        $trainingDatasetFile->setFilename('conversation_training_data.jsonl');
        $trainingDatasetFile->setPurpose('fine-tuning');
        $trainingDatasetFile->setBytes(10485760); // 10MB
        $trainingDatasetFile->setStatus(FileStatus::Uploaded);
        $trainingDatasetFile->setMimeType('application/x-jsonlines');
        $trainingDatasetFile->setDescription('对话训练数据集，用于微调客服助手模型，提高响应质量');
        $trainingDatasetFile->setExpiresAt(new \DateTimeImmutable('+3 months'));
        $trainingDatasetFile->setMetadata([
            'category' => 'training_data',
            'format' => 'jsonl',
            'conversations_count' => 5000,
            'quality_score' => 0.95,
            'purpose' => 'customer_service_fine_tuning',
        ]);

        $manager->persist($trainingDatasetFile);
        $this->addReference(self::FILE_TRAINING_DATASET_REFERENCE, $trainingDatasetFile);

        $manager->flush();
    }
}
