<?php
$router = new \Phalcon\Mvc\Router(FALSE);

$router->add(
    "/",
    array(
    	"namespace"  => 'Controllers',
        "controller" => "index",
        "action"     => "index",
    )
);

$router->add("/:controller/:action/:params", array(
	'namespace'  => 'Controllers',
    "controller" => 1,
    "action"     => 2,
    "params"     => 3
));
