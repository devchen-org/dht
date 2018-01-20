<?php

namespace DevChen\DHT;

use swoole_server;

class Client
{
    /**
     * 初始化路由器
     *
     * @var array
     */
    protected $table = [];

    /**
     * 长期在线node
     *
     * @var array
     */
    protected $bootstrapNodes = [];

    /**
     * 设置自身node id
     *
     * @var
     */
    protected $nid;

    /**
     * @var Compulsory
     */
    protected $compulsory;

    /**
     * @var swoole_server
     */
    protected $swooleServer;

    public function __construct(swoole_server $swooleServer, $nid, array $bootstrap_nodes)
    {
        $this->swooleServer = $swooleServer;
        $this->nid = $nid;
        $this->bootstrapNodes = $bootstrap_nodes;
        $this->compulsory = new Compulsory();

    }

    /**
     * 加入dht网络
     */
    public function joinDHT()
    {
        foreach ($this->bootstrapNodes as $bootstrapNode) {
            $this->findNode([gethostbyname($bootstrapNode[0]), $bootstrapNode[1]]);
        }
    }

    public function autoFindNode()
    {
        // 如果路由表中没有数据则先加入DHT网络
        if (count($this->table) == 0) {
            $this->joinDHT();
            return;
        }
        while (count($this->table) > 0) {
            // 从路由表中删除第一个node并返回被删除的node
            $node = array_shift($this->table);
            // 发送查找find_node到node中
            $this->findNode([$node->ip, $node->port], $node->nid);
        }
    }

    public function findNode(array $address, $id = null)
    {
        // 若未指定id则使用自身node id
        // 否则伪造一个相邻id
        $mid = empty($id) ? $this->nid :
            $this->compulsory->getNeighborId($id, $this->nid);
        // 定义发送数据
        $msg = [
            't' => $this->compulsory->entropy(2),
            'y' => 'q',
            'q' => 'find_node',
            'a' => [
                'id' => $this->nid,
                'target' => $mid
            ]
        ];
        $this->sendResponse($address, $msg);
    }

    public function sendResponse(array $address, $msg)
    {
        if (filter_var($address[0], FILTER_VALIDATE_IP) === false) {
            $ip = gethostbyname($address[0]);
            if (strcmp($ip, $address[0]) !== 0) {
                $address[0] = $ip;
            }
        }
        $this->swooleServer->sendto($address[0], $address[1], bencode($msg));
    }

    /**
     * 处理接收到的find_node回复
     *
     * @param array $msg
     * @return bool
     */
    public function responseAction(array $msg)
    {
        if (empty($msg['r']['nodes'])) {
            return false;
        }
        $nodes = $this->compulsory->decodeNodes($msg['r']['nodes']);
        foreach ($nodes as $node) {
            $this->append($node);
        }
        return true;
    }

    /**
     * 处理对端发来的请求
     *
     * @param array $msg
     * @param array $address
     * @return bool
     */
    public function requestAction(array $msg, array $address)
    {
        switch ($msg['q']) {
            case 'ping':
                $this->onPing($msg, $address);
                break;
            case 'find_node':
                $this->onFindNode($msg, $address);
                break;
            case 'get_peers':
                // 处理get_peers请求
                $this->onGetPeers($msg, $address);
                break;
            case 'announce_peer':
                // 处理announce_peer请求
                $this->onAnnouncePeer($msg, $address);
                break;
            default:
                return false;
        }
        return true;
    }

    public function onPing(array $msg, array $address)
    {
        // 获取对端node id
        $id = $msg['a']['id'];
        // 生成回复数据
        $data = [
            't' => $msg['t'],
            'y' => 'r',
            'r' => [
                'id' => $this->nid
            ]
        ];
        $this->append(new Node($id, $address[0], $address[1]));
        $this->sendResponse($address, $data);
        console_log('onPing ');

    }

    public function onFindNode(array $msg, array $address)
    {
        // 获取对端node id
        $id = $msg['a']['id'];
        // 生成回复数据
        $data = [
            't' => $msg['t'],
            'y' => 'r',
            'r' => [
                'id' => $this->nid,
                'nodes' => $this->compulsory->encodeNodes($this->getNodes(8)),
            ]
        ];

        // 将node加入路由表
        $this->append(new Node($id, $address[0], $address[1]));
        // 发送回复数据
        $this->sendResponse($address, $data);
        console_log('onFindNode ');
    }

    public function onGetPeers(array $msg, array $address)
    {
        // 获取info_hash信息
        $infohash = $msg['a']['info_hash'];
        // 获取node id
        $id = $msg['a']['id'];
        $token = $this->compulsory->entropy(4);
        // 生成回复数据
        $data = [
            't' => $msg['t'],
            'y' => 'r',
            'r' => [
                'id' => $this->nid,
                'nodes' => $this->compulsory->encodeNodes($this->getNodes(8)),
                'token' => $token
            ]
        ];

        // 将node加入路由表
        $this->append(new Node($id, $address[0], $address[1]));
        // 向对端发送回复数据
        $this->sendResponse($address, $data);
        console_log('onGetPeers ' . mb_strtoupper(bin2hex($infohash)));
    }

    public function onAnnouncePeer(array $msg, array $address)
    {
        // 获取infohash
        $infohash = $msg['a']['info_hash'];
        // 获取token
        $token = $msg['a']['token'];
        // 获取node id
        $id = $msg['a']['id'];

        file_put_contents('infohash.txt', PHP_EOL . 'onAnnouncePeer ' . mb_strtoupper(bin2hex($infohash)), FILE_APPEND);
        console_log('onAnnouncePeer ' . mb_strtoupper(bin2hex($infohash)));
        // 生成回复数据
        $data = [
            't' => $msg['t'],
            'y' => 'r',
            'r' => [
                'id' => $this->nid
            ]
        ];

        // 发送请求回复
        $this->sendResponse($address, $data);
    }

    /**
     * 添加node到路由表
     *
     * @param Node $node
     * @return bool
     */
    public function append(Node $node)
    {
        // 检查node id是否正确
        if (!isset($node->nid[19]))
            return false;
        // 检查是否为自身node id
        if ($node->nid == $this->nid)
            return false;
        // 检查node是否已存在
        if (in_array($node, $this->table))
            return false;

        // 如果路由表中的项达到200时, 删除第一项
        if (count($this->table) >= 200) {
            array_shift($this->table);
        }
        array_push($this->table, $node);
    }

    public function getNodes($length = 8)
    {
        if (count($this->table) <= $length)
            return $this->table;

        $nodes = [];

        for ($i = 0; $i < $length; $i++) {
            $nodes[] = $this->table[mt_rand(0, count($this->table) - 1)];
        }

        return $nodes;
    }
}