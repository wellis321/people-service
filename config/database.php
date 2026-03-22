<?php
/**
 * Database connection — returns a shared PDO instance.
 * Guarded against redeclaration (shared-auth/src/Database.php also defines this function).
 */
if (!function_exists('getDbConnection')) {
    function getDbConnection(): PDO
    {
        static $pdo = null;
        if ($pdo !== null) {
            return $pdo;
        }

        $host = getenv('DB_HOST') ?: '127.0.0.1';

        // Support host:port format used by MAMP (e.g. localhost:8889)
        $port = '3306';
        if (strpos($host, ':') !== false) {
            [$host, $port] = explode(':', $host, 2);
        } elseif (getenv('DB_PORT')) {
            $port = getenv('DB_PORT');
        }

        $name = getenv('DB_NAME') ?: 'people_service';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: '';

        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);

        return $pdo;
    }
}

// Class wrapper so models can call Database::getConnection()
if (!class_exists('Database')) {
    class Database
    {
        public static function getConnection(): PDO
        {
            return getDbConnection();
        }
    }
}
