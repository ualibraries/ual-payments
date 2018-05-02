<?php
namespace App\Service;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use \SimpleXMLElement;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;

class AlmaApi
{
    private $api_url;
    private $api_key;

  /**
   * HandleAlmaUserData constructor. Sets the API_URL and API_KEY variables that are set in .env
   */
    function __construct() {
        $this->api_url = getenv('API_URL');
        $this->api_key = getenv('API_KEY');
    }

  /**
   * Wrapper for requests to Almas API
   * @param $uaid - The users uaid (coming from shibboleth server variable property 'Shib-uaId'
   * @param $queryParams
   * @param $url
   * @return SimpleXMLElement
   */
    public function executeApiRequest($uaid, $queryParams, $url) 
    {
        $ch = curl_init();
        $queryParams .= '&' . urlencode('apikey') . '=' . $this->api_key;
        curl_setopt($ch, CURLOPT_URL, $url . $queryParams);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        $response = curl_exec($ch);
        curl_close($ch);
          
        $sXML = new SimpleXMLElement($response);
        return $sXML;
    }

  /**
   * A wrapper around the executeApiRequest method, to be called by get methods retrieving user data.
   * @param $uaid
   * @param $api_path
   * @return SimpleXMLElement
   */
    public function getUserData($uaid, $api_path) {
        $url = $this->api_url . $api_path;
        $templateParamNames = array('{user_id}');
        $templateParamValues = array(urlencode($uaid));
        $url = str_replace($templateParamNames, $templateParamValues, $url);
        $queryParams = '?' . urlencode('user_id_type') . '=' . urlencode('all_unique') . '&' . urlencode('view') . '=' . urlencode('full') . '&' . urlencode('expand') . '=' . urlencode('none');

        return $this->executeApiRequest($uaid, $queryParams, $url);
    }

    public function getUserFines($uaid) 
    {
        $client = new Client(['base_uri' => $this->api_url]);

        $url = '/almaws/v1/users/{user_id}/fees';
        $templateParamNames = array('{user_id}');
        $templateParamValues = array(urlencode($uaid));
        $url = str_replace($templateParamNames, $templateParamValues, $url);

        try {
            $response = $client->request('GET', $url, [
                'query' => [
                    'user_id_type' => 'all_unique',
                    'status' => 'ACTIVE',
                    'apikey' => $this->api_key
                ],
                'curl' => [
                    CURLOPT_HEADER => FALSE,
                    CURLOPT_RETURNTRANSFER => TRUE
                ]
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
   * Retreive users full name from Alma
   * @param $uaid
   * @return SimpleXMLElement
   */
    public function getUsersFullName($uaid)
    {
        $user = $this->getUserData($uaid, '/almaws/v1/users/{user_id}');
        return $user->full_name;
    }

  /**
   * Check the uaid coming from Shibboleth with the primary_id from Alma (they should  be the same). If the user with a netid exists there should be
   * total_record_count equal to '1'. If there is no matching primary_id to uaid in alma, return false.
   * @param $uaid
   * @return bool
   */
    public function getAlmaUser($uaid)
    {
        $queryParams = '?' . urlencode('limit') . '=' . urlencode('1') . '&' . urlencode('offset') . '=' . urlencode('0') . '&' . urlencode('q') . '=' . urlencode('primary_id~' . $uaid) . '&' . urlencode('order_by') . '=' . urlencode('last_name, first_name, primary_id');
        $url = $this->api_url .  '/almaws/v1/users';
        $users = $this->executeApiRequest($uaid,$queryParams, $url);

        return $users->attributes()->total_record_count == '1';

    }
}
