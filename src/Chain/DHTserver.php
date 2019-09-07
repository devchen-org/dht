<?php


namespace DevChen\DHT\Chain;

use Swoole\Server;
use Rych\Bencode\Bencode;

class DHTserver
{
    /**
     * 保存node_id最大数量
     *
     * @var int
     */
    public const MAX_NODE_SIZE = 300;

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
    public $selfNodeId;

    /**
     *
     * 初始化路由表
     *
     * @var array
     */
    public $table = [];

    /**
     * @var virtualNode
     */
    protected $virtualNode;

    /**
     * @var Server
     */
    protected $swooleServer;

    public function __construct(Server $swooleServer)
    {
        $this->swooleServer = $swooleServer;

        $this->virtualNode = new VirtualNode();

        $this->selfNodeId = $this->virtualNode->getNodeId();
    }

    public function joinDHT()
    {
        if (count($this->table) == 0) {
            // 路由表为空 将自身伪造的ID 加入预定义的DHT网络
            foreach ($this->bootstrapNodes as $node) {
                $this->findNode([gethostbyname($node[0]), $node[1]]);
            }
        } else {
            $this->autoFindNode();
        }
    }

    protected function findNode($address, $id = null)
    {
        if (is_null($id)) {
            $mid = $this->virtualNode->getNodeId();
        } else {
            $mid = $this->virtualNode->getNeighbor($id, $this->selfNodeId); // 否则伪造一个相邻id
        }
        // 定义发送数据 认识新朋友的。
        $msg = [
            't' => $this->virtualNode->entropy(2),
            'y' => 'q',
            'q' => 'find_node',
            'a' => [
                'id' => $this->selfNodeId,
                'target' => $mid
            ]
        ];
        $this->sendResponse($msg, $address);
    }

    protected function autoFindNode()
    {
        //$wait = 1.0 / self::MAX_NODE_SIZE;
        while (count($this->table) > 0) {
            // 从路由表中删除第一个node并返回被删除的node
            $node = array_shift($this->table);
            // 发送查找find_node到node中
            $this->findNode([$node->ip, $node->port], $node->nid);
            //  usleep($wait);
        }
    }

    public function sendResponse($msg, $address)
    {
        if (!filter_var($address[0], FILTER_VALIDATE_IP)) {
            return false;
        }
        $ip = $address[0];
        $data = Bencode::encode($msg);
        $this->swooleServer->sendto($ip, $address[1], $data);
    }
}