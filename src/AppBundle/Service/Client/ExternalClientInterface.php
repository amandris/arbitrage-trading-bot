<?php

namespace AppBundle\Service\Client;

use Psr\Http\Message\ResponseInterface;

/**
 * Interface ExternalClientInterface
 * @package AppBundle\Service\Client
 */
interface ExternalClientInterface
{
    /**
     * Get the instance of the inner client
     *
     * @return mixed
     */
    public function getClient();

    /**
     * Get an array of parameters passed to create the Client instance
     *
     * @return array
     */
    public function getParameters(): array;

    /**
     * @param $method string Method to do the call (GET, POST, PUT, ...)
     * @param $uri string Relative URL to the base_uri
     * @param array $options
     * @return mixed
     */
    public function request($method, $uri, array $options = []): ResponseInterface;
}
