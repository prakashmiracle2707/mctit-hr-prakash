<?php

if (!function_exists('format_currency')) {
    function format_currency($amount, $currency = 'INR') {
        return number_format($amount, 2) . ' ' . $currency;
    }
}

if (!function_exists('generate_uuid')) {
    function generate_uuid() {
        return (string) \Illuminate\Support\Str::uuid();
    }
}

?>