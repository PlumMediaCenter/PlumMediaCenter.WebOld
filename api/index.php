<?php

include 'Db.php';
include 'ExtendedPDO.php';
include 'web/Response.php';
include 'web/ResponseError.php';
include 'web/JsonResponse.php';
include 'lib/altorouter/AltoRouter.php';
include '../config.php';
include 'AppConfig.php';
include 'lib/jakesmith/http_build_url.php';

ob_start();
//enable CORS
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, X-Requested-With');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT');
header('Access-Control-Max-Age: 86400');

//if OPTIONS method, then exit
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

//elevate all errors to exceptions
set_error_handler(function($errno, $errstr, $errfile, $errline, array $errcontext) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});
//handle fatal errors
register_shutdown_function(function() {
    //get the error
    if (($error = error_get_last())) {
        ob_end_clean();

        $error = (object) $error;
        $responseError = new ResponseError();
        $responseError->file = $error->file;
        $responseError->line = $error->line;
        $messageParts = explode('Stack trace:', $error->message);
        $responseError->message = $messageParts[0];
        $responseError->traceString = isset($messageParts[1]) ? $messageParts[1] : null;
        try {
            $trace = explode('#', $responseError->traceString);
            if (count($trace) === 1 && $trace[0] === '') {
                $trace = [];
            }
            $responseError->trace = $trace;
        } catch (Exception $e) {
            
        }

        $result = new JsonResponse($responseError, 500);
        //render the error
        $result->render();
    }
});

$router = new AltoRouter();
$router->setBasePath(AppConfig::GetBasePath());

//include all controllers
$controllers = scandir(__DIR__ . '/controllers');
foreach ($controllers as $controller) {
    if ($controller == '.' || $controller == '..') {
        continue;
    }
    require "controllers/$controller";
}

$response = null;

$match = $router->match();
if ($match) {
    try {
        $target = $match['target'];
        if (is_string($target)) {
            $targetParts = explode('#', $target);
            $className = $targetParts[0];
            $classMethod = $targetParts[1];
            //construct a new instance of the controller
            $controller = new $className();

            $result = call_user_func_array([$controller, $classMethod], $match['params']);
        } else {
            $result = call_user_func_array($target, $match['params']);
        }
    } catch (NotAuthenticatedException $e) {
        $result = new JsonResponse(new ResponseError($e), 401);
    } catch (Exception $e) {
        //an error occurred. return a json response with a 500 error code so the frontend
        //can correctly handle the error
        $result = new JsonResponse(new ResponseError($e), 500);
    }
    if (is_a($result, 'Response')) {
        $response = $result;
    } else {
        $response = new JsonResponse($result);
    }
} else {
    try {
        throw new Exception('Resource not found');
    } catch (Exception $ex) {
        $response = new JsonResponse(new ResponseError($ex), 404);
    }
}
$response->render();
ob_end_flush();


