<?php

namespace AppBundle\Entity;

use DateTime;

/**
 * Class Status
 * @package AppBundle\Entity
 */
class Status
{
    /**
     * @var int $id
     */
    protected $id;

    /**
     * @var boolean $running
     */
    protected $running;

    /**
     * @var DateTime $startDate
     */
    protected $startDate;

    /**
     * @var float $thresholdUsd
     */
    protected $thresholdUsd;

    /**
     * @var float $orderValueBtc
     */
    protected $orderValueBtc;

    /**
     * @var int $tradingTimeMinutes
     */
    protected $tradingTimeMinutes;

    /**
     * @var float $addOrSubToOrderUsd;
     */
    protected $addOrSubToOrderUsd;

    /**
     * @var int $maxOpenOrders
     */
    protected $maxOpenOrders;

    /**
     * @var DateTime $orderPairLastUpdateDate
     */
    protected $orderPairLastUpdateDate;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isRunning(): bool
    {
        return $this->running;
    }

    /**
     * @param bool $running
     */
    public function setRunning(bool $running)
    {
        $this->running = $running;
    }

    /**
     * @return DateTime|null
     */
    public function getStartDate(): ?DateTime
    {
        return $this->startDate;
    }

    /**
     * @param DateTime|null $startDate
     */
    public function setStartDate(?DateTime $startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * @return float
     */
    public function getThresholdUsd(): float
    {
        return $this->thresholdUsd;
    }

    /**
     * @param float $thresholdUsd
     */
    public function setThresholdUsd(float $thresholdUsd)
    {
        $this->thresholdUsd = $thresholdUsd;
    }

    /**
     * @return float
     */
    public function getOrderValueBtc(): float
    {
        return $this->orderValueBtc;
    }

    /**
     * @param float $orderValueBtc
     */
    public function setOrderValueBtc(float $orderValueBtc)
    {
        $this->orderValueBtc = $orderValueBtc;
    }

    /**
     * @return int
     */
    public function getTradingTimeMinutes():? int
    {
        return $this->tradingTimeMinutes;
    }

    /**
     * @param int $tradingTimeMinutes
     */
    public function setTradingTimeMinutes(?int $tradingTimeMinutes)
    {
        $this->tradingTimeMinutes = $tradingTimeMinutes;
    }

    /**
     * @return float
     */
    public function getAddOrSubToOrderUsd(): float
    {
        return $this->addOrSubToOrderUsd;
    }

    /**
     * @param float $addOrSubToOrderUsd
     */
    public function setAddOrSubToOrderUsd(float $addOrSubToOrderUsd)
    {
        $this->addOrSubToOrderUsd = $addOrSubToOrderUsd;
    }

    /**
     * @return int
     */
    public function getMaxOpenOrders()
    {
        return $this->maxOpenOrders;
    }

    /**
     * @param int $maxOpenOrders
     */
    public function setMaxOpenOrders($maxOpenOrders)
    {
        $this->maxOpenOrders = $maxOpenOrders;
    }

    /**
     * @return DateTime
     */
    public function getOrderPairLastUpdateDate():? DateTime
    {
        return $this->orderPairLastUpdateDate;
    }

    /**
     * @param DateTime $orderPairLastUpdateDate
     */
    public function setOrderPairLastUpdateDate(?DateTime $orderPairLastUpdateDate)
    {
        $this->orderPairLastUpdateDate = $orderPairLastUpdateDate;
    }
}
