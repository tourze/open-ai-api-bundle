<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\EasyAdminEnumFieldBundle\Field\EnumField;
use Tourze\OpenAiApiBundle\Entity\ChatConversation;
use Tourze\OpenAiApiBundle\Enum\ConversationStatus;

/**
 * @extends AbstractCrudController<ChatConversation>
 */
#[AdminCrud(routePath: '/openai/chatconversation', routeName: 'openai_chatconversation')]
final class ChatConversationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ChatConversation::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('聊天对话')
            ->setEntityLabelInPlural('聊天对话管理')
            ->setPageTitle('index', '聊天对话列表')
            ->setPageTitle('new', '新建聊天对话')
            ->setPageTitle('edit', '编辑聊天对话')
            ->setPageTitle('detail', '聊天对话详情')
            ->setHelp('index', '管理OpenAI聊天对话会话，查看对话历史和统计信息')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['title', 'model', 'systemPrompt'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->onlyOnDetail()
        ;

        yield TextField::new('title', '对话标题')
            ->setMaxLength(255)
            ->setRequired(true)
            ->setHelp('用于标识此对话的标题')
        ;

        yield TextField::new('model', '使用模型')
            ->setMaxLength(50)
            ->setRequired(true)
            ->setHelp('该对话使用的OpenAI模型')
        ;

        $statusField = EnumField::new('status', '对话状态');
        $statusField->setEnumCases(ConversationStatus::cases());
        $statusField->setHelp('对话的当前状态');
        yield $statusField;

        yield TextareaField::new('systemPrompt', '系统提示词')
            ->setNumOfRows(5)
            ->setHelp('设定AI角色和行为的系统提示词')
            ->hideOnIndex()
        ;

        yield NumberField::new('temperature', '温度参数')
            ->setNumDecimals(2)
            ->setHelp('控制回答的随机性，范围0-2')
            ->hideOnIndex()
        ;

        yield CodeEditorField::new('messages', '对话消息')
            ->setLanguage('javascript')
            ->hideOnForm()
            ->onlyOnDetail()
            ->setHelp('对话的所有消息记录，JSON格式')
        ;

        yield NumberField::new('totalTokens', '总Token数')
            ->hideOnForm()
            ->onlyOnDetail()
            ->setHelp('该对话消耗的总Token数量')
        ;

        yield NumberField::new('cost', '费用')
            ->setNumDecimals(6)
            ->hideOnForm()
            ->onlyOnDetail()
            ->setHelp('该对话产生的费用（美元）')
        ;

        yield DateTimeField::new('createdAt', '创建时间')
            ->hideOnForm()
            ->setFormat('yyyy-MM-dd HH:mm:ss')
        ;

        yield DateTimeField::new('updatedAt', '更新时间')
            ->hideOnForm()
            ->setFormat('yyyy-MM-dd HH:mm:ss')
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('title', '对话标题'))
            ->add(TextFilter::new('model', '使用模型'))
            ->add('status')
            ->add(NumericFilter::new('totalTokens', '总Token数'))
            ->add(DateTimeFilter::new('createdAt', '创建时间'))
            ->add(DateTimeFilter::new('updatedAt', '更新时间'))
        ;
    }
}
