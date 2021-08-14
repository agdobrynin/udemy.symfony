<?php

namespace App\Controller\Admin;

use App\Entity\MediaObject;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class MediaObjectCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return MediaObject::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
