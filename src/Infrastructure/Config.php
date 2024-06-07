<?php
declare(strict_types=1);

namespace App\Infrastructure;
class Config
{

    public static function getDatabaseDsn(): string
    {
        return 'mysql:host=localhost;port=3306;dbname=test';
    }

    public static function getDatabaseUsername(): string
    {
        return 'root';
    }

    public static function getDatabasePassword(): string
    {
        return 'Ihoonigan-2005';
    }

    public static function getUploadsPath(): string
    {
        return 'C:/Users/Денис/Desktop/phpcourse/symfonylab/my_symfony_app/public/Uploads/';
    }

}