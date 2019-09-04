<?php

namespace DevChen\DHT\Command;

use DevChen\DHT\Service\DHTService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DHTSpider extends Command
{
    protected function configure()
    {
        $this->setName('dht:spider');
    }


    /**
     * @var DHTService
     */
    protected $dhtService;

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->dhtService = new DHTService();
        $this->dhtService->start();
    }

}