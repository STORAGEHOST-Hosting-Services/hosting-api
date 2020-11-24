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
require "../routes/users/activation/usersActivationModel.php";

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
use Users\Info;
use Users\Register;

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

        $containers = (new Info($id, $this->pdo))->listContainers();

        return $response->withStatus(200)->withJson($containers);
    } else {
        return $response->withStatus(400)->withJson('{"error":"Missing required parameter ID"}');
    }
});

$app->get('/api/user/{id}/vms', function (Request $request, Response $response, $args) {
    if (isset($args['id']) && (int)$args['id']) {
        $id = $args['id'];

        $containers = (new Info($id, $this->pdo))->listVms();

        return $response->withStatus(200)->withJson($containers);
    } else {
        return $response->withStatus(400)->withJson('{"error":"missing_parameter_id"}');
    }
});

$app->post('/api/user/create', function (Request $request, Response $response) {
    $body = $request->getParsedBody();

    if (isset($body) && !empty($body)) {
        $result = (new Register((array)$body, $this->pdo))->getFormData();
        if (is_array($result)) {
            return $response->withStatus(201)->withJson(json_encode($result));
        } else {
            return $response->withStatus(400)->withJson('{"error":' . json_encode($result) . '}');
        }
    } else {
        return $response->withStatus(400)->withJson('{"error":"missing_body"}');
    }
});

$app->get('/api/user/activation/email={email}&token={token}', function (Request $request, Response $response, $args) {
    $email = $args['email'];
    $token = $args['token'];

    $result = (new Users\usersActivationModel($this->pdo, $email, $token))->activateAccount();

    if ($result == "ok") {
        return $response->withStatus(200)->withJson('{"success":"account_activated"}');
    } elseif ($result == "already_enabled") {
        return $response->withStatus(200)->withJson('{"error":"account_already_enabled"}');
    } else {
        return $response->withStatus(400)->withJson('{"error":"bad_request"');
    }
});
$app->delete('/api/user/delete/email={email}', function (Request $request, Response $response, $args) {
    $email = $args['email'];

    $result = (new Users\Delete($this->pdo, $email))->deleteUser();

    if ($result == "ok") {
        return $response->withStatus(200)->withJson('{"success":"account_deleted"}');
    } elseif ($result == "not_exist") {
        return $response->withStatus(200)->withJson('{"error":"account_does_not_exist"}');
    } else {
        return $response->withStatus(400)->withJson('{"error":"bad_request"');
    }
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