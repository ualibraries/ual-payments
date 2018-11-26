<?php

namespace App\Service;

use GuzzleHttp\Psr7\Response;
use SimpleXMLElement;

/**
 * Utility class used for parsing responses from the Alma API.
 */
class AlmaUserData
{
    /**
     * Given a guzzle response from Alma, return the list of fees as an associative array with each
     * fee and each fees properties
     *
     * @param Response $response
     * @return array
     */
    public function listFees(Response $response)
    {
        $sxml = new SimpleXMLElement($response->getBody());

        $list_fees = [];

        // "fee" is an array that includes each individual fee in the user "fees" object in Alma
        foreach ($sxml->fee as $indv_fee) {
            $list_fees[] = [
                'id' => (string)$indv_fee->id,
                'label' => (string)$indv_fee->type->attributes()->desc,
                'balance' => (string)$indv_fee->balance,
                'title' => (string)$indv_fee->title,
                'date' => new \DateTime($indv_fee->creation_time),
                'comment' => (string)$indv_fee->comment
            ];
        }
        return $list_fees;
    }

    /**
     * Get the full name of user from Alma API response
     *
     * @param Response $response
     * @return string
     */
    public function getFullNameAsString(Response $response)
    {
        $sxml = new SimpleXMLElement($response->getBody());
        return $sxml->full_name->__toString();
    }

    /**
     * Checks that there is exactly one user has primary_id set to 'Shib-uaId' property (the user UA id)
     *
     * @param Response $response
     * @return bool
     */
    public function isValidUser(Response $response)
    {
        $sxml = new SimpleXMLElement($response->getBody());
        return $sxml->attributes()->total_record_count == '1';
    }
}
