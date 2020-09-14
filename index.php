<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Repository\InvitationRepository;
use App\Repository\UserRepository;
use App\Service\Auth;
use App\Service\Config;
use App\Service\Database;
use Klein\App;
use Klein\Klein;
use Klein\Request;
use Klein\Response;
use Klein\ServiceProvider;

$router = new Klein();

/*
 *  Loading services
 */
$router->respond(function (Request $request, Response $response, ServiceProvider $service, App $app) use ($router) {

    $app->register('config', function () {
        return new Config();
    });

    $app->register('database', function () use ($app) {
        $database = new Database($app->config);
        return $database->pdo();
    });

    $app->register('auth', function () use ($app) {
        return new Auth($app->database, $app->config->get('APP_KEY'));
    });
});

/*
 * Home/Welcome
 */
$router->respond('GET', '/', function (Request $request, Response $response, ServiceProvider $service, App $app) {
    return $response->json(['status' => 1, 'message' => 'welcome']);
});

/*
 *  ====================[ Authentication ]============================
 */

/**
 * check for authentication
 *
 * @param Request $request
 * @param App $app
 * @return array
 */
function checkAuthentication(Request $request, App $app)
{
    $token = $_SERVER['HTTP_AUTHORIZATION'] ?? $request->param('token');
    if (isset($token)) {
        if ($app->auth->isValid($token)) {
            return [1, ''];
        } else {
            return [0, 'authentication expired!'];
        }
    } else {
        return [0, 'token not provided!'];
    }
}

/*
 * Login endpoint
 */
$router->respond('POST', '/login', function (Request $request, Response $response, ServiceProvider $service, App $app) {
    $json = json_decode($request->body());
    if (isset($json->username) && isset($json->password)) {
        $token = $app->auth->login($json->username, $json->password);
        if (isset($token)) return $response->json(['status' => 1, 'token' => $token]);
        else {
            $response->code(401);
            return $response->json(['status' => 0, 'error' => 'authentication failed']);
        }
    }
    $response->code(400);
    return $response->json(['status' => 0, 'error' => 'Bad Request: parameter missing']);
});

/*
 *  ====================[ User Management ]============================
 */

/*
 * User list
 */
$router->respond('GET', '/users', function (Request $request, Response $response, ServiceProvider $service, App $app) {
    [$status, $err] = checkAuthentication($request, $app);
    if (!$status) {
        $response->code(401);
        return $response->json(['status' => 0, 'message' => $err]);
    }
    $userRepository = new UserRepository($app->database);
    $users = array_map(function ($each) {
        $array = $each->toArray();
        unset($array['modifiedAt']);
        unset($array['createdAt']);
        return $array;
    }, $userRepository->findAll());
    return $response->json(['status' => 1, 'data' => $users]);
});

/*
 * Adding new User
 */
$router->respond('POST', '/users', function (Request $request, Response $response, ServiceProvider $service, App $app) {
    [$status, $err] = checkAuthentication($request, $app);
    if (!$status) {
        $response->code(401);
        return $response->json(['status' => 0, 'message' => $err]);
    }
    $json = json_decode($request->body());
    if (isset($json->username) && isset($json->password) && isset($json->email)) {
        if (filter_var($json->email, FILTER_VALIDATE_EMAIL) && strlen($json->username)>6 && strlen($json->password)>6){
            $userRepository = new UserRepository($app->database);
            try {
                $user = $userRepository->insert($json->username, $json->password, $json->email)->toArray();
                return $response->json(['status' => 1, 'data' => $user]);
            } catch (PDOException $exception) {
                $response->code(400);
                return $response->json(['status' => 0, 'error' => $exception->getMessage()]);
            }
        }else{
            $response->code(400);
            return $response->json(['status' => 0, 'error' => 'Bad request: validation failed! username and password must be larger than 6 characters']);
        }
    }else{
        $response->code(400);
        return $response->json(['status' => 0, 'error' => 'Bad request: parameter missing!']);
    }
});

/*
 * get a User
 */
$router->respond('GET', '/users/[i:id]', function (Request $request, Response $response, ServiceProvider $service, App $app) {
    [$status, $err] = checkAuthentication($request, $app);
    if (!$status) {
        $response->code(401);
        return $response->json(['status' => 0, 'message' => $err]);
    }
    $userRepository = new UserRepository($app->database);
    $user = $userRepository->findById($request->id);
    if (isset($user)) return $response->json(['status' => 1, 'data' => $user->toArray()]);
    else{
        $response->code(404);
        return $response->json(['status'=> 0, 'error'=>'no user found']);
    }

});

/*
 * invite another user
 */
$router->respond('POST', '/users/[i:id]/invite', function (Request $request, Response $response, ServiceProvider $service, App $app) {
    [$status, $err] = checkAuthentication($request, $app);
    if (!$status) {
        $response->code(401);
        return $response->json(['status' => 0, 'message' => $err]);
    }
    $json = json_decode($request->body());
    if (isset($json->name)) {
        if (strlen($json->name)>5){
            $userRepository = new UserRepository($app->database);
            $invitationRepository = new InvitationRepository($app->database);
            try {
                $token = $_SERVER['HTTP_AUTHORIZATION'] ?? $request->param('token');
                $me = $app->auth->getUser($token);
                $user = $userRepository->findById($request->id);
                if (isset($user)){
                    $invitation = $invitationRepository->insert($json->name, $json->description??null, $me, $user);
                    return $response->json(['status' => 1, 'data' => $invitation->toArray()]);
                }
                $response->code(400);
                return $response->json(['status' => 0, 'error' => 'Bad request: user not found!']);
            } catch (PDOException $exception) {
                $response->code(400);
                return $response->json(['status' => 0, 'error' => $exception->getMessage()]);
            }
        }else{
            $response->code(400);
            return $response->json(['status' => 0, 'error' => 'Bad request: validation failed! name must be larger than 5 characters']);
        }
    }else{
        $response->code(400);
        return $response->json(['status' => 0, 'error' => 'Bad request: parameter missing!']);
    }
});

/*
 * Other User endpoints are not required by task
 */

/*
 *  ====================[ Invitation Management ]============================
 */

/*
 * get invitations I have been sent
 */
$router->respond('GET', '/invitations', function (Request $request, Response $response, ServiceProvider $service, App $app) {
    [$status, $err] = checkAuthentication($request, $app);
    if (!$status) {
        $response->code(401);
        return $response->json(['status' => 0, 'message' => $err]);
    }
    $invitationRepository = new InvitationRepository($app->database);
    $token = $_SERVER['HTTP_AUTHORIZATION'] ?? $request->param('token');
    $me = $app->auth->getUser($token);
    $invitations = $invitationRepository->findMine($me);
    $invitations = array_map(function ($each){
        return $each->toArray();
    }, $invitations);
    return $response->json(['status' => 1, 'data' => $invitations]);
});

/*
 * get invitations I have been invited
 */
$router->respond('GET', '/invitations/pending', function (Request $request, Response $response, ServiceProvider $service, App $app) {
    [$status, $err] = checkAuthentication($request, $app);
    if (!$status) {
        $response->code(401);
        return $response->json(['status' => 0, 'message' => $err]);
    }
    $invitationRepository = new InvitationRepository($app->database);
    $token = $_SERVER['HTTP_AUTHORIZATION'] ?? $request->param('token');
    $me = $app->auth->getUser($token);
    $invitations = $invitationRepository->findPending($me);
    $invitations = array_map(function ($each){
        return $each->toArray();
    }, $invitations);
    return $response->json(['status' => 1, 'data' => $invitations]);
});

/*
 * get an invitation
 */
$router->respond('GET', '/invitations/[i:id]', function (Request $request, Response $response, ServiceProvider $service, App $app) {
    [$status, $err] = checkAuthentication($request, $app);
    if (!$status) {
        $response->code(401);
        return $response->json(['status' => 0, 'message' => $err]);
    }
    $invitationRepository = new InvitationRepository($app->database);
    $invitation = $invitationRepository->findById($request->id);
    if (isset($invitation)) return $response->json(['status' => 1, 'data' => $invitation->toArray()]);
    else {
        $response->code(404);
        $response->json(['status'=>0, 'error'=>'No invitation found']);
    }
});

/*
 * cancel an invitation (remove it)
 */
$router->respond('DELETE', '/invitations/[i:id]', function (Request $request, Response $response, ServiceProvider $service, App $app) {
    [$status, $err] = checkAuthentication($request, $app);
    if (!$status) {
        $response->code(401);
        return $response->json(['status' => 0, 'message' => $err]);
    }
    $invitationRepository = new InvitationRepository($app->database);
    $token = $_SERVER['HTTP_AUTHORIZATION'] ?? $request->param('token');
    $me = $app->auth->getUser($token);
    if ($invitationRepository->delete($request->id, $me)) return $response->json(['status' => 1]);
    else {
        $response->code(403);
        $response->json(['status'=>0, 'error'=>'Not authorized']);
    }
});

/*
 * respond to an invitation (response: true/false)
 */
$router->respond('PUT', '/invitations/[i:id]', function (Request $request, Response $response, ServiceProvider $service, App $app) {
    [$status, $err] = checkAuthentication($request, $app);
    if (!$status) {
        $response->code(401);
        return $response->json(['status' => 0, 'message' => $err]);
    }
    $invitationRepository = new InvitationRepository($app->database);
    $token = $_SERVER['HTTP_AUTHORIZATION'] ?? $request->param('token');
    $me = $app->auth->getUser($token);
    $json = json_decode($request->body());
    if (isset($json->response) && is_bool($json->response)) {
        if ($invitationRepository->respond($request->id, $me, $json->response)) return $response->json(['status' => 1]);
        else {
            $response->code(403);
            $response->json(['status'=>0, 'error'=>'Not authorized']);
        }
    }else{
        $response->code(400);
        return $response->json(['status' => 0, 'error' => 'Bad request: parameter missing!']);
    }
});








$router->dispatch();