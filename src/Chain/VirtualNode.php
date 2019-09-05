<?php

namespace DevChen\DHT\Chain;

class VirtualNode
{
    /**
     * 生成随机字符串
     *
     * @param int $length
     * @return string
     */
    public function entropy($length = 20)
    {
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= chr(mt_rand(0, 255));
        }
        return $str;
    }

    /**
     * 生成一个node id
     *
     * @return string
     */
    public function getNodeId()
    {
        return sha1($this->entropy(), true);
    }

    /**
     *
     *
     * @param $target
     * @param $nid
     * @return string
     */
    public function getNeighbor($target, $nid)
    {
        return substr($target, 0, 10) . substr($nid, 10, 10);
    }
}