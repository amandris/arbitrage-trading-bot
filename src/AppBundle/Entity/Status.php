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
     * @var float $differenceUsd
     */
    protected $differenceUsd;


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
    public function getDifferenceUsd(): ?float
    {
        return $this->differenceUsd;
    }

    /**
     * @param float $differenceUsd
     */
    public function setDifferenceUsd(float $differenceUsd)
    {
        $this->differenceUsd = $differenceUsd;
    }
}
