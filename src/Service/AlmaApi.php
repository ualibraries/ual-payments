<?php
namespace App\Service;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use \SimpleXMLElement;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;

class AlmaApi
{
    private $apiUrl;
    private $apiKey;

  /**
   * HandleAlmaUserData constructor. Sets the apiUrl and apiKey variables that are set in .env
   */
    public function __construct()
    {
        $this->apiUrl = getenv('API_URL');
        $this->apiKey = getenv('API_KEY');
    }

    /**
     * Wrapper for requests to Almas API
     * @param $urlPath
     * @param $method
     * @param $queryParams
     * @param $curlOps
     * @param $templateParamNames
     * @param $templateParamValues
     * @throws \GuzzleHttp\Exception\TransferException
     * @return mixed|null|\Psr\Http\Message\ResponseInterface
     */
    protected function executeApiRequest($urlPath, $method, $queryParams, $curlOps, $templateParamNames, $templateParamValues)
    {
        $client = new Client(['base_uri' => $this->apiUrl]);

        $url = $urlPath;
        $url = str_replace($templateParamNames, $templateParamValues, $urlPath);

        $response = $client->request($method, $url, [
            'query' => $queryParams,
            'curl' => $curlOps,
            'headers' => [
                'Authorization' => 'apikey ' . $this->apiKey
            ]
        ]);

        return $response;
    }

    /**
     * Get the users list of fines from Alma
     * @param $uaid
     * @return mixed|null|\Psr\Http\Message\ResponseInterface
     */
    public function getUserFines($uaid)
    {
        $method = 'GET';
        $urlPath = '/almaws/v1/users/{user_id}/fees';
        $templateParamNames = array('{user_id}');
        $templateParamValues = array(urlencode($uaid));
        $queryParams = [
            'user_id_type' => 'all_unique',
            'status' => 'ACTIVE'
        ];
        $curlOps = [
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true
        ];

        return $this->executeApiRequest($urlPath, $method, $queryParams, $curlOps, $templateParamNames, $templateParamValues);
    }

    /**
     * Get the user from alma by the user id. Returns 400 status code if user does not exist.s
     * @param $uaid
     * @return mixed|null|\Psr\Http\Message\ResponseInterface
     */
    public function getUserById($uaid)
    {
        $method = 'GET';
        $urlPath = '/almaws/v1/users/{user_id}';
        $templateParamNames = array('{user_id}');
        $templateParamValues = array(urlencode($uaid));
        $queryParams = [
            'user_id_type' => 'all_unique',
            'view' => 'full',
            'expand' => 'none'
        ];
        $curlOps = [
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true
        ];
        return $this->executeApiRequest($urlPath, $method, $queryParams, $curlOps, $templateParamNames, $templateParamValues);
    }

    /**
     * Use the Alma api to search for the user by primary_id. This is how we will check that a the provided uaid is found
     * in Alma as a primary_id.
     * @param $uaid
     * @return mixed|null|\Psr\Http\Message\ResponseInterface
     */
    public function findUserById($uaid)
    {
        $method = 'GET';
        $urlPath = '/almaws/v1/users';
        $templateParamNames = array();
        $templateParamValues = array();
        $queryParams = [
            'limit' => '10',
            'offset' => '0',
            'q' => 'primary_id~' . $uaid,
            'order_by' => 'last_name first_name, primary_id'
        ];
        $curlOps = [
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true
        ];
        return $this->executeApiRequest($urlPath, $method, $queryParams, $curlOps, $templateParamNames, $templateParamValues);
    }

    /**
     * @param $uaid - The numeric uaid of the logged in user
     * @param $feeId - The Alma specific fee id to be updated
     * @return mixed|null|\Psr\Http\Message\ResponseInterface
     */
    public function payUserFee($uaid, $feeId, $amount, $method = 'ONLINE', $externalTransactionId = null, $comment = null)
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

        return $this->updateUserFee($uaid, $feeId, $queryParams);
    }

    /**
     * @param $uaid - The numeric uaid of the logged in user
     * @param $feeId - The Alma specific fee id to be updated
     * @param $queryParams - The parameters for the query.
     * @return mixed|null|\Psr\Http\Message\ResponseInterface
     */
    protected function updateUserFee($uaid, $feeId, $queryParams)
    {
        $method = 'POST';
        $urlPath = '/almaws/v1/users/{user_id}/fees/{fee_id}';
        $templateParamNames = array('{user_id}', '{fee_id}');
        $templateParamValues = array(urlencode($uaid), urlencode($feeId));
        $curlOps = [
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true
        ];
        return $this->executeApiRequest($urlPath, $method, $queryParams, $curlOps, $templateParamNames, $templateParamValues);
    }
}
