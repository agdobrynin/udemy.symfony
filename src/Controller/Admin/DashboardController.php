<?php

namespace App\Controller\Admin;

use App\Entity\BlogPost;
use App\Entity\Comment;
use App\Entity\MediaObject;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        // redirect to some CRUD controller
//        $routeBuilder = $this->get(AdminUrlGenerator::class);
//
//        return $this->redirect($routeBuilder->setController(BlogPost::class)->generateUrl());
        return parent::index();
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Udemy Symfony');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linktoDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Blog posts', 'fas fa-list', BlogPost::class);
        yield MenuItem::linkToCrud('Users', 'fas fa-user', User::class);
        yield MenuItem::linkToCrud('Comments', 'fas fa-comment', Comment::class);
        yield MenuItem::linkToCrud('Media objects', 'fas fa-image', MediaObject::class);
    }
}
