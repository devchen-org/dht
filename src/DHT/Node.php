<?php

namespace DevChen\DHT;

class Node
{
    /**
     * 保存node id
     * @var string
     */
    public $nid;

    /**
     * 保存IP地址
     * @var string
     */
    public $ip;

    /**
     * 保存端口号
     * @var integer
     */
    public $port;

    public function __construct($nid, $ip, $port)
    {
        $this->nid = $nid;
        $this->ip = $ip;
        $this->port = $port;
    }

    public function toArray()
    {
        return [
            'nid' => $this->nid,
            'ip' => $this->ip,
            'port' => $this->port
        ];
    }
}