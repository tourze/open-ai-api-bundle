<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Tourze\OpenAiApiBundle\Entity\AIModel;
use Tourze\OpenAiApiBundle\Enum\ModelStatus;

final class AIModelFixtures extends Fixture
{
    public const GPT_4_TURBO_REFERENCE = 'gpt-4-turbo';
    public const GPT_35_TURBO_REFERENCE = 'gpt-35-turbo';
    public const GPT_4_VISION_REFERENCE = 'gpt-4-vision';

    public function load(ObjectManager $manager): void
    {
        // GPT-4 Turbo 模型
        $gpt4Turbo = new AIModel();
        $gpt4Turbo->setModelId('gpt-4-turbo');
        $gpt4Turbo->setName('GPT-4 Turbo');
        $gpt4Turbo->setDescription('最新的GPT-4 Turbo模型，具有更高的性能和更大的上下文窗口');
        $gpt4Turbo->setOwner('openai');
        $gpt4Turbo->setStatus(ModelStatus::Available);
        $gpt4Turbo->setContextWindow(128000);
        $gpt4Turbo->setInputPricePerToken('0.000010');
        $gpt4Turbo->setOutputPricePerToken('0.000030');
        $gpt4Turbo->setCapabilities(['chat', 'text-generation', 'function-calling', 'json-mode']);
        $gpt4Turbo->setIsActive(true);

        $manager->persist($gpt4Turbo);
        $this->addReference(self::GPT_4_TURBO_REFERENCE, $gpt4Turbo);

        // GPT-3.5 Turbo 模型
        $gpt35Turbo = new AIModel();
        $gpt35Turbo->setModelId('gpt-3.5-turbo');
        $gpt35Turbo->setName('GPT-3.5 Turbo');
        $gpt35Turbo->setDescription('经济实惠的GPT-3.5 Turbo模型，适合大多数对话场景');
        $gpt35Turbo->setOwner('openai');
        $gpt35Turbo->setStatus(ModelStatus::Available);
        $gpt35Turbo->setContextWindow(16385);
        $gpt35Turbo->setInputPricePerToken('0.000001');
        $gpt35Turbo->setOutputPricePerToken('0.000002');
        $gpt35Turbo->setCapabilities(['chat', 'text-generation', 'function-calling']);
        $gpt35Turbo->setIsActive(true);

        $manager->persist($gpt35Turbo);
        $this->addReference(self::GPT_35_TURBO_REFERENCE, $gpt35Turbo);

        // GPT-4 Vision 模型
        $gpt4Vision = new AIModel();
        $gpt4Vision->setModelId('gpt-4-vision-preview');
        $gpt4Vision->setName('GPT-4 Vision Preview');
        $gpt4Vision->setDescription('支持图像理解的GPT-4模型，目前处于测试阶段');
        $gpt4Vision->setOwner('openai');
        $gpt4Vision->setStatus(ModelStatus::Beta);
        $gpt4Vision->setContextWindow(128000);
        $gpt4Vision->setInputPricePerToken('0.000010');
        $gpt4Vision->setOutputPricePerToken('0.000030');
        $gpt4Vision->setCapabilities(['chat', 'text-generation', 'vision', 'image-understanding']);
        $gpt4Vision->setIsActive(true);

        $manager->persist($gpt4Vision);
        $this->addReference(self::GPT_4_VISION_REFERENCE, $gpt4Vision);

        $manager->flush();
    }
}
