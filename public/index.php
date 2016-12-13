<?php
require_once __DIR__.'/../vendor/autoload.php';

use Phalcon\Config\Adapter\Ini as ConfigIni;
use Phalcon\Logger\Adapter\File\Multiple;
use Phalcon\Mvc\Application;

// header("Content-Type: text/html; charset=utf-8");  

define('APP_PATH', realpath('..') . '/');

$config = new ConfigIni(APP_PATH . 'app/config/config.ini');
include APP_PATH . 'app/config/constant.php';

try {
	include APP_PATH . 'app/config/loader.php';
	include APP_PATH . 'app/config/services.php';

	$application = new Application($di);
	echo $application->handle()->getContent();
} catch (Exception $e){
	$options['prefix'] = LOG_PREFIX;
    $logger = new Multiple(LOGPATH, $options);
    $logger->error("Message: {$e->getMessage()}, Trace:\n{$e->getTraceAsString()} ");

    $response = new Phalcon\Http\Response();
    $response->setStatusCode(404, "Not Found");
    $response->send();
}
