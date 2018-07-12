<?php

namespace App\Security\User;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AlmaUserProvider implements UserProviderInterface
{
    public function loadUserByUsername($username)
    {
        return new AlmaUser($username, array('ROLE_USER'));
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof AlmaUser) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }
        return $user;
    }

    public function supportsClass($class)
    {
        return AlmaUser::class === $class;
    }
}
