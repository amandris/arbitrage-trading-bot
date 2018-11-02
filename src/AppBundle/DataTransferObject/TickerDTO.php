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
     * @var float $ask
     */
    protected $ask;

    /**
     * @var float $bid
     */
    protected $bid;

    /**
     * @var \DateTime $timestamp
     */
    protected $timestamp;

    /**
     * TickerDTO constructor.
     * @param string $name
     * @param float $ask
     * @param float $bid
     * @param $timestamp
     */
    public function __construct($name, $ask, $bid, $timestamp)
    {
        $this->name = $name;
        $this->ask = $ask;
        $this->bid = $bid;
        $this->timestamp = $timestamp;
    }

    /**
     * @return float
     */
    public function getAsk(): float
    {
        return $this->ask;
    }

    /**
     * @return float
     */
    public function getBid(): float
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

    /**
     * @return mixed
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @return string
     */
    public function toString(){
        return str_pad($this->getName(), 10).' Ask:'.number_format($this->ask, 2).'  Bid:'.number_format($this->bid, 2);
    }
}