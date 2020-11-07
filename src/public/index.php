<?php

require "../../vendor/autoload.php";
require "../config/SQLConnection.php";

/**
 * Users
 */
require "../routes/users/register/register.php";
require "../routes/users/login/login.php";
require "../routes/users/delete/delete.php";
require "../routes/users/info/info.php";

/**
 * Containers
 */
require "../routes/containers/create/create.php";
require "../routes/containers/info/info.php";
require "../routes/containers/power/power.php";
require "../routes/containers/delete/delete.php";

/**
 * VMs
 */
require "../routes/vms/create/create.php";
require "../routes/vms/info/info.php";
require "../routes/vms/power/power.php";
require "../routes/vms/delete/delete.php";

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

$app->get('/api', function (Request $request, Response $response) {
    $response->getBody()->write("Hello");
    return $response;
});

/**
 * ---------------------------------------------------------------------------------------------------------------------
 * USER SECTION
 * ---------------------------------------------------------------------------------------------------------------------
 */

$app->get('/api/user/{id}', function (Request $request, Response $response, $args) {
    if (isset($args['id'])) {
        $id = $args['id'];
        var_dump($id);
    } else {
        $response->withStatus(400)->withJson('{"error":"Missing required parameter ID"}');
    }

    return $response;
});

$app->get('/api/user/{id}/containers', function (Request $request, Response $response, $args) {
    if (isset($args['id']) && (int)$args['id']) {
        $id = $args['id'];

        $containers = (new \Users\Info($id, $this->pdo))->listContainers();

        return $response->withStatus(200)->withJson($containers);
    } else {
        return $response->withStatus(400)->withJson('{"error":"Missing required parameter ID"}');
    }
});

$app->get('/api/user/{id}/vms', function (Request $request, Response $response, $args) {
    if (isset($args['id']) && (int)$args['id']) {
        $id = $args['id'];

        $containers = (new \Users\Info($id, $this->pdo))->listVms();

        return $response->withStatus(200)->withJson($containers);
    } else {
        return $response->withStatus(400)->withJson('{"error":"Missing required parameter ID"}');
    }
});

$app->post('/api/user/create', function (Request $request, Response $response) {

});

/**
 * ---------------------------------------------------------------------------------------------------------------------
 * DOCKER SECTION
 * ---------------------------------------------------------------------------------------------------------------------
 */

$app->post('/api/docker/create', function (Request $request, Response $response) {

});

$app->get('/api/docker/{id}/info', function (Request $request, Response $response, $args) {
    if (isset($args['id']) && (int)$args['id']) {
        $id = $args['id'];

        $containers = (new \Containers\Info($id, $this->pdo))->listContainers();

        return $response->withStatus(200)->withJson($containers);
    } else {
        return $response->withStatus(400)->withJson('{"error":"Missing required parameter ID"}');
    }
});

$app->patch('/api/docker/{id}/power', function (Request $request, Response $response, $args) {

});

$app->delete('/api/docker/{id}/delete', function (Request $request, Response $response, $args) {

});

/**
 * ---------------------------------------------------------------------------------------------------------------------
 * VM SECTION
 * ---------------------------------------------------------------------------------------------------------------------
 */

$app->post('/api/vm/create', function (Request $request, Response $response) {
    $body = $request->getParsedBody();

    if (isset($body) && !empty($body)) {
        $result = (new \Vms\Create((array)$body, $this->pdo))->validateData();
        if (is_array($result)) {
            $new_vm_data = (new \Vms\Create((array)$result, $this->pdo))->createVm();
            return $response->withStatus(201)->withJson('{"success":' . $new_vm_data . ' }');
        } else {
            return $response->withStatus(400)->withJson('{"error": "' . $result . '"}');
        }
    } else {
        return $response->withStatus(400)->withJson('{"error":"Missing body"}');
    }
});

$app->get('/api/vm/{id}/info', function (Request $request, Response $response, $args) {
    if (isset($args['id']) && (int)$args['id']) {
        $id = $args['id'];

        $containers = (new \Vms\Info($id, $this->pdo))->listVms();

        return $response->withStatus(200)->withJson($containers);
    } else {
        return $response->withStatus(400)->withJson('{"error":"Missing required parameter ID"}');
    }
});

$app->patch('/api/vm/{id}/power', function (Request $request, Response $response, $args) {

});

$app->delete('/api/vm/{id}/delete', function (Request $request, Response $response, $args) {

});

/**
 * ---------------------------------------------------------------------------------------------------------------------
 */

$app->run();