<?php
/**
 * Created by PhpStorm.
 * User: cao89
 * Date: 5/2/18
 * Time: 8:32 AM
 */

namespace App\Service;

use GuzzleHttp\Psr7\Response;
use SimpleXMLElement;

class AlmaUserData
{
    /**
     * Given a guzzle response from Alma, return the list of fines as an associative array with each
     * fine and each fines properties
     * @param Response $response
     * @return array
     */
    public function listFines(Response $response)
    {
        $sxml = new SimpleXMLElement($response->getBody());

        $list_fees = [];

        // "fee" is an array that includes each individual fee in the user "fees" object in Alma
        foreach ($sxml->fee as $indv_fee) {
            $list_fees[] = [
                'id' => (string)$indv_fee->id,
                'label' => (string)$indv_fee->type->attributes()->desc,
                'balance' => (string)$indv_fee->balance,
                'title' => (string)$indv_fee->title
            ];
        }
        return $list_fees;
    }

    /**
     * Get the full name of user from Alma API response
     * @param Response $response
     * @return SimpleXMLElement
     */
    public function getFullNameAsString(Response $response)
    {
        $sxml = new SimpleXMLElement($response->getBody());
        return $sxml->full_name;
    }

    /**
     * Checks that there is exactly one user has primary_id set to 'Shib-uaId' property (the user UA id)
     * @param Response $response
     * @return bool
     */
    public function isValidUser(Response $response)
    {
        $sxml = new SimpleXMLElement($response->getBody());
        return $sxml->attributes()->total_record_count == '1';
    }
}
