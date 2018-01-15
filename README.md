# dht
## 快速使用
- php>=7.0.0
- 安装[Swoole](https://www.swoole.com/) 扩展
- 使用 composer

```
git clone https://github.com/devchen-org/dht.git
cd dht
composer install

```
- 运行

```
php tests/artisan dht:spider

```

## infohash
可以获得get_peers请求的infohash

**但接收不到announce_peer请求**

```
Log:2018-01-15 23:59:19: onPing

Log:2018-01-15 23:59:19: onPing

Log:2018-01-15 23:59:19: onGetPeers FED5010E9F543DAC9870363257D96B26CC5C5E45

```

## torrent
### 获取种子文件信息
```
<?php

require __DIR__ . '/../vendor/autoload.php';

use DevChen\TorrentFile\Torrent;

$torrent = new Torrent('test.torrent');

var_dump($torrent->info());

```
