<?php

require_once __DIR__.'/../vendor/autoload.php';

use Phalcon\Di\FactoryDefault\Cli as CliDI,
    Phalcon\Cli\Console as ConsoleApp;

use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use Utils\FileLogger;

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/');

class SocketServer
{
    private $hiker_id;

    public static $instance;

    public function __construct() {
        // 创建swoole_http_server对象
        $server = new swoole_websocket_server("0.0.0.0", 9501);
        // 设置参数
        $server->set(array(
            'worker_num'               => 8,
            'max_request'              => 50000,
            'daemonize'                => 0,
            'heartbeat_check_interval' => 60,
            'heartbeat_idle_time'      => 600,
        ));
        
        // 绑定WorkerStart
        $server->on('workerstart' , array( $this , 'onWorkerStart'));
        // 绑定request
        $server->on('open', array( $this, 'onOpen'));
        // 监听消息
        $server->on('message', array( $this, 'onMessage'));
        // 监听关闭
        $server->on('close', array( $this, 'onClose' ));

        $server->start();
    }

    // WorkerStart回调
    public function onWorkerStart() {
        $config = new \Phalcon\Config\Adapter\Ini(ROOT_PATH . '/application/config/config.ini');
        
        include APP_PATH . 'application/config/constant.php';
        include APP_PATH . 'application/config/loader.php';   

        $this->di = new CliDI();

        $this->di->set('config', $config);

        $this->di->set('redis', function() use ($config) {
            $redis = new Redis();
            $redis->connect($config->redis->host, $config->redis->port);

            $frontend = new \Phalcon\Cache\Frontend\Data(array(
                'lifetime' => 86400
            ));

            //Create the cache passing the connection
            $cache = new \Phalcon\Cache\Backend\Redis($frontend, array(
                'redis' => $redis
            ));

            return $cache;
        });
    }

    public function onOpen(swoole_websocket_server $server, $frame) {
        
        echo "this server fd ". $frame->fd ." is open\n";
        $this->logger = new FileLogger( LOGLEVEL, LOGPATH, LOGFILE_SOC_DEBUG );
    }

    public function onMessage(swoole_websocket_server $server, $frame) {

        // echo "frame fd " . $frame->fd . "\n";

        $data = json_decode( $frame->data, true );

        // echo 'frame->data ' . json_encode($data) . "\n";
    }

    //写日志
    private function log_lalo( $data )
    {
        $this->logger->info('xxxx');
    }

    public function onClose($server, $fd) {
        echo "client {$fd} closed\n";
    }

    // 获取实例对象
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }
}

SocketServer::getInstance();