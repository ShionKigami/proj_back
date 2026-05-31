<?php
function db_connect() {
    static $db = null;
    if ($db === null) {
        $host = conf('db_host');
        $name = conf('db_name');
        $user = conf('db_user');
        $pass = conf('db_psw');
        try {
            $db = new PDO("mysql:host=$host;dbname=$name;charset=utf8", $user, $pass, [
                PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }
    return $db;
}