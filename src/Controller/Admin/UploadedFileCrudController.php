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
use Tourze\OpenAiApiBundle\Entity\UploadedFile;
use Tourze\OpenAiApiBundle\Enum\FileStatus;

/**
 * @extends AbstractCrudController<UploadedFile>
 */
#[AdminCrud(routePath: '/openai/file', routeName: 'openai_file')]
final class UploadedFileCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UploadedFile::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('上传文件')
            ->setEntityLabelInPlural('上传文件管理')
            ->setPageTitle('index', '上传文件列表')
            ->setPageTitle('new', '新建上传文件记录')
            ->setPageTitle('edit', '编辑上传文件')
            ->setPageTitle('detail', '上传文件详情')
            ->setHelp('index', '管理上传到OpenAI的文件记录')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['fileId', 'filename', 'purpose', 'mimeType'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->onlyOnDetail()
        ;

        yield TextField::new('fileId', '文件ID')
            ->setMaxLength(100)
            ->setRequired(true)
            ->setHelp('OpenAI返回的文件唯一标识符')
        ;

        yield TextField::new('filename', '文件名')
            ->setMaxLength(255)
            ->setRequired(true)
            ->setHelp('原始文件名')
        ;

        yield TextField::new('purpose', '用途')
            ->setMaxLength(100)
            ->setRequired(true)
            ->setHelp('文件的用途，如：assistants、fine-tune等')
        ;

        yield NumberField::new('bytes', '文件大小（字节）')
            ->setRequired(true)
            ->setHelp('文件的字节大小')
        ;

        yield TextField::new('humanReadableSize', '文件大小')
            ->onlyOnIndex()
            ->setHelp('人类可读的文件大小格式')
        ;

        $statusField = EnumField::new('status', '文件状态');
        $statusField->setEnumCases(FileStatus::cases());
        $statusField->setHelp('文件的处理状态');
        yield $statusField;

        yield TextField::new('mimeType', 'MIME类型')
            ->setMaxLength(100)
            ->setHelp('文件的MIME类型')
            ->hideOnIndex()
        ;

        yield TextareaField::new('description', '文件描述')
            ->setNumOfRows(3)
            ->setHelp('文件的描述信息')
            ->hideOnIndex()
        ;

        yield CodeEditorField::new('metadata', '元数据')
            ->setLanguage('javascript')
            ->hideOnIndex()
            ->setHelp('文件的附加元数据信息，JSON格式')
        ;

        yield DateTimeField::new('expiresAt', '过期时间')
            ->setHelp('文件在OpenAI服务器上的过期时间')
            ->hideOnIndex()
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
            ->add(TextFilter::new('fileId', '文件ID'))
            ->add(TextFilter::new('filename', '文件名'))
            ->add(TextFilter::new('purpose', '用途'))
            ->add('status')
            ->add(TextFilter::new('mimeType', 'MIME类型'))
            ->add(NumericFilter::new('bytes', '文件大小'))
            ->add(DateTimeFilter::new('expiresAt', '过期时间'))
            ->add(DateTimeFilter::new('createdAt', '创建时间'))
            ->add(DateTimeFilter::new('updatedAt', '更新时间'))
        ;
    }
}
