<?php

namespace App\Entity;

use Symfony\Component\HttpFoundation\Request;


class AlmaUser
{
    private $userId;

    function __construct() {
        $this->userId = $this->getUserIdFromVariable();
    }

    public function getUserIdFromVariable()
    {
        $request = Request::createFromGlobals();

        $testUserId = getenv('TEST_ID');
        $app_env = getenv('APP_ENV');
        
        // If the environment variable TEST_ID, in .env, is set use that as the user id
        if ($testUserId !== "" && $app_env !== 'prod') {
            return $testUserId;
        }

        $userId = $request->server->get('Shib-uaId');

        return $userId;
    }

    public function getUserId() {
        return $this->userId;
    }
}
