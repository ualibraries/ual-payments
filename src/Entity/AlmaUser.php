<?php

namespace App\Entity;

use Symfony\Component\HttpFoundation\Request;


class AlmaUser
{
    private $uaid;

    function __construct() {
        $this->uaid = $this->getUaIdFromVariable();
    }

    public function getUaIdFromVariable()
    {
        $request = Request::createFromGlobals();

        $uaid = $request->server->get('Shib-uaId');

        $test_uaid = getenv('TEST_UAID');

        $app_env = getenv('APP_ENV');
        
        // If the environment variable TEST_UAID, in .env, is set use that as the uaid
        if ($uaid === null && $test_uaid !== "" && $app_env !== 'prod') {
            return $test_uaid;
        }

        return $uaid;
    }

    public function getUaId() {
        return $this->uaid;
    }
}
