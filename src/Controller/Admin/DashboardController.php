<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\BlogPost;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        return parent::index();
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Udemy Symfony');
    }

    public function configureMenuItems(): iterable
    {
        return [
            MenuItem::section(),
            MenuItem::linktoDashboard('Dashboard', 'fa fa-home'),
            MenuItem::section('Blog'),
            MenuItem::linkToCrud('Blog posts', 'fas fa-list', BlogPost::class),
        ];
    }
}
