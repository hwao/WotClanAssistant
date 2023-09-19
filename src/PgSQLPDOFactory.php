<?php

namespace hwao\WotClanTools;

class PgSQLPDOFactory
{
    public static function create(string $host, string $database, string $user, string $password): \PDO
    {
        $conStr = sprintf("pgsql:host=%s;dbname=%s;user=%s;password=%s",
            $host,
            $database,
            $user,
            $password
        );

        $pdo = new \PDO($conStr);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }
}