<?php

namespace App\Traits;

trait Model
{
    public static function db()
    {
        $connection = new \PDO('mysql:host='.  $_ENV['DB_HOST']. ';dbname='. $_ENV['DB_DATABASE'] .';charset=utf8', $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);
        return new \ClanCats\Hydrahon\Builder('mysql', function ($query, $queryString, $queryParameters) use ($connection) {
            $statement = $connection->prepare($queryString);
            $statement->execute($queryParameters);
            if ($query instanceof \ClanCats\Hydrahon\Query\Sql\FetchableInterface) {
                return $statement->fetchAll(\PDO::FETCH_ASSOC);
            } elseif ($query instanceof \ClanCats\Hydrahon\Query\Sql\Insert) {
                return $connection->lastInsertId();
            } else {
                return $statement->rowCount();
            }
        });
    }
}
