<?php
require 'vendor/autoload.php';
require_once 'config.php';

$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true
    ]
]);

$container = $app->getContainer();

$container['getConn'] = function ($container) use ($app)
{
    return new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME,
        DB_USER,
        DB_PASS,
        [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"]
    );
};

$container['fetchAll'] = function ($container) use ($app)
{
    return PDO::FETCH_OBJ;
};

/*
    Instancias dos controllers no container
*/
$container['UrlController'] = function($container) use ($app) {
    return new DownsMaster\Controllers\UrlController($container);
};
$container['UserController'] = function($container) use ($app) {
    return new DownsMaster\Controllers\UserController($container);
};
$container['HomeController'] = function($container) use ($app) {
    return new DownsMaster\Controllers\HomeController($container);
};

/*
routes
*/
$app->get('/', 'HomeController:index')->setName('home');
$app->get('/urls','UrlController:urlList');
$app->get('/users','UserController:userList');

$app->get('/urls/{id}','UrlController:getUrl');
$app->get('/stats','UrlController:getStats');
$app->get('/users/{userId}/stats','UserController:getUserStats');
$app->get('/stats/{id}','UrlController:getUrlStats');

$app->post('/users/{userId}/urls','UrlController:addUrl');
$app->post('/users','UserController:addUser');
$app->delete('/urls/{id}','UrlController:deleteUrl');
$app->delete('/users/{id}','UserController:deleteUser');

$app->run();

