<?php

namespace DevChen\DHT\Chain;

class Node
{
    /**
     * 保存node id
     *
     * @var string
     */
    public $nid;

    /**
     * 保存IP地址
     *
     * @var string
     */
    public $ip;

    /**
     * 保存端口号
     *
     * @var integer
     */
    public $port;

    /**
     *
     * @param string $nid node id
     * @param string $ip IP地址
     * @param integer $port 端口号
     * @return void
     */
    public function __construct($nid, $ip, $port)
    {
        $this->nid = $nid;
        $this->ip = $ip;
        $this->port = $port;
    }

    /**
     * 将Node模型转换为数组
     * @return array 转换后的数组
     */
    public function toArray()
    {
        return [
            'nid' => $this->nid,
            'ip' => $this->ip,
            'port' => $this->port
        ];
    }
}