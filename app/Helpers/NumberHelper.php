<?php

if (!function_exists('terbilang')) {
    /**
     * Convert number to Indonesian words
     *
     * @param int|float $number
     * @return string
     */
    function terbilang($number)
    {
        $number = abs($number);
        $angka = [
            '', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima',
            'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'
        ];
        
        $result = '';
        
        if ($number < 12) {
            $result = ' ' . $angka[$number];
        } elseif ($number < 20) {
            $result = terbilang($number - 10) . ' Belas';
        } elseif ($number < 100) {
            $result = terbilang($number / 10) . ' Puluh' . terbilang($number % 10);
        } elseif ($number < 200) {
            $result = ' Seratus' . terbilang($number - 100);
        } elseif ($number < 1000) {
            $result = terbilang($number / 100) . ' Ratus' . terbilang($number % 100);
        } elseif ($number < 2000) {
            $result = ' Seribu' . terbilang($number - 1000);
        } elseif ($number < 1000000) {
            $result = terbilang($number / 1000) . ' Ribu' . terbilang($number % 1000);
        } elseif ($number < 1000000000) {
            $result = terbilang($number / 1000000) . ' Juta' . terbilang($number % 1000000);
        } elseif ($number < 1000000000000) {
            $result = terbilang($number / 1000000000) . ' Miliar' . terbilang($number % 1000000000);
        } elseif ($number < 1000000000000000) {
            $result = terbilang($number / 1000000000000) . ' Triliun' . terbilang($number % 1000000000000);
        }
        
        return trim($result);
    }
}

if (!function_exists('number_to_words')) {
    /**
     * Alias for terbilang function
     *
     * @param int|float $number
     * @return string
     */
    function number_to_words($number)
    {
        return terbilang($number);
    }
}
