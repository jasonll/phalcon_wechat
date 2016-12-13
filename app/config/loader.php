<?php

$loader = new \Phalcon\Loader();

/**
 * We're a registering a set of directories taken from the configuration file
 */

$loader->registerDirs(
    array(
        APP_PATH . $config->application->controllersDir,
        APP_PATH . $config->application->modelsDir,
    )
)->registerNamespaces(
    array(
        'Models'      => APP_PATH . $config->application->modelsDir,
        'Controllers' => APP_PATH . $config->application->controllersDir,
    )
)->register();

