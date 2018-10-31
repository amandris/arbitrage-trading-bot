<?php

namespace AppBundle\Service\Client;

use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ParametersAwareClientAbstract
 * @package AppBundle\Service\Client
 */
abstract class ParametersAwareClientAbstract
{
    /**
     * @var ClientInterface 
     */
    protected $client;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * ParametersAwareClientAbstract constructor.
     * @param ClientInterface $client
     * @param array $parameters
     */
    public function __construct(ClientInterface $client, array $parameters)
    {
        $this->client = $client;
        $this->parameters = $parameters;
    }

    /**
     * Get the instance of the inner client
     *
     * @return mixed
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Get an array of parameters passed to create the Client instance
     *
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param $method string Method to do the call (GET, POST, PUT, ...)
     * @param $uri string Relative URL to the base_uri
     * @param array $options
     * @return mixed
     */
    public function request($method, $uri, array $options = []): ResponseInterface
    {
        $uri = $this->createUriWithParameters($uri);
        $options = $this->prepareOptions($options);

        return $this->getClient()->request($method, $uri, $options);
    }

    /**
     * @param string $uri
     * @return string
     */
    protected function createUriWithParameters(string $uri): string
    {
        return $this->replacePlaceHoldersForParameters($this->parameters['base_uri'] . $uri);
    }

    /**
     * @param array $options
     * @return array
     */
    protected function prepareOptions(array $options)
    {
        if (array_key_exists('body', $options)) {
            $options['body'] = $this->replacePlaceHoldersForParameters($options['body']);
        }

        // Reemplazamos o añadimos los DefaultOptions con las options en concreto para la request

        return array_replace_recursive($this->getDefaultOptions(), $options);
    }

    /**
     * @param string $element
     * @return string
     */
    private function replacePlaceHoldersForParameters(string $element)
    {
        foreach ($this->parameters as $parameterName => $parameterValue) {
            $element = str_replace('{' . $parameterName . '}', $parameterValue, $element);
        }

        return $element;
    }

    abstract protected function getDefaultOptions();
}