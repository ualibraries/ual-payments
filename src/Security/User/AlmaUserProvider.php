<?php

namespace App\Security\User;

use App\Service\AlmaApi;
use App\Service\AlmaUserData;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AlmaUserProvider implements UserProviderInterface
{
    private $api;
    private $userData;

    public function __construct(AlmaApi $api, AlmaUserData $userData)
    {
        $this->api = $api;
        $this->userData = $userData;
    }

    public function loadUserByUsername($username)
    {
        try {
            $response = $this->api->findUserById($username);
            if ($this->userData->isValidUser($response)) {
                list($firstName, $lastName) = $this->userData->getFirstLastName($response);

                return new AlmaUser($username, array('ROLE_USER'), $firstName, $lastName);
            }
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            echo $e->getCode() . $e->getMessage();
        }

        throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
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
