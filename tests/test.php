<?php

ini_set('date.timezone', 'Asia/Shanghai');

require __DIR__ . '/../vendor/autoload.php';

use DevChen\TorrentFile\Torrent;

$torrent = new Torrent('test.torrent');

var_dump($torrent->info());