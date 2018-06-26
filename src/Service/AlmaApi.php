<?php

namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class AlmaApi
{
    /**
     * Wrapper for requests to Alma API
     *
     * @param $urlPath
     * @param $method
     * @param $requestParams
     * @param $templateParamNames
     * @param $templateParamValues
     * @return mixed|null|\Psr\Http\Message\ResponseInterface
     * @throws GuzzleException
     */
    protected function executeApiRequest($urlPath, $method, $requestParams, $templateParamNames, $templateParamValues)
    {
        $client = new Client(['base_uri' => getenv('API_URL')]);

        $url = str_replace($templateParamNames, $templateParamValues, $urlPath);
        $defaultRequestParams = [
            'headers' => [
                'Authorization' => 'apikey ' . getenv('API_KEY'),
            ]
        ];
        $response = $client->request($method, $url, array_merge_recursive($requestParams, $defaultRequestParams));

        return $response;
    }

    /**
     * Get the users list of fees from Alma
     *
     * @param $userId
     * @return mixed|null|\Psr\Http\Message\ResponseInterface
     * @throws GuzzleException
     */
    public function getUserFees($userId)
    {
        $method = 'GET';
        $urlPath = '/almaws/v1/users/{user_id}/fees';
        $templateParamNames = array('{user_id}');
        $templateParamValues = array(urlencode($userId));
        $query = [
            'user_id_type' => 'all_unique',
            'status' => 'ACTIVE'
        ];
        $requestParams = compact('query');

        return $this->executeApiRequest($urlPath, $method, $requestParams, $templateParamNames, $templateParamValues);
    }

    /**
     * Get the user from alma by the user id. Returns 400 status code if user does not exist.
     *
     * @param $userId
     * @return mixed|null|\Psr\Http\Message\ResponseInterface
     * @throws GuzzleException
     */
    public function getUserById($userId)
    {
        $method = 'GET';
        $urlPath = '/almaws/v1/users/{user_id}';
        $templateParamNames = array('{user_id}');
        $templateParamValues = array(urlencode($userId));
        $query = [
            'user_id_type' => 'all_unique',
            'view' => 'full',
            'expand' => 'none'
        ];
        $requestParams = compact('query');

        return $this->executeApiRequest($urlPath, $method, $requestParams, $templateParamNames, $templateParamValues);
    }

    /**
     * Use the Alma api to search for the user by primary_id.
     * This is how we will check that a the provided user id is found in Alma as a primary_id.
     *
     * @param $userId
     * @return mixed|null|\Psr\Http\Message\ResponseInterface
     * @throws GuzzleException
     */
    public function findUserById($userId)
    {
        $method = 'GET';
        $urlPath = '/almaws/v1/users';
        $templateParamNames = array();
        $templateParamValues = array();
        $query = [
            'limit' => '10',
            'offset' => '0',
            'q' => 'primary_id~' . $userId,
            'order_by' => 'last_name first_name, primary_id'
        ];
        $requestParams = compact('query');

        return $this->executeApiRequest($urlPath, $method, $requestParams, $templateParamNames, $templateParamValues);
    }

    /**
     * @param string $userId The alphanumeric userId of the logged in user
     * @param string $feeId The Alma specific fee id to be updated
     * @param $amount
     * @param string $method
     * @param null $externalTransactionId
     * @param null $comment
     * @return mixed|null|\Psr\Http\Message\ResponseInterface
     * @throws GuzzleException
     */
    public function payUserFee($userId, $feeId, $amount, $method = 'ONLINE', $externalTransactionId = null, $comment = null)
    {
        $queryParams = [
            'op' => 'pay',
            'amount' => $amount,
            'method' => $method,
            'external_transaction_id' => $externalTransactionId,
            'comment' => $comment,
        ];
        /**
         * " If no callback is supplied, all entries of array equal to FALSE (see converting to boolean) will be removed."
         * - http://php.net/array_filter
         */
        $queryParams = array_filter($queryParams);

        return $this->updateUserFee($userId, $feeId, $queryParams);
    }

    /**
     * @param string $userId The alphanumeric userId of the logged in user
     * @param string $feeId The Alma specific fee id to be updated
     * @param array $query The parameters for the query.
     * @return mixed|null|\Psr\Http\Message\ResponseInterface
     * @throws GuzzleException
     */
    protected function updateUserFee($userId, $feeId, $query)
    {
        $method = 'POST';
        $urlPath = '/almaws/v1/users/{user_id}/fees/{fee_id}';
        $templateParamNames = array('{user_id}', '{fee_id}');
        $templateParamValues = array(urlencode($userId), urlencode($feeId));
        $requestParams = compact('query');

        return $this->executeApiRequest($urlPath, $method, $requestParams, $templateParamNames, $templateParamValues);
    }

    /**
     * @param string $userId The alphanumeric userId of the logged in user
     * @param mixed $body A plain PHP object representing a fee.
     * @return mixed|null|\Psr\Http\Message\ResponseInterface
     * @throws GuzzleException
     */
    public function createUserFee($userId, $body)
    {
        $method = 'POST';
        $urlPath = '/almaws/v1/users/{user_id}/fees';
        $templateParamNames = array('{user_id}');
        $templateParamValues = array(urlencode($userId));

        $headers = [
            'Content-Type' => 'application/json'
        ];
        $body = json_encode($body);
        $requestParams = compact('body', 'headers');

        return $this->executeApiRequest($urlPath, $method, $requestParams, $templateParamNames, $templateParamValues);
    }

    /**
     * @param string $userId The alphanumeric userId of the logged in user
     * @param string $userPassword The alphanumeric userPassword of the logged in user
     * @return mixed|null|\Psr\Http\Message\ResponseInterface
     * @throws GuzzleException
     */
    public function authenticateUser($userId, $userPassword)
    {
        $method = 'POST';
        $urlPath = '/almaws/v1/users/{user_id}';
        $templateParamNames = array('{user_id}');
        $templateParamValues = array(urlencode($userId));
        $query = [
            'user_id_type' => 'all_unique',
            'op' => 'auth'
        ];

        $headers = [
            'Exl-User-Pw' => $userPassword
        ];
        $requestParams = compact('query', 'headers');

        return $this->executeApiRequest($urlPath, $method, $requestParams, $templateParamNames, $templateParamValues);
    }
}
