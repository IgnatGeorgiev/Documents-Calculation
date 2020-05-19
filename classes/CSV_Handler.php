<?php

class CSV_Handler
{
    public $csv_array = array();

    /**
     * __construct
     *
     * @param string $path
     * @return void
     */
    public function __construct($path)
    {
        $this->csv_array = $this->csv_to_array($path);
    }

    /**
     * csv_to_array
     *
     * @param string $file
     * @return array $csv
     */
    public function csv_to_array($file)
    {
        $csv = array_map('str_getcsv', file($file));
        array_walk($csv, function (&$a) use ($csv) {
            $a = array_combine($csv[0], $a);
        });
        array_shift($csv);
        return $csv;
    }
}
