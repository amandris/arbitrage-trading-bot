<?php

namespace AppBundle\Entity;

use DateTime;

/**
 * Class Ticker
 * @package AppBundle\Entity
 */
class Ticker
{
    const BITSTAMP = 'bitstamp';
    const ITBIT = 'itbit';
    const KRAKEN = 'kraken';
    const OKCOIN = 'okcoin';
    const BINANCE = 'binance';
    const CEXIO = 'cexio';
    const BITTREX = 'bittrex';
    const QUADRIGACX = 'quadrigacx';

    /**
     * @var int $id
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var float
     */
    protected $ask;

    /**
     * @var float
     */
    protected $bid;

    /**
     * @var DateTime
     */
    protected $created;


    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return float
     */
    public function getAsk(): float
    {
        return $this->ask;
    }

    /**
     * @param float $ask
     */
    public function setAsk(float $ask)
    {
        $this->ask = $ask;
    }

    /**
     * @return float
     */
    public function getBid(): float
    {
        return $this->bid;
    }

    /**
     * @param float $bid
     */
    public function setBid(float $bid)
    {
        $this->bid = $bid;
    }

    /**
     * @return DateTime
     */
    public function getCreated(): DateTime
    {
        return $this->created;
    }

    /**
     * @param DateTime $created
     */
    public function setCreated(DateTime $created)
    {
        $this->created = $created;
    }
}
