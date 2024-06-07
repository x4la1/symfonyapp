<?php
declare(strict_types=1);

namespace App\Infrastructure\Database;

use App\Infrastructure\Config;

//uppercase

class ConnectionProvider
{
    public static function connectDatabase(): \PDO
    {
        return new \PDO(Config::getDatabaseDsn(), Config::getDatabaseUsername(), Config::getDatabasePassword());
    }
}