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
     * @var float $orderValueUsd
     */
    protected $orderValueUsd;

    /**
     * @var int $tradingTimeMinutes
     */
    protected $tradingTimeMinutes;

    /**
     * @var float $addOrSubToOrderUsd;
     */
    protected $addOrSubToOrderUsd;


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
     * @return float
     */
    public function getStartDate(): ?DateTime
    {
        return $this->startDate;
    }

    /**
     * @param float $startDate
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
    public function getOrderValueUsd(): float
    {
        return $this->orderValueUsd;
    }

    /**
     * @param float $orderValueUsd
     */
    public function setOrderValueUsd(float $orderValueUsd)
    {
        $this->orderValueUsd = $orderValueUsd;
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
}
