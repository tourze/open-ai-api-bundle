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
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\EasyAdminEnumFieldBundle\Field\EnumField;
use Tourze\OpenAiApiBundle\Entity\Assistant;
use Tourze\OpenAiApiBundle\Enum\AssistantStatus;

/**
 * @extends AbstractCrudController<Assistant>
 */
#[AdminCrud(routePath: '/openai/assistant', routeName: 'openai_assistant')]
final class AssistantCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Assistant::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('AI助手')
            ->setEntityLabelInPlural('AI助手管理')
            ->setPageTitle('index', 'AI助手列表')
            ->setPageTitle('new', '新建AI助手')
            ->setPageTitle('edit', '编辑AI助手')
            ->setPageTitle('detail', 'AI助手详情')
            ->setHelp('index', '管理OpenAI助手配置，包括模型、工具和指令设置')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['assistantId', 'name', 'description', 'model'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->onlyOnDetail()
        ;

        yield TextField::new('assistantId', '助手ID')
            ->setMaxLength(100)
            ->setRequired(true)
            ->setHelp('OpenAI助手的唯一标识符')
        ;

        yield TextField::new('name', '助手名称')
            ->setMaxLength(255)
            ->setRequired(true)
            ->setHelp('助手的显示名称')
        ;

        yield TextareaField::new('description', '助手描述')
            ->setNumOfRows(3)
            ->setHelp('助手的功能和用途描述')
            ->hideOnIndex()
        ;

        yield TextField::new('model', '使用模型')
            ->setMaxLength(50)
            ->setRequired(true)
            ->setHelp('助手使用的OpenAI模型')
        ;

        yield TextareaField::new('instructions', '指令提示词')
            ->setNumOfRows(5)
            ->setHelp('定义助手行为和能力的指令')
            ->hideOnIndex()
        ;

        $statusField = EnumField::new('status', '助手状态');
        $statusField->setEnumCases(AssistantStatus::cases());
        $statusField->setHelp('助手的当前状态');
        yield $statusField;

        yield NumberField::new('temperature', '温度参数')
            ->setNumDecimals(2)
            ->setHelp('控制回答的随机性，范围0-2')
            ->hideOnIndex()
        ;

        yield NumberField::new('topP', 'Top P参数')
            ->setNumDecimals(2)
            ->setHelp('核采样参数，范围0-1')
            ->hideOnIndex()
        ;

        yield TextField::new('responseFormat', '响应格式')
            ->setHelp('助手响应的格式要求')
            ->hideOnIndex()
        ;

        yield CodeEditorField::new('tools', '工具配置')
            ->setLanguage('javascript')
            ->hideOnIndex()
            ->setHelp('助手可使用的工具配置，JSON格式')
        ;

        yield CodeEditorField::new('fileIds', '关联文件')
            ->setLanguage('javascript')
            ->hideOnIndex()
            ->setHelp('助手关联的文件ID列表，JSON格式')
        ;

        yield CodeEditorField::new('metadata', '元数据')
            ->setLanguage('javascript')
            ->hideOnIndex()
            ->setHelp('助手的附加元数据信息，JSON格式')
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
            ->add(TextFilter::new('assistantId', '助手ID'))
            ->add(TextFilter::new('name', '助手名称'))
            ->add(TextFilter::new('model', '使用模型'))
            ->add('status')
            ->add(DateTimeFilter::new('createdAt', '创建时间'))
            ->add(DateTimeFilter::new('updatedAt', '更新时间'))
        ;
    }
}
