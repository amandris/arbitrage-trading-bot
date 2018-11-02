<?php

namespace AppBundle\Service\Client;

/**
 * Class BinanceClient
 * @package AppBundle\Service\Client
 */
class BinanceClient extends ParametersAwareClientAbstract implements ExternalClientInterface
{
    /**
     * Returns an array of options to
     *
     * @return array
     */
    protected function getDefaultOptions()
    {
        $headers = [
            'headers' => [
                'Content-Type' => 'application/form-data',
                'apiKey' => $this->parameters['api_key']
            ]
        ];

        return $headers;
    }
}
