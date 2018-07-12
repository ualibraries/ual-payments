<?php

namespace App\Security\User;

use App\Service\AlmaApi;
use App\Service\AlmaUserData;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AlmaUser implements UserInterface, EquatableInterface
{
    private $username;
    private $roles;
    private $firstName;
    private $lastName;

    private $valid = false;

    public function __construct($username, array $roles, AlmaApi $api, AlmaUserData $userData)
    {
        $this->username = $username;
        $this->roles = $roles;

        try {
            $response = $api->findUserById($username);
            if ($userData->isValidUser($response)) {
                $this->valid = true;
                list($this->firstName, $this->lastName) = $userData->getFirstLastName($response);
            }
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            echo $e->getCode() . $e->getMessage();
        }
    }

    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return array (Role|string)[] The user roles
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string The password
     */
    public function getPassword()
    {
        return null;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
    }

    /**
     * The equality comparison should neither be done by referential equality
     * nor by comparing identities (i.e. getId() === getId()).
     *
     * However, you do not need to compare every attribute, but only those that
     * are relevant for assessing whether re-authentication is required.
     *
     * Also implementation should consider that $user instance may implement
     * the extended user interface `AdvancedUserInterface`.
     *
     * @param UserInterface $user
     * @return bool
     */
    public function isEqualTo(UserInterface $user)
    {
        if (!$user instanceof AlmaUser) {
            return false;
        }
        if ($this->username !== $user->getUsername()) {
            return false;
        }
        return true;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return $this->valid;
    }
}
