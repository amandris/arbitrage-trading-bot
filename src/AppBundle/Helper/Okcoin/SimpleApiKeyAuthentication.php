<?php

namespace AppBundle\Helper\Okcoin;

use stdClass;

/**
 * Class SimpleApiKeyAuthentication
 * @package AppBundle\Helper\Okcoin
 */
class SimpleApiKeyAuthentication extends Authentication
{
    /**
     * @var string $_apiKey
     */
	private $_apiKey;

    /**
     * SimpleApiKeyAuthentication constructor.
     * @param $apiKey
     */
	public function __construct($apiKey) {
		$this -> _apiKey = $apiKey;
	}

    /**
     * @return stdClass
     */
	public function getData() {
		$data = new stdClass();
		$data -> apiKey = $this -> _apiKey;
		return $data;
	}

}
