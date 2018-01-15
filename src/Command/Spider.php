<?php

namespace DevChen\Command;

use Symfony\Component\Console\Command\Command;
use swoole_server;
use swoole_process;
use DevChen\DHT\Compulsory;
use DevChen\DHT\Client;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;

class Spider extends Command
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
    protected $bootstrapNodes = [
        ['router.bittorrent.com', 6881],
        ['dht.transmissionbt.com', 6881],
        ['router.utorrent.com', 6881]
    ];

    /**
     * @var Client
     */
    protected $client;

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

    protected function configure()
    {
        $this->setName('dht:spider')
            ->setDescription('dht spider');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->compulsory = new Compulsory();

        $this->nid = $this->compulsory->getNodeId();

        $this->swooleServer = new swoole_server('0.0.0.0', '2555', SWOOLE_PROCESS, SWOOLE_SOCK_UDP);
        $this->swooleServer->set([
            'log_file' => 'error.log'
        ]);

        $this->client = new Client($this->swooleServer, $this->nid, $this->bootstrapNodes);

        $this->swooleServer->on('WorkerStart', function (swoole_server $swooleServer, $worker_id) {
            $this->swooleServer->tick(10000, function () {
                for ($i = 0; $i < 10; $i++) {
                    $swooleProcess = new swoole_process(function (swoole_process $swooleProcess) {
                        $this->client->autoFindNode();
                    });
                    $pid = $swooleProcess->start();
                    $workers[$pid] = $swooleProcess;
                    swoole_process::wait();
                }
            });
            $this->client->autoFindNode();
        });

        $this->swooleServer->on('Packet', function (swoole_server $swooleServer, $data, $client_info) {
            if (mb_strlen($data) == 0) {
                return false;
            }
            $msg = bdecode($data);
            if (empty($msg['y'])) {
                return false;
            }
            switch ($msg['y']) {
                case 'r':
                    // 如果是回复 response, 且包含nodes信息
                    if (array_key_exists('nodes', $msg['r'])) {
                        $this->client->responseAction($msg);
                    }
                    break;
                case 'q':
                    // 如果是请求 query, 则执行请求判断
                    $this->client->requestAction($msg, [$client_info['address'], $client_info['port']]);
                    break;
                case 'e':
                    // 如果是错误 error,
                    // console_log($msg['e']);
                    break;
            }
        });

        $this->swooleServer->start();
    }
}