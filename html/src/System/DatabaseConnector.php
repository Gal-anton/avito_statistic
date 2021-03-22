<?php

namespace Src\System;

class DatabaseConnector
{

    private static ?\PDO $dbConnection = null;

    /**
     * Get instance of DB connection
     * @return \PDO Database connection
     */
    public static function getConnection(): \PDO
    {
        if (!isset(self::$dbConnection)) {
            $connection = getenv('DB_CONNECTION');
            $host       = getenv('DB_HOST');
            $port       = getenv('DB_PORT');
            $db         = getenv('DB_DATABASE');
            $user       = getenv('DB_USERNAME');
            $pass       = getenv('DB_PASSWORD');
            try {
                self::$dbConnection = new \PDO(
                    "$connection:host=$host;port=$port;charset=utf8mb4;dbname=$db",
                    $user,
                    $pass
                );
            } catch (\PDOException $e) {
                throw $e;
            }
        }

        return self::$dbConnection;
    }
    private function __clone() {
    }
}