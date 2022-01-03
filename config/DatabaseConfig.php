<?php

use BadHabit\LoginManagement\Helper\DotEnv;

function getDatabaseConfig(): array
{

    $dotenv = new DotEnv(__DIR__ . "/../.env");
    $dotenv->load();

    $host = getenv("DB_HOST");
    $port = getenv("DB_PORT");
    $dbname = getenv("DB_NAME");
    $dbname_test = getenv("DB_NAME_TEST");
    $username = getenv("DB_USERNAME");
    $password = getenv("DB_PASSWORD");

    return [
        "database" => [
            "test" => [
                "url" => "mysql:host=$host:$port;dbname=$dbname_test",
                "username" => "$username",
                "password" => "$password",
            ],
            "production" => [
                "url" => "mysql:host=$host:$port;dbname=$dbname",
                "username" => "$username",
                "password" => "$password",
            ],
        ]
    ];
}

