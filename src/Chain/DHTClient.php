<?php


namespace DevChen\DHT\Chain;

class DHTClient
{
    protected $dhtServer;

    public function __construct(DHTserver $dhtServer)
    {
        $this->dhtServer = $dhtServer;
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
        // '朋友'.$address[0].'在线'.PHP_EOL;
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
            case 'ping'://确认你是否在线
                echo '朋友' . $address[0] . '正在确认你是否在线' . PHP_EOL;
                break;
            case 'find_node': //向服务器发出寻找节点的请求
                echo '朋友' . $address[0] . '向你发出寻找节点的请求' . PHP_EOL;
                break;
            case 'get_peers':
                echo '朋友' . $address[0] . '向你发出查找资源的请求' . PHP_EOL;
                // 处理get_peers请求
                break;
            case 'announce_peer':
                echo '朋友' . $address[0] . '找到资源了 通知你一声' . PHP_EOL;
                // 处理announce_peer请求
                break;
            default:
                break;
        }
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
}