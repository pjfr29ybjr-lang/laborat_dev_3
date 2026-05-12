<?php
/**
 * Simple Logger
 * weather-system/backend/utils/Logger.php
 */

class Logger {
    private static string $logDir = __DIR__ . '/../storage/logs';

    public static function info(string $message, array $context = []): void {
        self::write('INFO', $message, $context);
    }

    public static function error(string $message, array $context = []): void {
        self::write('ERROR', $message, $context);
    }

    public static function warning(string $message, array $context = []): void {
        self::write('WARNING', $message, $context);
    }

    private static function write(string $level, string $message, array $context): void {
        if (!is_dir(self::$logDir)) {
            mkdir(self::$logDir, 0755, true);
        }
        $date    = date('Y-m-d');
        $logFile = self::$logDir . "/app-$date.log";
        $ctx     = $context ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $line    = sprintf("[%s] [%s] %s%s\n", date('Y-m-d H:i:s'), $level, $message, $ctx);
        file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    }
}