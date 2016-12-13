<?php

use EasyWeChat\Foundation\Application as WxApp;
use Phalcon\Cache\Frontend\Data as FrontendData;
use Phalcon\DI\FactoryDefault;
use Phalcon\Logger\Adapter\File\Multiple as MultipleLogger;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Model\Metadata\Memory as MetaData;
use Phalcon\Mvc\Url as UrlProvider;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;

/**
 * The FactoryDefault Dependency Injector automatically register the right services providing a full stack framework
 */
$di = new FactoryDefault();

$di->set('config', $config);

$di->set('view', function() use ($config) {

	$view = new View();

	$view->setViewsDir(APP_PATH . $config->application->viewsDir);

	$view->registerEngines(array(
		".html" => function($view, $di) use ($config) {
			$volt = new VoltEngine($view, $di);
			$volt->setOptions(array(
				"compiledPath" => APP_PATH . "cache/volt/",
				"compiledSeparator" => "_",
			));

			$compiler = $volt->getCompiler();
			return $volt;
		},
        '.phtml' => 'Phalcon\Mvc\View\Engine\Php',
	));

	return $view;
}, true);

/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->set('db', function() use ($config) {
	$dbclass = '\Phalcon\Db\Adapter\Pdo\\' . $config->database->adapter;
	return new $dbclass(array(
		"host"     => $config->database->host,
		"username" => $config->database->username,
		"password" => $config->database->password,
		"dbname"   => $config->database->database
	));
});

/**
 * Memcached
 */

$di->set('modelsCache', function() use ($config) {
    $frontCache = new FrontendData(array("lifetime" => 86400));
    // Memcached connection settings
    $cache = new \Phalcon\Cache\Backend\Libmemcached($frontCache, array(
        'servers' => array(
            array(
                'host' => $config->memcached->host,
                'port' => $config->memcached->port,
                'weight' => 1
            ),
        ),
        "client" => array(
            Memcached::OPT_HASH => Memcached::HASH_CRC,
            Memcached::OPT_DISTRIBUTION => Memcached::DISTRIBUTION_CONSISTENT,
            Memcached::OPT_LIBKETAMA_COMPATIBLE => true,
        )
    ));
    
    return $cache;
});

/**
 * Redis
 */
$di->set('redis', function() use ($config) {
	//Connect to redis
    $redis = new Redis();
    $redis->connect($config->redis->host, $config->redis->port);

    //Create a Data frontend and set a default lifetime to 1 hour
    $frontend = new Phalcon\Cache\Frontend\Data(array(
        'lifetime' => $config->redis->lifetime
    ));

    //Create the cache passing the connection
    $cache = new  \Utils\Cache\Redis($frontend, array(
        'redis' => $redis
    ));

    return $cache;
});

/**
 * If the configuration specify the use of metadata adapter use it or use memory otherwise
 */
$di->set('modelsMetadata', function() {
	return new MetaData();
});

/**
 * Start the session the first time some component request the session service
 */
$di->set('session', function() use ($config) {
	$session = new Phalcon\Session\Adapter\Redis(array(
        'path'            => "tcp://". $config->redis->host .":". $config->redis->port ."?weight=1",
        'lifetime'        => 86400,
        'cookie_lifetime' => 86400
    ));
    
    $session->start();

    return $session;
});

$di->set('router', function () use ($config) {
    include '../app/config/routes.php';
    return $router;
});

//注册微信服务
$di->set('wxapp', function () use ($config) {
    $options = [
        'debug'  => $config->wechat->debug,
        'app_id' => $config->wechat->appid,
        'secret' => $config->wechat->app_secret,
        'token'  => $config->wechat->token,
        'log' => [
            'level' => 'debug',
            'file'  => LOGPATH . '/' . LOG_PREFIX . 'wechat.log',
        ],
        'payment' => [
            'merchant_id'        => $config->wechat->merchant_id,
            'key'                => $config->wechat->mer_key,
            'cert_path'          => CONF_PATH . $config->wechat->cert_path, // XXX: 绝对路径！！！！
            'key_path'           => CONF_PATH . $config->wechat->key_path,      // XXX: 绝对路径！！！！
        ],
    ];
    return new WxApp($options);
});

//debug日志
$di->set('logger', function() use ($config) {
    $options['prefix'] = LOG_PREFIX;
    return new MultipleLogger(LOGPATH, $options);
});