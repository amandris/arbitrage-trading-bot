<?php

namespace AppBundle\Entity;

use DateTime;

/**
 * Class Balance
 * @package AppBundle\Entity
 */
class Balance
{
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
    protected $usd;

    /**
     * @var float
     */
    protected $btc;

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
    public function getUsd(): float
    {
        return $this->usd;
    }

    /**
     * @param float $usd
     */
    public function setUsd(float $usd)
    {
        $this->usd = $usd;
    }

    /**
     * @return float
     */
    public function getBtc(): float
    {
        return $this->btc;
    }

    /**
     * @param float $btc
     */
    public function setBtc(float $btc)
    {
        $this->btc = $btc;
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
