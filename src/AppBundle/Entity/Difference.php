<?php

namespace AppBundle\Entity;

use DateTime;

/**
 * Class Difference
 * @package AppBundle\Entity
 */
class Difference
{
    /**
     * @var int $id
     */
    protected $id;

    /**
     * @var string
     */
    protected $exchangeSellName;

    /**
     * @var string
     */
    protected $exchangeBuyName;

    /**
     * @var string
     */
    protected $exchangeNames;

    /**
     * @var float
     */
    protected $ask;

    /**
     * @var float
     */
    protected $bid;

    /**
     * @var float
     */
    protected $difference;

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
    public function getExchangeSellName(): string
    {
        return $this->exchangeSellName;
    }

    /**
     * @param string $exchangeSellName
     */
    public function setExchangeSellName(string $exchangeSellName)
    {
        $this->exchangeSellName = $exchangeSellName;
    }

    /**
     * @return string
     */
    public function getExchangeBuyName(): string
    {
        return $this->exchangeBuyName;
    }

    /**
     * @param string $exchangeBuyName
     */
    public function setExchangeBuyName(string $exchangeBuyName)
    {
        $this->exchangeBuyName = $exchangeBuyName;
    }

    /**
     * @return string
     */
    public function getExchangeNames(): string
    {
        return $this->exchangeNames;
    }

    /**
     * @param string $exchangeNames
     */
    public function setExchangeNames(string $exchangeNames)
    {
        $this->exchangeNames = $exchangeNames;
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
     * @return float
     */
    public function getDifference(): float
    {
        return $this->difference;
    }

    /**
     * @param float $difference
     */
    public function setDifference(float $difference)
    {
        $this->difference = $difference;
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
