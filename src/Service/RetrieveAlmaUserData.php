<?php
namespace App\Service;

use \SimpleXMLElement; 

class RetrieveAlmaUserData
{
    private $api_url;
    private $api_key;

    function __construct() {
        $this->api_url = getenv('API_URL');
        $this->api_key = getenv('API_KEY');
    }

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



        $ch = curl_init();
        $url = 'https://api-eu.hosted.exlibrisgroup.com/almaws/v1/users';
        $queryParams = '?' . urlencode('limit') . '=' . urlencode('1') . '&' . urlencode('offset') . '=' . urlencode('0') . '&' . urlencode('q') . '=' . urlencode('primary_id~113827331439') . '&' . urlencode('order_by') . '=' . urlencode('last_name, first_name, primary_id') . '&' . urlencode('apikey') . '=' . urlencode('l7xx4bbfb649aec2482a87023726e41fafce');
        curl_setopt($ch, CURLOPT_URL, $url . $queryParams);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        $response = curl_exec($ch);
        curl_close($ch);
         
        var_dump($response);
    }
    
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
        $fees = $this->getUserData($uaid, '/almaws/v1/users/{user_id}/fees');
        $list_fees = [];
        
        // "fee" is an array that includes each individual fee in the user "fees" object in Alma
        foreach ($fees->fee as $indv_fee) {
            $list_fees[] = [
                'id' => (string)$indv_fee->id,
                'label' => (string)$indv_fee->type->attributes()->desc,
                'balance' => (string)$indv_fee->balance,
                'title' => (string)$indv_fee->title
            ];
        }
        return $list_fees;

        

    }

    public function getUsersFullName($uaid)
    {
        $user = $this->getUserData($uaid, '/almaws/v1/users/{user_id}');
        return $user->full_name;
    }

    public function almaUserExists($uaid)
    {
        $queryParams = '?' . urlencode('limit') . '=' . urlencode('1') . '&' . urlencode('offset') . '=' . urlencode('0') . '&' . urlencode('q') . '=' . urlencode('primary_id~' . $uaid) . '&' . urlencode('order_by') . '=' . urlencode('last_name, first_name, primary_id');
        $url = $this->api_url .  '/almaws/v1/users';
        $users = $this->executeApiRequest($uaid,$queryParams, $url);

        return $users->attributes()->total_record_count == '1';

    }
}
