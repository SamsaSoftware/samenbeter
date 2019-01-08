<?php
/**
 * Created by PhpStorm.
 * User: mihai.coditoiu
 * Date: 05.09.2015
 * Time: 18:02
 */

namespace Application\DatabaseConnection;


class Database
{

    private static $instance;

    private static $dbName;
    private static $username;
    private static $password;

    public function __construct($dbName, $username, $password)
    {
        self::$dbName = $dbName;
        self::$username = $username;
        self::$password = $password;
    }

    public static function getInstance()
    {
        //if (null === self::$instance) {
            if (self::$username != null && self::$password != null) {
                $instance = new \MongoClient(
                    "mongodb://localhost:27017",
                    array("username" => self::$username, "password" => self::$password)
                );
            } else {
                $instance = new \MongoClient(
                    "mongodb://localhost:27017"
                );
            }
       // }

        return $instance;
    }
}