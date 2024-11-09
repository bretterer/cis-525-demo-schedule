<?php

namespace Bretterer\DemonstrationScheduling;

class Database {
    private $connection;

    public function __construct() {
        $this->connection = new \PDO('mysql:host=localhost;dbname=demo_scheduling', 'root', 'root');
    }


}