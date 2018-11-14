<?php

namespace AppBundle\Helper\Okcoin;

/**
 * Class Requestor
 * @package AppBundle\Helper\Okcoin
 */
class Requestor
{
    /**
     * @param $curl
     * @return array
     * @throws Exception
     */
    public function doCurlRequest($curl)
    {
        $response = curl_exec($curl);
        // Check for errors
        if($response === false) {
            $error = curl_errno($curl);
            $message = curl_error($curl);
            curl_close($curl);
            throw new Exception("Network error " . $message . " (" . $error . ")");
        }
        // Check status code
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if($statusCode != 200) {
            throw new Exception("Status code " . $statusCode, $statusCode, $response);
        }

        return array( "statusCode" => $statusCode, "body" => $response );
    }

}
