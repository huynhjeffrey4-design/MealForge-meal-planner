<?php

// File: DatabaseConnection.php


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
        // For local testing with xampp
//        $host = 'localhost'; //
//        $port = '3307';
//        $dbname = 'cse442_2025_spring_team_v_db';
//        $username = 'root';
//        $password = '';

//         Database connection details for remote database on Aptitude (once deployed)
        $host = 'localhost'; // Remote host (or IP address of the server)
        $dbname = 'cse442_2025_spring_team_v_db'; // Remote database name
        $username = 'dsgulvin'; // Database username
        $password = '50504609'; // Database password

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