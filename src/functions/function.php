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

if (!function_exists('decode_nodes')) {
    function decode_nodes($msg)
    {
        // 先判断数据长度是否正确
        if ((strlen($msg) % 26) != 0)
            return [];
        $n = [];
        // 每次截取26字节进行解码
        foreach (str_split($msg, 26) as $s) {
            // 将截取到的字节进行字节序解码
            $r = unpack('a20nid/Nip/np', $s);
            $n[] = new \DevChen\DHT\Chain\Node($r['nid'], long2ip($r['ip']), $r['p']);
        }
        return $n;
    }
}

if (!function_exists('c_log')) {
    function c_log(string $msg)
    {
        echo date('Y-m-d H:i:s') . ': ' . $msg . PHP_EOL;
    }
}
