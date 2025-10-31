<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Tourze\OpenAiApiBundle\Entity\ChatConversation;
use Tourze\OpenAiApiBundle\Enum\ConversationStatus;

final class ChatConversationFixtures extends Fixture
{
    public const CONVERSATION_CODING_REFERENCE = 'conversation-coding-help';
    public const CONVERSATION_TRANSLATION_REFERENCE = 'conversation-translation-task';
    public const CONVERSATION_BRAINSTORM_REFERENCE = 'conversation-brainstorm-session';

    public function load(ObjectManager $manager): void
    {
        // 编程帮助对话
        $codingConversation = new ChatConversation();
        $codingConversation->setTitle('PHP性能优化咨询');
        $codingConversation->setModel('gpt-4-turbo');
        $codingConversation->setMessages([
            [
                'role' => 'system',
                'content' => '你是一个专业的PHP开发专家，擅长性能优化和最佳实践。',
                'timestamp' => '2024-09-21T10:00:00Z',
            ],
            [
                'role' => 'user',
                'content' => '我的PHP应用在处理大量数据时很慢，有什么优化建议吗？',
                'timestamp' => '2024-09-21T10:01:00Z',
            ],
            [
                'role' => 'assistant',
                'content' => '对于PHP大数据处理优化，我建议从以下几个方面入手：\n\n1. **内存管理**：使用生成器(Generators)处理大数据集，避免一次性加载所有数据到内存\n2. **数据库优化**：使用批量操作、索引优化、查询缓存\n3. **缓存策略**：实现Redis/Memcached缓存热点数据\n4. **异步处理**：使用队列系统处理耗时任务\n\n能告诉我具体的使用场景吗？',
                'timestamp' => '2024-09-21T10:01:30Z',
            ],
        ]);
        $codingConversation->setStatus(ConversationStatus::Active);
        $codingConversation->setTotalTokens(256);
        $codingConversation->setCost('0.002560');
        $codingConversation->setSystemPrompt('你是一个专业的PHP开发专家，擅长性能优化和最佳实践。');
        $codingConversation->setTemperature('0.3');

        $manager->persist($codingConversation);
        $this->addReference(self::CONVERSATION_CODING_REFERENCE, $codingConversation);

        // 翻译任务对话
        $translationConversation = new ChatConversation();
        $translationConversation->setTitle('技术文档中英翻译');
        $translationConversation->setModel('gpt-4-turbo');
        $translationConversation->setMessages([
            [
                'role' => 'system',
                'content' => '你是一个专业的技术翻译专家，精通中英文技术文档翻译。',
                'timestamp' => '2024-09-21T14:30:00Z',
            ],
            [
                'role' => 'user',
                'content' => '请帮我翻译这个API文档：\n\n"This endpoint allows you to retrieve user information including profile data, preferences, and activity history."',
                'timestamp' => '2024-09-21T14:30:15Z',
            ],
            [
                'role' => 'assistant',
                'content' => '该接口的中文翻译为：\n\n"此端点允许您检索用户信息，包括个人资料数据、偏好设置和活动历史记录。"\n\n这是一个标准的用户信息获取API接口描述，翻译保持了技术文档的专业性和准确性。',
                'timestamp' => '2024-09-21T14:30:25Z',
            ],
        ]);
        $translationConversation->setStatus(ConversationStatus::Completed);
        $translationConversation->setTotalTokens(128);
        $translationConversation->setCost('0.001280');
        $translationConversation->setSystemPrompt('你是一个专业的技术翻译专家，精通中英文技术文档翻译。');
        $translationConversation->setTemperature('0.5');

        $manager->persist($translationConversation);
        $this->addReference(self::CONVERSATION_TRANSLATION_REFERENCE, $translationConversation);

        // 头脑风暴对话
        $brainstormConversation = new ChatConversation();
        $brainstormConversation->setTitle('产品功能创意讨论');
        $brainstormConversation->setModel('gpt-3.5-turbo');
        $brainstormConversation->setMessages([
            [
                'role' => 'system',
                'content' => '你是一个创意顾问，善于产品设计和用户体验思考。',
                'timestamp' => '2024-09-21T16:00:00Z',
            ],
            [
                'role' => 'user',
                'content' => '我们正在开发一个在线学习平台，想增加一些有趣的功能来提高用户参与度，有什么建议？',
                'timestamp' => '2024-09-21T16:00:30Z',
            ],
            [
                'role' => 'assistant',
                'content' => '很棒的问题！以下是一些提高用户参与度的创意功能：\n\n🎯 **游戏化元素**\n- 学习积分和徽章系统\n- 每日学习挑战\n- 学习排行榜\n\n👥 **社交功能**\n- 学习小组和讨论区\n- 学习伙伴匹配\n- 知识分享社区\n\n📱 **个性化体验**\n- AI学习路径推荐\n- 学习习惯分析\n- 个性化学习提醒\n\n你觉得哪个方向最符合你们的产品定位？',
                'timestamp' => '2024-09-21T16:01:00Z',
            ],
            [
                'role' => 'user',
                'content' => '游戏化元素听起来很有趣，能详细说说积分系统应该怎么设计吗？',
                'timestamp' => '2024-09-21T16:02:00Z',
            ],
        ]);
        $brainstormConversation->setStatus(ConversationStatus::Active);
        $brainstormConversation->setTotalTokens(185);
        $brainstormConversation->setCost('0.000370');
        $brainstormConversation->setSystemPrompt('你是一个创意顾问，善于产品设计和用户体验思考。');
        $brainstormConversation->setTemperature('0.7');

        $manager->persist($brainstormConversation);
        $this->addReference(self::CONVERSATION_BRAINSTORM_REFERENCE, $brainstormConversation);

        $manager->flush();
    }
}
