<?php

require_once __DIR__ . '/configuration.php';

/**
 * Conexion PDO a MySQL:
 *   $connection = new Connection();
 *   $query = $connection->prepare($sql);
 */
class Connection extends PDO
{
    public function __construct()
    {
        $dsn = 'mysql:host=' . HOST_DB . ';dbname=' . DATABASE . ';charset=' . CHARSET;

        parent::__construct($dsn, USER_DB, PASSWORD_DB, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
}
