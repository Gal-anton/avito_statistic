<?php

namespace Test\System;

use Dotenv\Dotenv;
use Src\System\DatabaseConnector;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 **/
class DatabaseConnectorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @covers \Src\System\DatabaseConnector
     */
    public function testGetConnectionOk(){
        $dotenv = new DotEnv("/var/www/html/tests", ".env_valid");
        $dotenv->load();

        $dbConnection = (new DatabaseConnector())->getConnection();
        $this->assertInstanceOf(\PDO::class, $dbConnection);

    }

    /**
     * @covers \Src\System\DatabaseConnector
     */
    public function testGetConnectionWithException(){

        $this->expectException(\PDOException::class);

        $dotenv = new DotEnv("/var/www/html/tests", ".env_invalid");
        $dotenv->load();

        (new DatabaseConnector())->getConnection();
    }

}
