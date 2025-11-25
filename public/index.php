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
    header("Location: /WEBSYS_FINAL_PROJECT/public/error.php?code=404&msg=Controller+Not+Found");
    exit;
}

require_once $controllerFile;

if (!class_exists($controllerName)) {
    header("Location: /WEBSYS_FINAL_PROJECT/public/error.php?code=500&msg=Controller+Class+Missing");
    exit;
}

$controller = new $controllerName();

if (!method_exists($controller, $action)) {
    header("Location: /WEBSYS_FINAL_PROJECT/public/error.php?code=404&msg=Action+Not+Found");
    exit;
}

$controller->$action();
?>
