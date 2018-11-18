<?php

namespace AppBundle\Entity;

use DateTime;

/**
 * Class OrderPair
 * @package AppBundle\Entity
 */
class OrderPair
{
    /**
     * @var int $id
     */
    protected $id;

    /**
     * @var string $buyOrderId
     */
    protected $buyOrderId;

    /**
     * @var string $buyOrderExchange
     */
    protected $buyOrderExchange;

    /**
     * @var float $buyOrderAmountBtc
     */
    protected $buyOrderAmountBtc;

    /**
     * @var float $buyOrderAmountUsd
     */
    protected $buyOrderAmountUsd;

    /**
     * @var float $buyOrderPrice
     */
    protected $buyOrderPrice;

    /**
     * @var boolean $buyOrderOpen
     */
    protected $buyOrderOpen;

    /**
     * @var DateTime $buyOrderCreated
     */
    protected $buyOrderCreated;

    /**
     * @var string $sellOrderId
     */
    protected $sellOrderId;

    /**
     * @var string $sellOrderExchange
     */
    protected $sellOrderExchange;

    /**
     * @var float $sellOrderAmountBtc
     */
    protected $sellOrderAmountBtc;

    /**
     * @var float $sellOrderAmountUsd
     */
    protected $sellOrderAmountUsd;

    /**
     * @var float $sellOrderPrice
     */
    protected $sellOrderPrice;

    /**
     * @var boolean $sellOrderOpen
     */
    protected $sellOrderOpen;

    /**
     * @var DateTime $sellOrderCreated
     */
    protected $sellOrderCreated;

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
    public function getBuyOrderId(): string
    {
        return $this->buyOrderId;
    }

    /**
     * @param string $buyOrderId
     */
    public function setBuyOrderId(string $buyOrderId)
    {
        $this->buyOrderId = $buyOrderId;
    }

    /**
     * @return string
     */
    public function getBuyOrderExchange(): string
    {
        return $this->buyOrderExchange;
    }

    /**
     * @param string $buyOrderExchange
     */
    public function setBuyOrderExchange(string $buyOrderExchange)
    {
        $this->buyOrderExchange = $buyOrderExchange;
    }

    /**
     * @return float
     */
    public function getBuyOrderAmountBtc(): float
    {
        return $this->buyOrderAmountBtc;
    }

    /**
     * @param float $buyOrderAmountBtc
     */
    public function setBuyOrderAmountBtc(float $buyOrderAmountBtc)
    {
        $this->buyOrderAmountBtc = $buyOrderAmountBtc;
    }

    /**
     * @return float
     */
    public function getBuyOrderAmountUsd(): float
    {
        return $this->buyOrderAmountUsd;
    }

    /**
     * @param float $buyOrderAmountUsd
     */
    public function setBuyOrderAmountUsd(float $buyOrderAmountUsd)
    {
        $this->buyOrderAmountUsd = $buyOrderAmountUsd;
    }

    /**
     * @return float
     */
    public function getBuyOrderPrice(): float
    {
        return $this->buyOrderPrice;
    }

    /**
     * @param float $buyOrderPrice
     */
    public function setBuyOrderPrice(float $buyOrderPrice)
    {
        $this->buyOrderPrice = $buyOrderPrice;
    }

    /**
     * @return bool
     */
    public function isBuyOrderOpen(): bool
    {
        return $this->buyOrderOpen;
    }

    /**
     * @param bool $buyOrderOpen
     */
    public function setBuyOrderOpen(bool $buyOrderOpen)
    {
        $this->buyOrderOpen = $buyOrderOpen;
    }

    /**
     * @return string
     */
    public function getSellOrderId():? string
    {
        return $this->sellOrderId;
    }

    /**
     * @param string $sellOrderId
     */
    public function setSellOrderId(string $sellOrderId)
    {
        $this->sellOrderId = $sellOrderId;
    }

    /**
     * @return string
     */
    public function getSellOrderExchange():? string
    {
        return $this->sellOrderExchange;
    }

    /**
     * @param string $sellOrderExchange
     */
    public function setSellOrderExchange(string $sellOrderExchange)
    {
        $this->sellOrderExchange = $sellOrderExchange;
    }

    /**
     * @return float
     */
    public function getSellOrderAmountBtc():? float
    {
        return $this->sellOrderAmountBtc;
    }

    /**
     * @param float $sellOrderAmountBtc
     */
    public function setSellOrderAmountBtc(float $sellOrderAmountBtc)
    {
        $this->sellOrderAmountBtc = $sellOrderAmountBtc;
    }

    /**
     * @return float
     */
    public function getSellOrderAmountUsd():? float
    {
        return $this->sellOrderAmountUsd;
    }

    /**
     * @param float $sellOrderAmountUsd
     */
    public function setSellOrderAmountUsd(float $sellOrderAmountUsd)
    {
        $this->sellOrderAmountUsd = $sellOrderAmountUsd;
    }

    /**
     * @return float
     */
    public function getSellOrderPrice():? float
    {
        return $this->sellOrderPrice;
    }

    /**
     * @param float $sellOrderPrice
     */
    public function setSellOrderPrice(float $sellOrderPrice)
    {
        $this->sellOrderPrice = $sellOrderPrice;
    }

    /**
     * @return bool
     */
    public function isSellOrderOpen():? bool
    {
        return $this->sellOrderOpen;
    }

    /**
     * @param bool $sellOrderOpen
     */
    public function setSellOrderOpen(bool $sellOrderOpen)
    {
        $this->sellOrderOpen = $sellOrderOpen;
    }

    /**
     * @return DateTime
     */
    public function getBuyOrderCreated():? DateTime
    {
        return $this->buyOrderCreated;
    }

    /**
     * @param DateTime $buyOrderCreated
     */
    public function setBuyOrderCreated(DateTime $buyOrderCreated)
    {
        $this->buyOrderCreated = $buyOrderCreated;
    }

    /**
     * @return DateTime
     */
    public function getSellOrderCreated():? DateTime
    {
        return $this->sellOrderCreated;
    }

    /**
     * @param DateTime $sellOrderCreated
     */
    public function setSellOrderCreated(DateTime $sellOrderCreated)
    {
        $this->sellOrderCreated = $sellOrderCreated;
    }
}
