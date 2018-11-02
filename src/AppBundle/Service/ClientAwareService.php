<?php

namespace AppBundle\Service;

use AppBundle\Service\Client\ExternalClientInterface;

/**
 * Class ClientAwareService
 * @package AppBundle\Service
 */
abstract class ClientAwareService
{
    /**
     * @var ExternalClientInterface $client
     */
    protected $client;

    /**
     * ClientAwareService constructor.
     * @param ExternalClientInterface $client
     */
    public function __construct(ExternalClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @return ExternalClientInterface
     */
    public function getClient(): ExternalClientInterface
    {
        return $this->client;
    }


}
