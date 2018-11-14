<?php

namespace AppBundle\Helper\Okcoin;

/**
 * Class ApiKeyAuthentication
 * @package AppBundle\Helper\Okcoin
 */
class ApiKeyAuthentication extends Authentication
{
    /**
     * @var string $_apiKey
     */
    private $_apiKey;

    /**
     * @var string $_apiKeySecret
     */
    private $_apiKeySecret;

    /**
     * ApiKeyAuthentication constructor.
     * @param $apiKey
     * @param $apiKeySecret
     */
    public function __construct($apiKey, $apiKeySecret)
    {
        $this->_apiKey = $apiKey;
        $this->_apiKeySecret = $apiKeySecret;
    }

    /**
     * @return \stdClass
     */
    public function getData()
    {
        $data = new \stdClass();
        $data->apiKey = $this->_apiKey;
        $data->apiKeySecret = $this->_apiKeySecret;
        return $data;
    }
}