<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/env.php';


$route = $_GET['route'] ?? 'auth/login';
$parts = explode('/', $route);
$controllerName = ucfirst($parts[0]) . 'Controller';
$action = $parts[1] ?? 'index';


$controllerFile = __DIR__ . '/../src/controllers/' . $controllerName . '.php';
if (!file_exists($controllerFile)) {
http_response_code(404);
echo "Controller not found";
exit;
}


require_once $controllerFile;


if (!class_exists($controllerName)) {
echo "Controller class missing";
exit;
}


$controller = new $controllerName();
if (!method_exists($controller, $action)) {
echo "Action not found";
exit;
}


$controller->$action();
?>