<?php

namespace AppBundle\Helper;

use Exception;

/**
 * Class KrakenHelper
 * @package AppBundle\Helper
 *
 */
class KrakenHelper
{
    /**
     * @var string $key
     */
    protected $key;

    /**
     * @var string $secret
     */
    protected $secret;

    /**
     * @var string $baseUri
     */
    protected $baseUri;

    /**
     * @var resource $curl
     */
    protected $curl;

    /**
     * Constructor for KrakenAPI
     *
     * @param string $key API key
     * @param string $secret API secret
     * @param string $baseUri URL for Kraken API
     * @param bool $sslverify enable/disable SSL peer verification.  disable if using beta.api.kraken.com
     */
    function __construct($key, $secret, $baseUri, $sslverify=true)
    {
        if(!function_exists('curl_init')) {
            print "[ERROR] The Kraken API client requires that PHP is compiled with 'curl' support.\n";
            exit(1);
        }

        $this->key      = $key;
        $this->secret   = $secret;
        $this->baseUri  = $baseUri;
        $this->curl     = curl_init();

        curl_setopt_array($this->curl, array(
                CURLOPT_SSL_VERIFYPEER => $sslverify,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_USERAGENT => 'Kraken PHP API Agent',
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true)
        );
    }

    function __destruct()
    {
        if(function_exists('curl_close')) {
            curl_close($this->curl);
        }
    }

    /**
     * Query public methods
     *
     * @param string $method method name
     * @param array $request request parameters
     * @return array request result on success
     * @throws Exception
     */
    function queryPublic($method, array $request = array())
    {
        $postdata = http_build_query($request, '', '&');

        curl_setopt($this->curl, CURLOPT_URL, $this->baseUri . '/0/public/' . $method);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, array());
        $result = curl_exec($this->curl);

        if($result===false)
            throw new Exception('CURL error: ' . curl_error($this->curl));

        $result = json_decode($result, true);

        if(!is_array($result))
            throw new Exception('JSON decode error');

        return $result;
    }

    /**
     * Query private methods
     *
     * @param string $method method path
     * @param array $request request parameters
     * @return array request result on success
     * @throws Exception
     */
    function queryPrivate($method, array $request = array())
    {
        if(!isset($request['nonce'])) {
            $nonce = explode(' ', microtime());
            $request['nonce'] = $nonce[1] . str_pad(substr($nonce[0], 2, 6), 6, '0');
        }

        $postdata = http_build_query($request, '', '&');

        $path = '/0/private/' . $method;

        $sign = hash_hmac('sha512', $path . hash('sha256', $request['nonce'] . $postdata, true), base64_decode($this->secret), true);
        $headers = array(
            'API-Key: ' . $this->key,
            'API-Sign: ' . base64_encode($sign)
        );

        curl_setopt($this->curl, CURLOPT_URL, $this->baseUri . $path);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($this->curl);

        if($result===false)
            throw new Exception('CURL error: ' . curl_error($this->curl));

        $result = json_decode($result, true);

        if(!is_array($result))
            throw new Exception('JSON decode error');

        return $result;
    }
}