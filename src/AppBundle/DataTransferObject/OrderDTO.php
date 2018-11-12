<?php

namespace AppBundle\DataTransferObject;

/**
 * Class OrderDTO
 * @package AppBundle\DataTransferObject\OrderDTO
 */
class OrderDTO
{
    const ORDER_TYPE_BUY = 1;
    const ORDER_TYPE_SELL = 2;

    /**
     * @var string $orderId
     */
    protected $orderId;

    /**
     * @var string $exchange
     */
    protected $exchange;

    /**
     * @var float $price
     */
    protected $price;

    /**
     * @var float $amountUSD
     */
    protected $amountUSD;

    /**
     * @var float $amountBTC
     */
    protected $amountBTC;

    /**
     * @var \DateTime
     */
    protected $created;

    /**
     * @var int $type
     */
    protected $type;

    /**
     * OrderDTO constructor.
     * @param string $orderId
     * @param string $exchange
     * @param float $price
     * @param float $amountUSD
     * @param float $amountBTC
     * @param \DateTime $created
     * @param int $type
     */
    public function __construct($orderId, $exchange, $price, $amountUSD, $amountBTC, \DateTime $created, $type)
    {
        $this->orderId = $orderId;
        $this->exchange = $exchange;
        $this->price = $price;
        $this->amountUSD = $amountUSD;
        $this->amountBTC = $amountBTC;
        $this->created = $created;
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }

    /**
     * @return string
     */
    public function getExchange(): string
    {
        return $this->exchange;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @return float
     */
    public function getAmountUSD(): float
    {
        return $this->amountUSD;
    }

    /**
     * @return float
     */
    public function getAmountBTC(): float
    {
        return $this->amountBTC;
    }

    /**
     * @return \DateTime
     */
    public function getCreated(): \DateTime
    {
        return $this->created;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }
}