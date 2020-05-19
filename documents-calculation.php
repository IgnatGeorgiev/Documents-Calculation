<?php

include __DIR__ . '/classes/CSV_Handler.php';
include __DIR__ . '/classes/Currency.php';

class InvoiceCalculator
{
    public $currencies;
    public $data;
    public $totals;
    public $selected_currency;

    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * setData
     *
     * @param string $fileData
     * @return void
     */
    public function setData($fileData)
    {
        $csv_handler = new CSV_Handler($fileData);
        $this->data = $csv_handler->csv_array;
    }
    /**
     * getData
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * setCurrency
     *
     * @param array $currency_array
     * @return void
     */
    public function setCurrency($currency_array)
    {
        foreach ($currency_array as $currency) {
            $this->currencies[$currency->getCurrencyName()] = $currency->getExchangeRate($currency->getCurrencyName());
        }
    }
    /**
     * setSelectedCurrency
     *
     * @param string $selected_currency
     * @return void
     */
    public function setSelectedCurrency($selected_currency)
    {
        $this->selected_currency = $selected_currency;
    }
    /**
     * getTotals
     *
     * @param mixed $vat=''
     * @return array $total_customers
     */
    public function getTotals($vat='')
    {
        $customers = $this->getData();
        if (! empty($customers)) {
            foreach ($customers as $index => $line) {
                if (! empty($line['Parent document'])) {
                    $parent_document = $line['Parent document'];
                    $key = array_search($parent_document, array_column($customers, 'Document number'));
                    if ($key === false) {
                        throw new Exception('Error: Parent Document not found for ' . $line['Customer']);
                    }
                }
                $total = $line['Total'];
                $customer_name = $line['Customer'];
                if ($line['Type'] == '2') {
                    $total = $total * (-1);
                }
                $currency = $line['Currency'];
                $default_currency = array_search("1", $this->currencies);
                if ($currency !== $this->selected_currency) {
                    if ($this->selected_currency == $default_currency) {
                        $total = $total - ($total * 1 - floatval($this->currencies[$currency]));
                    }
                    $total = $total * floatval($this->currencies[$this->selected_currency]);
                }
                if (empty($total_customers[$customer_name])) {
                    $total_customers[$customer_name]['Vat'] = $line['Vat number'];
                    $total_customers[$customer_name]['Total'] = round($total, 2);
                } else {
                    $total_customers[$customer_name]['Total'] += round($total, 2);
                }
            }
        }
        foreach ($total_customers as $customer_name => $customer_data) {
            $total = strval($customer_data['Total']) . ' ' . $this->selected_currency;
            $total_customers[$customer_name]['Total'] = "";
            $total_customers[$customer_name]['Total'] = $total;
            if (! empty($vat) &&  $customer_data['Vat'] != $vat) {
                unset($total_customers[$customer_name]);
            }
        }

        return $total_customers;
    }
    /**
     * printOutput
     *
     * @param array $customers
     * @return void
     */
    public function printOutput($customers)
    {
        foreach ($customers as $customer_name => $customer_data) {
            echo $customer_name . ' - ' . $customer_data['Total'] . "\n";
        }
    }
}

$instance = new InvoiceCalculator();
$instance->setData($argv[2]);

$currencies = explode(',', $argv[3]);
foreach ($currencies as $currency) {
    $currency_explode = explode(':', $currency);
    $instance->setCurrency([
        new Currency($currency_explode[0], $currency_explode[1]),
    ]);
}
$instance->setSelectedCurrency($argv[4]);
$vat = '';
if (! empty($argv[5])) {
    $vat = explode("=", $argv[5])[1];
}
$test = $instance->getTotals($vat);
$instance->printOutput($test);
