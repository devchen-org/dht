<?php

if (!function_exists('bencode')) {
    function bencode(array $data)
    {
        return Rych\Bencode\Encoder::encode($data);
    }
}

if (!function_exists('bdecode')) {
    function bdecode($data)
    {
        return Rych\Bencode\Bencode::decode($data);
    }
}


if (!function_exists('console_log')) {
    function console_log($txt)
    {
        echo PHP_EOL . 'Log:' . date('Y-m-d H:i:s') . ': ';
        if (is_string($txt)) {
            echo $txt;
        } else {
            print_r($txt);
        }
        echo PHP_EOL;
    }
}

