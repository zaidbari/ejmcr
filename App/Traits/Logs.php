<?php

namespace App\Traits;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

trait Logs
{
    /**
     * @param $type    type of error
     * @param string $message message to log
     * @param array  $data    data to log
     * 
     * @return void
     */
    public static function log( $type, string $message, array $data = [] ) : void
    {
        $path = $_SERVER['DOCUMENT_ROOT'] . '/logs/' . $type . '.log';
        $stream = new StreamHandler($path, $type);
        $firephp = new FirePHPHandler();

        $dateFormat = "d-M-Y, g:i a";

        // the default output format is "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"
        // we now change the default output format according to our needs.
        $output = "%datetime% > %message% %context% %extra%\n";

        // finally, create a formatter
        $formatter = new LineFormatter($output, $dateFormat);
        $stream->setFormatter($formatter);

        // Create the main logger of the app
        $logger = new Logger($type);
        $logger->pushHandler($stream);
        $logger->pushHandler($firephp);

        // Log the message
        $logger->$type($message, $data);
    }

    public static function dump($data)
    {
        echo "<pre>";
        var_dump($data);
        echo "</pre>";
    }
}
