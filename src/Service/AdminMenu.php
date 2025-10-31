<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle\Service;

use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use Tourze\OpenAiApiBundle\Entity\AIModel;
use Tourze\OpenAiApiBundle\Entity\Assistant;
use Tourze\OpenAiApiBundle\Entity\ChatConversation;
use Tourze\OpenAiApiBundle\Entity\Thread;
use Tourze\OpenAiApiBundle\Entity\UploadedFile;

#[Autoconfigure(public: true)]
readonly class AdminMenu implements MenuProviderInterface
{
    public function __construct(private LinkGeneratorInterface $linkGenerator)
    {
    }

    public function __invoke(ItemInterface $item): void
    {
        $openaiMenu = $item->getChild('OpenAI API');
        if (null === $openaiMenu) {
            $openaiMenu = $item->addChild('OpenAI API');
        }

        $openaiMenu
            ->addChild('聊天对话')
            ->setUri($this->linkGenerator->getCurdListPage(ChatConversation::class))
            ->setAttribute('icon', 'fas fa-comments')
        ;

        $openaiMenu
            ->addChild('AI 模型')
            ->setUri($this->linkGenerator->getCurdListPage(AIModel::class))
            ->setAttribute('icon', 'fas fa-brain')
        ;

        $openaiMenu
            ->addChild('上传文件')
            ->setUri($this->linkGenerator->getCurdListPage(UploadedFile::class))
            ->setAttribute('icon', 'fas fa-file-upload')
        ;

        $openaiMenu
            ->addChild('AI 助手')
            ->setUri($this->linkGenerator->getCurdListPage(Assistant::class))
            ->setAttribute('icon', 'fas fa-user-robot')
        ;

        $openaiMenu
            ->addChild('对话线程')
            ->setUri($this->linkGenerator->getCurdListPage(Thread::class))
            ->setAttribute('icon', 'fas fa-thread')
        ;
    }
}
