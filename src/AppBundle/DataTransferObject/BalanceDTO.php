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
     * @var int $usd
     */
    protected $usd;

    /**
     * @var int $btc
     */
    protected $btc;

    /**
     * BalanceDTO constructor.
     * @param string $name
     * @param int $usd
     * @param int $btc
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
     * @return int
     */
    public function getUsd(): int
    {
        return $this->usd;
    }

    /**
     * @return int
     */
    public function getBtc(): int
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