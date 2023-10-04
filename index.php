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

/**
 * =========================
 * Public routes
 * =========================
 **/
// Get the current URL.
$currentUrl = $_SERVER['REQUEST_URI'];

// Check if the current URL matches the pattern to be redirected and does not already contain the `mno` query parameter.
if (isset($_GET['mno'])) {

    // Construct the new URL.
    $newUrl = "/article/" . $_GET['mno'];

    // Send a 301 redirect header to the new URL.
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . $newUrl);
    exit();
}

$router->get('/', '\App\Controllers\HomeController@index');
$router->get('/current-issue', '\App\Controllers\IssueController@current_issue');
$router->get('/latest-issue', '\App\Controllers\IssueController@latest_issue');
$router->get('/issue', '\App\Controllers\IssueController@index');
$router->get('/archives', '\App\Controllers\IssueController@archives');
$router->get('/article/{mno}', '\App\Controllers\ArticlesController@index');
$router->get('/page/guide-for-authors', '\App\Controllers\PageController@gfa');
$router->get('/page/editorial-board', '\App\Controllers\PageController@eboard');
$router->get('/page/{slug}', '\App\Controllers\PageController@index');
$router->get('/404', '\App\Controllers\PageController@notFound');

/**
 * =========================
 * Authentication routes
 * =========================
 **/
$router->all('/auth/login', '\App\Controllers\Admin\AuthController@login');
$router->get('/auth/logout', '\App\Controllers\Admin\AuthController@logout');
$router->get('/admin', '\App\Controllers\Admin\AuthController@login');

/**
 * =========================
 * Admin routes
 * =========================
 **/
$router->before('GET|POST|DELETE', '/admin/.*', function() {
    if (!isset($_SESSION['user'])) {
        header('location: /auth/login');
        exit();
    }
});
$router->get('/admin/dashboard', '\App\Controllers\Admin\DashboardController@index');

/**
 * =========================
 * Journal Settings routes
 * =========================
 **/
$router->get('/admin/settings', '\App\Controllers\Admin\SettingsController@index');
$router->post('/admin/upload-cover', '\App\Controllers\Admin\SettingsController@uploadCover');
$router->post('/admin/update-settings', '\App\Controllers\Admin\SettingsController@updateSettings');
$router->post('/admin/showHide-settings', '\App\Controllers\Admin\SettingsController@showHide');

/**
 * =========================
 * Journal Featured Article routes
 * =========================
 **/
$router->get('/admin/featured-article', '\App\Controllers\Admin\FeaturedArticleController@index');
$router->post('/admin/upload-featured-image', '\App\Controllers\Admin\FeaturedArticleController@uploadFeatured');
$router->post('/admin/update-featured-article', '\App\Controllers\Admin\FeaturedArticleController@updateFeatured');

/**
 * =========================
 * Journal Pages routes
 * =========================
 **/
$router->get('/admin/pages', '\App\Controllers\Admin\PagesController@index');
$router->get('/admin/pages/create', '\App\Controllers\Admin\PagesController@create');
$router->get('/admin/pages/edit/{id}', '\App\Controllers\Admin\PagesController@edit');
$router->post('/admin/pages/update/{id}/{action}', '\App\Controllers\Admin\PagesController@put');

$router->post('/admin/pages/insert', '\App\Controllers\Admin\PagesController@insert');
$router->post('/admin/update-page/{id}', '\App\Controllers\Admin\PagesController@update');

/**
 * =========================
 * Journal Editors routes
 * =========================
 **/
$router->get('/admin/editors', '\App\Controllers\Admin\EditorController@index');
$router->get('/admin/editors/create', '\App\Controllers\Admin\EditorController@create');
$router->post('/admin/create-editor', '\App\Controllers\Admin\EditorController@insert');
$router->post('/admin/editors/upload-image/{id}', '\App\Controllers\Admin\EditorController@uploadEditorImage');
$router->post('/admin/editors/delete/{id}', '\App\Controllers\Admin\EditorController@delete');
$router->get('/admin/editors/edit/{id}', '\App\Controllers\Admin\EditorController@edit');
$router->post('/admin/update-editor/{id}', '\App\Controllers\Admin\EditorController@update');

$router->run();