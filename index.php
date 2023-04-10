<?php
session_start();

// autoloader
use App\Core\App;
use Bramus\Router\Router;

require __DIR__ . '/vendor/autoload.php';

App::run(__DIR__);

if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle)
    {
        return $needle !== '' && mb_strpos($haystack, $needle) !== false;
    }
}


// create a new router
$router = new Router();
$router->setNamespace('\App\Controllers');

/**
 * =========================
 * Public routes
 * =========================
 **/
$router->get('/', 'HomeController@index');

$router->get('/current-issue', 'IssueController@current_issue');
$router->get('/latest-articles', 'IssueController@latest_issue');

$router->get('/log/request', 'ErrorLog@request');
$router->get('/log/error', 'ErrorLog@error');


$router->run();
