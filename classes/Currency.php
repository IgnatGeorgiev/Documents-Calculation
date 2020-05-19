<?php

class Currency
{
    public $currency_name;
    public $exchange_rate;

    /**
     * __construct
     *
     * @param string $name
     * @param mixed $exchange_rate
     * @return void
     */
    public function __construct($name, $exchange_rate)
    {
        $this->currency_name = $name;
        $this->exchange_rate = $exchange_rate;
    }
    /**
     * getCurrencyName
     *
     * @return string $currency_name
     */
    public function getCurrencyName()
    {
        return $this->currency_name;
    }
    /**
     * getExchangeRate
     *
     * @param string $name
     * @return mixed $exchange_rate
     */
    public function getExchangeRate($name)
    {
        if ($name == $this->currency_name) {
            return $this->exchange_rate;
        } else {
            throw new Exception('Wrong currency.');
        }
    }
}
