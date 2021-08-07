<?php

declare(strict_types=1);

namespace App\Controller;

use App\Security\UserConfirmationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/")
 */
class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="default.index")
     */
    public function index()
    {
        return $this->render('base.html.twig');
    }
    /**
     * @Route(
     *     "/api/users/confirm/{token}",
     *     methods={"get"},
     *     name="user_confirm_from_email"
     * )
     */
    public function confirmUser(string $token, UserConfirmationService $userConfirmationService): RedirectResponse
    {
        $userConfirmationService->confirmUser($token);

        return $this->redirectToRoute('default.index');
    }
}
