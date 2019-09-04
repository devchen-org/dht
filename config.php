<?php

return [
    /**
     * 是否后台守护进程
     */
    'daemonize' => false,

    /**
     * 全异步非阻塞服务器 worker_num配置为CPU核数的1-4倍即可。
     * 同步阻塞服务器，worker_num配置为100或者更高，具体要看每次请求处理的耗时和操作系统负载状况
     */
    'worker_num' => 1,

    /**
     * 配置Task进程的数量，配置此参数后将会启用task功能。
     * 所以Server务必要注册onTask、onFinish2个事件回调函数。如果没有注册，服务器程序将无法启动。
     * Task进程是同步阻塞的，配置方式与Worker同步模式一致
     * 最大值不得超过SWOOLE_CPU_NUM * 1000
     * task进程的数量 值越大 CPU占用越高
     */
    'task_worker_num' => 200,

    /**
     * 设置task进程的最大任务数。一个task进程在处理完超过此数值的任务后将自动退出。
     * 这个参数是为了防止PHP进程内存溢出。如果不希望进程自动退出可以设置为0。
     */
    'task_max_request' => 0,

    /**
     * max_request => 2000，此参数表示worker进程在处理完n次请求后结束运行。manager会重新创建一个worker进程。此选项用来防止worker进程内存溢出。
     * PHP代码也可以使用memory_get_usage来检测进程的内存占用情况，发现接近memory_limit时，调用exit()退出进程。manager进程会回收此进程，然后重新启动一个新的Worker进程。
     * onConnect/onClose不增加计数
     * 设置为0表示不自动重启。在Worker进程中需要保存连接信息的服务，需要设置为0.
     */
    'max_request' => 0,

    /**
     * log_file => '/data/log/swoole.log', 指定swoole错误日志文件。在swoole运行期发生的异常信息会记录到这个文件中。默认会打印到屏幕。
     * 注意log_file不会自动切分文件，所以需要定期清理此文件。观察log_file的输出，可以得到服务器的各类异常信息和警告。
     * log_file中的日志仅仅是做运行时错误记录，没有长久存储的必要。
     * 开启守护进程模式后(daemonize => true)，标准输出将会被重定向到log_file。在PHP代码中echo/var_dump/print等打印到屏幕的内容会写入到log_file文件
     */
    'log_file' => __DIR__ . '/runtime/logs/dht.log',


    /**
     * 服务器程序，最大允许的连接数，如max_connection => 10000, 此参数用来设置Server最大允许维持多少个TCP连接。超过此数量后，新进入的连接将被拒绝。
     * max_connection最大不得超过操作系统ulimit -n的值，否则会报一条警告信息，并重置为ulimit -n的值
     */
    'max_conn' => 4000,

    /**
     * 启用心跳检测，此选项表示每隔多久轮循一次，单位为秒。如 heartbeat_check_interval => 60，表示每60秒，遍历所有连接，
     * 如果该连接在120秒内（heartbeat_idle_time未设置时默认为interval的两倍），没有向服务器发送任何数据，此连接将被强制关闭。若未配置，则不会启用心跳, 该配置默认关闭。
     * Server并不会主动向客户端发送心跳包，而是被动等待客户端发送心跳。服务器端的heartbeat_check仅仅是检测连接上一次发送数据的时间，如果超过限制，将切断连接。
     * 被心跳检测切断的连接依然会触发onClose事件回调
     */
    'heartbeat_check_interval' => 5,
    'heartbeat_idle_time' => 10,


];