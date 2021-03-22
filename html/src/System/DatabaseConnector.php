<?php

namespace Src\System;

class DatabaseConnector
{

    private ?\PDO $dbConnection = null;

    private string $connection;
    private string $host;
    private string $port;
    private string $db;
    private string $user;
    private string $pass;

    /**
     * DatabaseConnector constructor.
     */
    public function __construct()
    {
        $this->connection = getenv('DB_CONNECTION');
        $this->host       = getenv('DB_HOST');
        $this->port       = getenv('DB_PORT');
        $this->db         = getenv('DB_DATABASE');
        $this->user       = getenv('DB_USERNAME');
        $this->pass       = getenv('DB_PASSWORD');
    }

    /**
     * Get instance of DB connection
     * @return \PDO
     */
    public function getConnection(): \PDO
    {
        if (!isset($this->dbConnection)) {
            try {
                $this->dbConnection = new \PDO(
                    "$this->connection:host=$this->host;port=$this->port;charset=utf8mb4;dbname=$this->db",
                    $this->user,
                    $this->pass
                );
            } catch (\PDOException $e) {
                throw $e;
            }
        }

        return $this->dbConnection;
    }
}