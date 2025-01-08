<?php

namespace OpenExchangeRate;

class ExchangeRate
{
    public \DateTime $dateTime;
    public string $base;
    public $rates;

    public function __construct(int $timestamp, string $base, object $rates)
    {
        $this->dateTime = new \DateTime();
        $this->dateTime->setTimestamp($timestamp);

        $this->base = $base;

        $this->rates = $rates;
    }
}