<?php

namespace AppBundle\Service\Client;

/**
 * Class KrakenClient
 * @package AppBundle\Service\Client
 */
class KrakenClient extends ParametersAwareClientAbstract implements ExternalClientInterface
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
