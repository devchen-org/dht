<?php


namespace DevChen\DHT\Chain;

class DHTClient
{
    /**
     * @var DHTserver
     */
    protected $dhtServer;

    /**
     * @var
     */
    protected $lastIp;

    /**
     * @var VirtualNode
     */
    protected $virtualNode;

    public function __construct(DHTserver $dhtServer)
    {
        $this->dhtServer = $dhtServer;
        $this->virtualNode = new VirtualNode();
    }

    /**
     * 处理接收到的find_node回复
     *
     * @param $msg
     * @param $address
     */
    public function response($msg, $address)
    {
        // 先检查接收到的信息是否正确
        if (!isset($msg['r']['nodes']) || !isset($msg['r']['nodes'][1]))
            return;
        // 对nodes数据进行解码
        $nodes = decode_nodes($msg['r']['nodes']);
        // 对nodes循环处理
        foreach ($nodes as $node) {
            // 将node加入到路由表中
            $this->append($node);
        }
        //c_log('路由表nodes数量 ' . count($this->dhtServer->table));
    }

    /**
     * 处理对端发来的请求
     *
     * @param $msg
     * @param $address
     */
    public function request($msg, $address)
    {
        switch ($msg['q']) {
            case 'ping':
                // 确认你是否在线
                // c_log('朋友' . $address[0] . '正在确认你是否在线');
                $this->onPing($msg, $address);
                break;
            case 'find_node':
                // 向服务器发出寻找节点的请求
                // c_log('朋友' . $address[0] . '向你发出寻找节点的请求');
                $this->onFindNode($msg, $address);
                break;
            case 'get_peers':
                // 处理get_peers请求
                // c_log('朋友' . $address[0] . '向你发出查找资源的请求');
                $this->onGetPeers($msg, $address);
                break;
            case 'announce_peer':
                // 处理announce_peer请求
                c_log('朋友' . $address[0] . '找到资源了通知你一声');
                $this->onAnnouncePeer($msg, $address);
                break;
            default:
                break;
        }
    }

    /**
     * @param $msg
     * @param $address
     */
    protected function onPing($msg, $address)
    {
        // 获取对端node id
        $id = $msg['a']['id'];
        // 生成回复数据
        $msg = [
            't' => $msg['t'],
            'y' => 'r',
            'r' => [
                'id' => $this->virtualNode->getNeighbor($id, $this->dhtServer->selfNodeId),
            ]
        ];
        // 将node加入路由表
        $this->append(new Node($id, $address[0], $address[1]));
        // 发送回复数据
        $this->dhtServer->sendResponse($msg, $address);
    }

    /**
     * @param $msg
     * @param $address
     */
    protected function onFindNode($msg, $address)
    {
        // 获取对端node id
        $id = $msg['a']['id'];
        // 生成回复数据
        $msg = [
            't' => $msg['t'],
            'y' => 'r',
            'r' => [
                'id' => $this->virtualNode->getNeighbor($id, $this->dhtServer->selfNodeId),
                'nodes' => encode_nodes($this->getNodes(16))
            ]
        ];
        // 将node加入路由表
        $this->append(new Node($id, $address[0], $address[1]));
        // 发送回复数据
        $this->dhtServer->sendResponse($msg, $address);
    }

    /**
     * @param $msg
     * @param $address
     */
    protected function onGetPeers($msg, $address)
    {
        // 获取info_hash信息
        $infohash = $msg['a']['info_hash'];
        // c_log(bin2hex($infohash));
        // 获取node id
        $id = $msg['a']['id'];
        // 生成回复数据
        $msg = [
            't' => $msg['t'],
            'y' => 'r',
            'r' => [
                'id' => $this->virtualNode->getNeighbor($id, $this->dhtServer->selfNodeId),
                'nodes' => encode_nodes($this->getNodes()),
                'token' => substr($infohash, 0, 2)
            ]
        ];
        // 将node加入路由表
        $this->append(new Node($id, $address[0], $address[1]));
        // 向对端发送回复数据
        $this->dhtServer->sendResponse($msg, $address);
    }

    /**
     * @param $msg
     * @param $address
     */
    protected function onAnnouncePeer($msg, $address)
    {
        $infohash = $msg['a']['info_hash'];
        $port = $msg['a']['port'];
        $token = $msg['a']['token'];
        $id = $msg['a']['id'];
        $tid = $msg['t'];

        // 验证token是否正确
        if (substr($infohash, 0, 2) != $token)
            return;

        if (isset($msg['a']['implied_port']) && $msg['a']['implied_port'] != 0) {
            $port = $address[1];
        }

        if ($port >= 65536 || $port <= 0) {
            return;
        }

        if ($tid == '') {
            //return;
        }

        // 生成回复数据
        $msg = [
            't' => $msg['t'],
            'y' => 'r',
            'r' => [
                'id' => $this->dhtServer->selfNodeId,
            ]
        ];

        if ($address[0] == $this->lastIp) {
            return;
        }
        $this->lastIp = $ip = $address[0];
        // 发送请求回复
        $this->dhtServer->sendResponse($msg, $address);

        c_log(bin2hex($infohash));
        return;
    }

    /**
     * 添加node到路由表
     *
     * @param Node $node
     * @return bool|int
     */
    public function append(Node $node)
    {
        // 检查node id是否正确
        if (!isset($node->nid[19]))
            return false;

        // 检查是否为自身node id
        if ($node->nid == $this->dhtServer->selfNodeId)
            return false;

        // 检查node是否已存在
        if (in_array($node, $this->dhtServer->table))
            return false;

        if ($node->port < 1 || $node->port > 65535)
            return false;

        // 如果路由表中的项达到200时, 删除第一项
        if (count($this->dhtServer->table) >= DHTserver::MAX_NODE_SIZE)
            array_shift($this->dhtServer->table);

        return array_push($this->dhtServer->table, $node);
    }

    protected function getNodes($len = 8)
    {
        if (count($this->dhtServer->table) <= $len) {
            return $this->dhtServer->table;
        }
        // shuffle($table);
        return array_slice($this->dhtServer->table, 0, $len);
    }
}