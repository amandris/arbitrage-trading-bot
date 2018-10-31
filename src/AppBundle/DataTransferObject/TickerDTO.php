<?php

namespace AppBundle\DataTransferObject;

/**
 * Class TickerDTO
 * @package AppBundle\DataTransferObject\TickerDTO
 */
class TickerDTO
{
    /** @var string $name */
    protected $name;

    /**
     * @var int $ask
     */
    protected $ask;

    /**
     * @var int $bid
     */
    protected $bid;

    /**
     * TickerDTO constructor.
     * @param string $name
     * @param int $ask
     * @param int $bid
     */
    public function __construct($name, $ask, $bid)
    {
        $this->name = $name;
        $this->ask = $ask;
        $this->bid = $bid;
    }

    /**
     * @return int
     */
    public function getAsk(): int
    {
        return $this->ask;
    }

    /**
     * @return int
     */
    public function getBid(): int
    {
        return $this->bid;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}