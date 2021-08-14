<?php

namespace App\Controller\Admin;

use App\Entity\MediaObject;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

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
            TextField::new('fileName'),
            ImageField::new('fileName', 'Image Preview')
                ->setBasePath($this->getParameter('app.path.media_objects'))
                ->hideOnForm(),
            ImageField::new('file')
                ->setUploadDir($this->getParameter('app.path.upload.media_objects'))
                ->hideOnIndex(),
        ];
    }
}
