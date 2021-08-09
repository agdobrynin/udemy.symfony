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
    private $robotEmail;

    public function __construct(MailerInterface $mailer, string $robotEmail)
    {
        $this->mailer = $mailer;
        $this->robotEmail = $robotEmail;
    }

    public function sendConfirmationLogin(User $user)
    {
        $email = (new TemplatedEmail())->from($this->robotEmail)
            ->to(new Address($user->getEmail()))
            ->subject('Confirm your account!')
            ->htmlTemplate('emails/signup.html.twig')
            ->context(['user' => $user]);

        $this->mailer->send($email);
    }
}
