<?php

// File: DatabaseConnection.php

namespace App\Controllers;

use PDO;
use PDOException;

class DatabaseConnection
{
    private $db;

    public function __construct()
    {
        $this->db = $this->connect();
    }

    // Database connection method
    private function connect(): PDO
    {
        $host = env('DB_HOST', '127.0.0.1');
        $username = env('DB_USERNAME', 'dsgulvin');
        $password = env('DB_PASSWORD', '50504609');
        $dbname = 	env('DB_DATABASE', 'cse442_2025_spring_team_v_db');


        try {
            // Create PDO connection string
            $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8"; // Use the remote host and database name

            // Create a new PDO instance (connection to the database)
            $pdo = new PDO($dsn, $username, $password);

            // Set the PDO error mode to exception for better error handling
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $pdo;
        } catch (PDOException $e) {
            // Handle connection error
            die("Connection failed: " . $e->getMessage());
        }
    }

    // Getter for the PDO connection
    public function getConnection(): PDO
    {
        return $this->db;
    }
}