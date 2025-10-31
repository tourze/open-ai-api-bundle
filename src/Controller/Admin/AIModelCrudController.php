<?php

declare(strict_types=1);

namespace Tourze\OpenAiApiBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\EasyAdminEnumFieldBundle\Field\EnumField;
use Tourze\OpenAiApiBundle\Entity\AIModel;
use Tourze\OpenAiApiBundle\Enum\ModelStatus;

/**
 * @extends AbstractCrudController<AIModel>
 */
#[AdminCrud(routePath: '/openai/aimodel', routeName: 'openai_aimodel')]
final class AIModelCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return AIModel::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('AI模型')
            ->setEntityLabelInPlural('AI模型管理')
            ->setPageTitle('index', 'AI模型列表')
            ->setPageTitle('new', '新建AI模型')
            ->setPageTitle('edit', '编辑AI模型')
            ->setPageTitle('detail', 'AI模型详情')
            ->setHelp('index', '管理OpenAI模型配置，包括价格、能力等信息')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['modelId', 'name', 'description', 'owner'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->onlyOnDetail()
        ;

        yield TextField::new('modelId', '模型ID')
            ->setMaxLength(100)
            ->setRequired(true)
            ->setHelp('OpenAI模型的唯一标识符')
        ;

        yield TextField::new('name', '模型名称')
            ->setMaxLength(255)
            ->setRequired(true)
            ->setHelp('模型的显示名称')
        ;

        yield TextareaField::new('description', '模型描述')
            ->setNumOfRows(3)
            ->setHelp('模型的详细描述和用途说明')
            ->hideOnIndex()
        ;

        yield TextField::new('owner', '所有者')
            ->setMaxLength(50)
            ->setRequired(true)
            ->setHelp('模型的提供方，如：openai')
        ;

        $statusField = EnumField::new('status', '模型状态');
        $statusField->setEnumCases(ModelStatus::cases());
        $statusField->setHelp('模型的当前可用状态');
        yield $statusField;

        yield BooleanField::new('isActive', '是否激活')
            ->setHelp('是否在系统中启用此模型')
        ;

        yield NumberField::new('contextWindow', '上下文窗口')
            ->setHelp('模型支持的最大Token数')
            ->hideOnIndex()
        ;

        yield NumberField::new('inputPricePerToken', '输入Token价格')
            ->setNumDecimals(6)
            ->setHelp('每个输入Token的价格（美元）')
            ->hideOnIndex()
        ;

        yield NumberField::new('outputPricePerToken', '输出Token价格')
            ->setNumDecimals(6)
            ->setHelp('每个输出Token的价格（美元）')
            ->hideOnIndex()
        ;

        yield CodeEditorField::new('capabilities', '模型能力')
            ->setLanguage('javascript')
            ->hideOnIndex()
            ->setHelp('模型支持的功能特性，JSON格式')
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
            ->add(TextFilter::new('modelId', '模型ID'))
            ->add(TextFilter::new('name', '模型名称'))
            ->add(TextFilter::new('owner', '所有者'))
            ->add('status')
            ->add(BooleanFilter::new('isActive', '是否激活'))
            ->add(NumericFilter::new('contextWindow', '上下文窗口'))
            ->add(DateTimeFilter::new('createdAt', '创建时间'))
            ->add(DateTimeFilter::new('updatedAt', '更新时间'))
        ;
    }
}
