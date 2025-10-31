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
use Tourze\OpenAiApiBundle\Entity\Thread;
use Tourze\OpenAiApiBundle\Enum\ThreadStatus;

/**
 * @extends AbstractCrudController<Thread>
 */
#[AdminCrud(routePath: '/openai/thread', routeName: 'openai_thread')]
final class ThreadCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Thread::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('会话线程')
            ->setEntityLabelInPlural('会话线程管理')
            ->setPageTitle('index', '会话线程列表')
            ->setPageTitle('new', '新建会话线程')
            ->setPageTitle('edit', '编辑会话线程')
            ->setPageTitle('detail', '会话线程详情')
            ->setHelp('index', '管理OpenAI助手的会话线程')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['threadId', 'title', 'description', 'assistantId'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->onlyOnDetail()
        ;

        yield TextField::new('threadId', '线程ID')
            ->setMaxLength(100)
            ->setRequired(true)
            ->setHelp('OpenAI线程的唯一标识符')
        ;

        yield TextField::new('title', '线程标题')
            ->setMaxLength(255)
            ->setHelp('线程的显示标题')
        ;

        yield TextareaField::new('description', '线程描述')
            ->setNumOfRows(3)
            ->setHelp('线程的详细描述')
            ->hideOnIndex()
        ;

        yield TextField::new('assistantId', '关联助手ID')
            ->setMaxLength(100)
            ->setHelp('与此线程关联的助手ID')
        ;

        $statusField = EnumField::new('status', '线程状态');
        $statusField->setEnumCases(ThreadStatus::cases());
        $statusField->setHelp('线程的当前状态');
        yield $statusField;

        yield NumberField::new('messageCount', '消息数量')
            ->setHelp('线程中的消息总数')
            ->hideOnForm()
            ->onlyOnDetail()
        ;

        yield CodeEditorField::new('metadata', '元数据')
            ->setLanguage('javascript')
            ->hideOnIndex()
            ->setHelp('线程的附加元数据信息，JSON格式')
        ;

        yield CodeEditorField::new('toolResources', '工具资源')
            ->setLanguage('javascript')
            ->hideOnIndex()
            ->setHelp('线程可使用的工具资源配置，JSON格式')
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
            ->add(TextFilter::new('threadId', '线程ID'))
            ->add(TextFilter::new('title', '线程标题'))
            ->add(TextFilter::new('assistantId', '关联助手ID'))
            ->add('status')
            ->add(NumericFilter::new('messageCount', '消息数量'))
            ->add(DateTimeFilter::new('createdAt', '创建时间'))
            ->add(DateTimeFilter::new('updatedAt', '更新时间'))
        ;
    }
}
