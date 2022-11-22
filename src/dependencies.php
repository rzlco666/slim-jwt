<?php

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Slim\App;
use Slim\Views\PhpRenderer;

return function (App $app) {
    $container = $app->getContainer();

    // view renderer
    $container['renderer'] = function ($c) {
        $settings = $c->get('settings')['renderer'];
        return new PhpRenderer($settings['template_path']);
    };

    // monolog
    $container['logger'] = function ($c) {
        $settings = $c->get('settings')['logger'];
        $logger = new Logger($settings['name']);
        $logger->pushProcessor(new UidProcessor());
        $logger->pushHandler(new StreamHandler($settings['path'], $settings['level']));
        return $logger;
    };

    // database
    $container['db'] = function ($c) {
        $settings = $c->get('settings')['db'];
        $server = $settings['driver'] . ":host=" . $settings['host'] . ";dbname=" . $settings['dbname'];
        $conn = new PDO($server, $settings["user"], $settings["pass"]);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $conn;
    };
};
