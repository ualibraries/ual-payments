<?php

namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use SimpleXMLElement;

/**
 * Provides functionality for accessing the Alma API.
 */
class AlmaApi
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

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
        $client = new Client(['base_uri' => $_ENV['API_URL']]);

        $url = str_replace($templateParamNames, $templateParamValues, $urlPath);
        $defaultRequestParams = [
            'headers' => [
                'Authorization' => 'apikey ' . $_ENV['API_KEY'],
            ]
        ];

        try {
            $response = $client->request($method, $url, array_merge_recursive($requestParams, $defaultRequestParams));
        } catch (\Exception $e) {
            $emergency = true;
            $emergencyClientStatusCodes = ['404', '403', '401'];
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();
                // 500 errors are always considered and emergency
                if (!in_array($statusCode, $emergencyClientStatusCodes) && !preg_match('/5[0-9][0-9]/', $statusCode)) {
                    $emergency = false;
                }

                $body = $response->getBody();
                try {
                    $sxml = new SimpleXMLElement($body);

                    foreach ($sxml->errorList as $error) {
                        $code = $error->error->errorCode;
                        $msg = $error->error->errorMessage;
                        $this->logger->error("Alma API error (HTTP status code: $statusCode Alma Error Code: $code): $msg");
                    }
                } catch (\Exception $e1) {
                    /**
                     *  The Alma API will return a 400 status code in the event that the API key is invalid.
                     *  Unfortunately, this same status code is returned under many other circumstances, for
                     *  example if a user provides incorrect credentials to log in.  The only way I can figure
                     *  out how to distinguish the two is by checking the actual text of the body, which annoyingly
                     *  isn't a valid XML response like all the other responses.
                     */
                    if ($body == 'Invalid API Key') {
                        $this->logger->emergency("@web-irt-dev Critical Error: $body :fire:");
                    } else {
                        $this->logger->error("Unable to parse response from Alma API as XML.  Status code: $statusCode  Body: $body");
                    }
                }
            }

            if ($emergency) {
                $this->logger->emergency("@web-irt-dev Critical Error: Unable to reach the Alma API! :fire:");
            }

            throw $e;
        }

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
        $templateParamValues = array(rawurlencode($userId));
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
        $templateParamValues = array(rawurlencode($userId));
        $query = [
            'user_id_type' => 'all_unique',
            'view' => 'full',
            'expand' => 'none'
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
        $templateParamValues = array(rawurlencode($userId), rawurlencode($feeId));
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
        $templateParamValues = array(rawurlencode($userId));

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
        $templateParamValues = array(rawurlencode($userId));
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
