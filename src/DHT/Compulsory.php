<?php

namespace DevChen\DHT;
class Compulsory
{
    public function getNodeId()
    {
        // raw_output

        // 如果可选的 raw_output 参数被设置为 TRUE， 那么 sha1 摘要将以 20 字符长度的原始格式返回， 否则返回值是一个 40 字符长度的十六进制数字。

        return sha1($this->entropy(), true);
    }

    public function getNeighborId($target, $nid)
    {
        return mb_substr($target, 0, 10) . mb_substr($nid, 10, 10);
    }

    public function entropy($length = 20)
    {
        try {
            return random_bytes($length);
        } catch (\Exception $exception) {
            console_log($exception->getMessage());
        }
    }

    /**
     * 对nodes列表解码
     *
     * @param $data
     * @return array
     */
    public function decodeNodes($data)
    {
        // 先判断数据长度是否正确
        if ((mb_strlen($data) % 26) != 0)
            return [];

        $n = [];

        // 每次截取26字节进行解码
        foreach (str_split($data, 26) as $s) {
            // 将截取到的字节进行字节序解码
            $r = unpack('a20nid/Nip/np', $s);
            $n[] = new Node($r['nid'], long2ip($r['ip']), $r['p']);
        }
        return $n;
    }

    /**
     * 对nodes列表编码
     *
     * @param array $nodes
     * @return array|string
     */
    public function encodeNodes(array $nodes)
    {
        // 判断当前nodes列表是否为空
        if (count($nodes) == 0)
            return $nodes;

        $n = '';

        // 循环对node进行编码
        foreach ($nodes as $node) {
            $n .= pack('a20Nn', $node->nid, ip2long($node->ip), $node->port);
        }
        return $n;
    }
}