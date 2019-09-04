<?php


namespace DevChen\DHT\Chain;

class DHTserver
{
    /**
     * 保存node_id最大数量
     *
     * @var int
     */
    protected const MAX_NODE_SIZE = 300;

    protected $bootstrapNodes = [
        ['router.bittorrent.com', 6881],
        ['dht.transmissionbt.com', 6881],
        ['router.utorrent.com', 6881],
    ];

    /**
     * 伪造设置自身node id
     *
     * @var
     */
    protected $selfNodeId;

    public function __construct()
    {

    }

    public function joinDHT(&$table)
    {
        if (count($table) == 0) {
            // 路由表为空 将自身伪造的ID 加入预定义的DHT网络
            foreach ($this->bootstrapNodes as $node) {
                $this->findNode([gethostbyname($node[0]), $node[1]]);
            }
        }
    }

    public function findNode($address, $id = null)
    {

    }
}