<?php

require "../../vendor/autoload.php";

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Container;

$configuration = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];

$c = new Container($configuration);

$app = new App($c);

$container = $app->getContainer();

$container['pdo'] = function () {
    return (new SQLConnection())->connect();
};

/**
 * ---------------------------------------------------------------------------------------------------------------------
 * USER SECTION
 * ---------------------------------------------------------------------------------------------------------------------
 */

$app->get('/api/user/{id}', function (Request $request, Response $response, $args) {

});

$app->get('/api/user/{id}/containers', function (Request $request, Response $response, $args) {

});

$app->get('/api/user/{id}/vms', function (Request $request, Response $response, $args) {

});

/**
 * ---------------------------------------------------------------------------------------------------------------------
 * DOCKER SECTION
 * ---------------------------------------------------------------------------------------------------------------------
 */

$app->post('/api/docker/create', function (Request $request, Response $response) {

});

$app->get('/api/docker/{id}/info', function (Request $request, Response $response, $args) {

});

$app->patch('/api/docker/{id}/power', function (Request $request, Response $response, $args) {

});

$app->delete('/api/docker/{id}/delete', function (Request $request, Response $response, $args) {

});

/**
 * ---------------------------------------------------------------------------------------------------------------------
 * DOCKER SECTION
 * ---------------------------------------------------------------------------------------------------------------------
 */

$app->post('/api/vm/create', function (Request $request, Response $response) {

});

$app->get('/api/vm/{id}/info', function (Request $request, Response $response, $args) {

});

$app->patch('/api/vm/{id}/power', function (Request $request, Response $response, $args) {

});

$app->delete('/api/vm/{id}/delete', function (Request $request, Response $response, $args) {

});

/**
 * ---------------------------------------------------------------------------------------------------------------------
 */

$app->run();