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
     * @param $requestParams
     * @param $templateParamNames
     * @param $templateParamValues
     * @throws \GuzzleHttp\Exception\TransferException
     * @return mixed|null|\Psr\Http\Message\ResponseInterface
     */
    protected function executeApiRequest($urlPath, $method, $requestParams, $templateParamNames, $templateParamValues)
    {
        $client = new Client(['base_uri' => $this->apiUrl]);

        $url = $urlPath;
        $url = str_replace($templateParamNames, $templateParamValues, $urlPath);
        $defaultRequestParams = [
            'headers' => [
                'Authorization' => 'apikey ' . $this->apiKey
            ]
        ];

        $response = $client->request($method, $url, $defaultRequestParams + $requestParams);

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
        $query = [
            'user_id_type' => 'all_unique',
            'status' => 'ACTIVE'
        ];
        $curl = [
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true
        ];
        $requestParams = compact('query', 'curl');

        return $this->executeApiRequest($urlPath, $method, $requestParams, $templateParamNames, $templateParamValues);
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
        $query = [
            'user_id_type' => 'all_unique',
            'view' => 'full',
            'expand' => 'none'
        ];
        $curl = [
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true
        ];

        $requestParams = compact('query', 'curl');
        return $this->executeApiRequest($urlPath, $method, $requestParams, $templateParamNames, $templateParamValues);
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
        $query = [
            'limit' => '10',
            'offset' => '0',
            'q' => 'primary_id~' . $uaid,
            'order_by' => 'last_name first_name, primary_id'
        ];
        $curl = [
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true
        ];

        $requestParams = compact('query', 'curl');
        return $this->executeApiRequest($urlPath, $method, $requestParams, $templateParamNames, $templateParamValues);
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
     * @param $query - The parameters for the query.
     * @return mixed|null|\Psr\Http\Message\ResponseInterface
     */
    protected function updateUserFee($uaid, $feeId, $query)
    {
        $method = 'POST';
        $urlPath = '/almaws/v1/users/{user_id}/fees/{fee_id}';
        $templateParamNames = array('{user_id}', '{fee_id}');
        $templateParamValues = array(urlencode($uaid), urlencode($feeId));
        $curl = [
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true
        ];
        $requestParams = compact('curl', 'query');
        return $this->executeApiRequest($urlPath, $method, $requestParams, $templateParamValues);
    }

}
