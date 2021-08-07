<?php

declare(strict_types=1);

namespace App\Email;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

final class Mailer
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendConfirmationLogin(User $user)
    {
        $email = (new TemplatedEmail())->from('fabien@example.com')
            ->to(new Address($user->getEmail()))
            ->subject('Confirm your account!')
            ->htmlTemplate('emails/signup.html.twig')
            ->context(['user' => $user]);

        $this->mailer->send($email);
    }
}
