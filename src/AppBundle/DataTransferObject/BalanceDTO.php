<?php

namespace AppBundle\DataTransferObject;

/**
 * Class BalanceDTO
 * @package AppBundle\DataTransferObject\BalanceDTO
 */
class BalanceDTO
{
    /** @var string $name */
    protected $name;

    /**
     * @var float $usd
     */
    protected $usd;

    /**
     * @var float $btc
     */
    protected $btc;

    /**
     * BalanceDTO constructor.
     * @param string $name
     * @param float $usd
     * @param float $btc
     */
    public function __construct($name, $usd, $btc)
    {
        $this->name = $name;
        $this->usd = $usd;
        $this->btc = $btc;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return float
     */
    public function getUsd(): float
    {
        return $this->usd;
    }

    /**
     * @return float
     */
    public function getBtc(): float
    {
        return $this->btc;
    }

    /**
     * @return string
     */
    public function toString(){
        return str_pad($this->getName(), 10).' BTC:'.number_format($this->btc, 8).'  USD:'.number_format($this->usd, 2);
    }
}