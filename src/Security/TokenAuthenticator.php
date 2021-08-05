<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\ExpiredTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Guard\JWTTokenAuthenticator;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class TokenAuthenticator extends JWTTokenAuthenticator
{
    public function getUser($preAuthToken, UserProviderInterface $userProvider)
    {
        /** @var User $user */
        $user = parent::getUser($preAuthToken, $userProvider);
        $timeStampLastUpdate = $user->getPasswordChangedTimeStamp();
        if($timeStampLastUpdate && $preAuthToken->getPayload()['iat'] < $timeStampLastUpdate) {
            throw new ExpiredTokenException();
        }

        return $user;
    }

}
