<?php

namespace AppBundle\Service\Client;

/**
 * Class ItbitClient
 * @package AppBundle\Service\Client
 */
class ItbitClient extends ParametersAwareClientAbstract implements ExternalClientInterface
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
                'Content-Type' => 'application/x-www-form-urlencoded',
                'apiKey' => $this->parameters['api_key']
            ]
        ];

        return $headers;
    }
}
