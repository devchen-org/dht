<?php

if (!function_exists('config')) {
    function config($key = '')
    {
        $conf = \Noodlehaus\Config::load(__DIR__ . '/../../config.php');
        if (func_num_args() === 0) {
            return $conf->all();
        }
        return $conf->get($key);
    }
}