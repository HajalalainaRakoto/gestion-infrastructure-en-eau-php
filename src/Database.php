<?php

namespace App;

use PDOException;
use PDO;

class Database {

    private $db;

    public function __construct() {
        return $this;
    }

    public function getConnection() {
        $db_host = $_ENV['DB_HOST'];
        $db_user = $_ENV['DB_USER'];
        $db_pass = $_ENV['DB_PASS'];
        $db_name = $_ENV['DB_NAME'];

        $dsn = "mysql:host={$db_host};dbname={$db_name}";
        try {
            if (!$this->db) {
                $pdo = new PDO($dsn, $db_user, $db_pass);

                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $pdo->setAttribute( PDO::ATTR_EMULATE_PREPARES, false );
                $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

                $this->db = $pdo;
            }
            return $this->db;

        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    public function queryExec(string $statement, ?array $options = []) {
        $db = $this->getConnection();

        if (empty($options)) {
            // s'il n'y a pas de $options on utilise une requete simple
            return $db->query($statement);
        } else {
            // sinon on utilise une requete prepare
            $query = $db->prepare($statement);
            $query->execute($options);
            return $query;
        }

    }

}