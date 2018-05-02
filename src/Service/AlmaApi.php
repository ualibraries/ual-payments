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
    function __construct() {
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
     * @return mixed|null|\Psr\Http\Message\ResponseInterface
     */
    public function executeApiRequest($urlPath, $method, $queryParams, $curlOps, $templateParamNames, $templateParamValues)
    {
        $client = new Client(['base_uri' => $this->apiUrl]);

        $url = $urlPath;
        $url = str_replace($templateParamNames, $templateParamValues, $urlPath);

        try {
            $response = $client->request($method, $url, [
                'query' => $queryParams,
                'curl' => $curlOps
            ]);
        } catch (GuzzleException $e) {
            echo Psr7\str($e->getRequest());
            if ($e->hasResponse()) {
                echo Psr7\str($e->getResponse());
            }
            return null;
        }
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
            'status' => 'ACTIVE',
            'apikey' => $this->apiKey
        ];
        $curlOps = [
            CURLOPT_HEADER => FALSE,
            CURLOPT_RETURNTRANSFER => TRUE
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
            'expand' => 'none',
            'apikey' => $this->apiKey
        ];
        $curlOps = [
            CURLOPT_HEADER => FALSE,
            CURLOPT_RETURNTRANSFER => TRUE
        ];
        return $this->executeApiRequest($urlPath, $method, $queryParams, $curlOps, $templateParamNames, $templateParamValues);
    }

    /**
     * Use the Alma api to search for the user by primary_id. This is how we will check that a the provided uaid is found
     * in Alma as a primary_id.
     * @param $uaid
     * @return mixed|null|\Psr\Http\Message\ResponseInterface
     */
    public function findUserById($uaid) {
        $method = 'GET';
        $urlPath = '/almaws/v1/users';
        $templateParamNames = array();
        $templateParamValues = array();
        $queryParams = [
            'limit' => '10',
            'offset' => '0',
            'q' => 'primary_id~' . $uaid,
            'order_by' => 'last_name, first_name, primary_id',
            'apikey' => $this->apiKey
        ];
        $curlOps = [
            CURLOPT_HEADER => FALSE,
            CURLOPT_RETURNTRANSFER => TRUE
        ];
        return $this->executeApiRequest($urlPath, $method, $queryParams, $curlOps, $templateParamNames, $templateParamValues);
    }
}
