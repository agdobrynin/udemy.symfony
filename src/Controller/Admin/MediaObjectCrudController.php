<?php

namespace App\Controller\Admin;

use App\Entity\MediaObject;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Vich\UploaderBundle\Form\Type\VichImageType;

class MediaObjectCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return MediaObject::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('fileName')->hideOnForm(),
            TextField::new('mimeType')->hideOnForm(),
            ImageField::new('fileName', 'Image Preview')
                ->setBasePath($this->getParameter('app.path.media_objects'))
                ->hideOnForm(),
            Field::new('file')->setFormType(VichImageType::class)->hideOnIndex(),
        ];
    }
}
