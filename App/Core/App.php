<?php

namespace App\Core;
use Dotenv\Dotenv;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

class App
{

    public static function run($dir)
    {
        /* ---------------- read environment variables from .env file --------------- */
        $dotenv = Dotenv::createImmutable($dir . "/");
        $dotenv->load();

        if (!file_exists($dir . '/fulltext')) mkdir($dir . '/fulltext', 0777, true);
        if (!file_exists($dir . '/files_html')) mkdir($dir . '/files_html', 0777, true);
        if (!file_exists($dir . 'uploads/cover')) mkdir($dir . 'uploads/cover', 0777, true);
        if (!file_exists($dir . 'uploads')) mkdir($dir . 'uploads', 0777, true);
        if (!file_exists('uploads/cover')) mkdir('uploads/cover', 0777, true);
        if (!file_exists('uploads/featured')) mkdir('uploads/featured', 0777, true);
        if (!file_exists('uploads/editors')) mkdir('uploads/editors', 0777, true);

        


        if ($_ENV['APP_DEBUG'] == "true") {
            $whoops = new Run;
            $handler = new PrettyPageHandler;

            /* ----------------- hide sensitive data in excepion handler ---------------- */
            $handler->blacklist('_ENV', 'DB_PASSWORD');
            $handler->blacklist('_ENV', 'DB_NAME');
            $handler->blacklist('_ENV', 'DB_USERNAME');
            $handler->blacklist('_ENV', 'DB_HOST');
            $handler->blacklist('_SERVER', 'DB_PASSWORD');
            $handler->blacklist('_SERVER', 'DB_NAME');
            $handler->blacklist('_SERVER', 'DB_USERNAME');
            $handler->blacklist('_SERVER', 'DB_HOST');
            $handler->blacklist('_SERVER', 'HTTP_COOKIE');

            /* -------------------- open exception source in vs-code -------------------- */
            $handler->setEditor('vscode');
            $whoops->pushHandler($handler);
            $whoops->register();
        }
    }
}
