<?php

namespace DevChen\DHT\Service;

use DevChen\DHT\Chain\DHTClient;
use DevChen\DHT\Chain\DHTserver;
use Swoole\Server;
use Swoole\Server\Task;
use Rych\Bencode\Bencode;
use Exception;

/**
 * 字节存储次序
 */
define('BIG_ENDIAN', pack('L', 1) === pack('N', 1));

class DHTService
{
    /**
     * 定时寻找节点时间间隔/毫秒
     * @var int
     */
    protected const AUTO_FIND_TIME = 3000;

    /**
     * @var Server
     */
    protected $swooleServer;

    /**
     * @var DHTserver
     */
    protected $dhtServer;

    /**
     * @var DHTClient
     */
    protected $dhtClient;


    public function __construct()
    {
        $this->swooleServer = new Server('0.0.0.0', '6882', SWOOLE_PROCESS, SWOOLE_SOCK_UDP);
        $this->config();

        $this->swooleServer->on('WorkerStart', [$this, 'workerStart']);
        $this->swooleServer->on('Packet', [$this, 'packet']);
        $this->swooleServer->on('Task', [$this, 'task']);
        $this->swooleServer->on('Finish', [$this, 'finish']);

        $this->dhtServer = new DHTserver($this->swooleServer);
        $this->dhtClient = new DHTClient($this->dhtServer);
    }

    protected function config()
    {
        $this->swooleServer->set([
            'daemonize' => config('daemonize'),
            /**
             * https://wiki.swoole.com/wiki/page/275.html
             */
            'worker_num' => config('worker_num'),

            /**
             * https://wiki.swoole.com/wiki/page/p-max_request.html
             */
            'max_request' => config('max_request'),

            /**
             * https://wiki.swoole.com/wiki/page/277.html
             * 2，固定模式，根据连接的文件描述符分配Worker。这样可以保证同一个连接发来的数据只会被同一个Worker处理
             */
            'dispatch_mode' => 2,

            /**
             * https://wiki.swoole.com/wiki/page/280.html
             */
            'log_file' => config('log_file'),

            /**
             * https://wiki.swoole.com/wiki/page/282.html
             */
            'max_conn' => config('max_conn'),

            /**
             * https://wiki.swoole.com/wiki/page/283.html
             */
            'heartbeat_check_interval' => config('heartbeat_check_interval'),

            /**
             * https://wiki.swoole.com/wiki/page/284.html
             */
            'heartbeat_idle_time' => config('heartbeat_idle_time'),

            /**
             * https://wiki.swoole.com/wiki/page/276.html
             */
            'task_worker_num' => config('task_worker_num'),

            /**
             * https://wiki.swoole.com/wiki/page/295.html
             */
            'task_max_request' => config('task_max_request'),
        ]);
    }

    public function start()
    {
        $this->swooleServer->start();
    }

    public function workerStart(Server $server, $worker_id)
    {
        swoole_timer_tick(self::AUTO_FIND_TIME, function ($timer_id) {
            $this->dhtServer->joinDHT();
        });
    }

    public function packet(Server $server, $data, $client_info)
    {
        if (strlen($data) == 0) {
            return false;
        }
        try {
            $msg = Bencode::decode($data);

            if (!isset($msg['y'])) {
                return false;
            }
            if ($msg['y'] == 'r') {
                // 如果是回复, 且包含nodes信息 添加到路由表
                if (array_key_exists('nodes', $msg['r'])) {
                    $this->dhtClient->response($msg, [$client_info['address'], $client_info['port']]);
                }
            } elseif ($msg['y'] == 'q') {
                // 如果是请求, 则执行请求判断
                $this->dhtClient->request($msg, [$client_info['address'], $client_info['port']]);
            }
        } catch (Exception $e) {
            c_log($e->getMessage());
        }
    }

    public function task(Server $server, Task $task)
    {

    }

    public function finish(Server $server, $task_id, $data)
    {

    }
}