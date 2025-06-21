<?php
use Monolog\Logger as MonoLogger;
use Monolog\Handler\StreamHandler;

class Logger {
    private static $logger;

    public static function getLogger(): MonoLogger {
        if (!self::$logger) {
            self::$logger = new MonoLogger('app_logger');
            self::$logger->pushHandler(new StreamHandler(__DIR__ . '/../logs/app.log', MonoLogger::DEBUG));
        }
        return self::$logger;
    }
}
