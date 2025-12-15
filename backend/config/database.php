<?php
/**
 * Database Configuration File
 * 
 * This file handles the database connection using PDO (PHP Data Objects)
 * PDO provides a secure way to interact with databases and prevents SQL injection
 */

class Database {
    // Database credentials
    private $host = "localhost";        // Database server (usually localhost)
    private $db_name = "hotel_reservation_db";  // Database name
    private $username = "root";         // Database username (change in production)
    private $password = "";             // Database password (change in production)
    public $conn;                       // Connection object

    /**
     * Get Database Connection
     * 
     * This method creates and returns a PDO connection to the database
     * It uses try-catch to handle connection errors gracefully
     * 
     * @return PDO|null Returns PDO connection object or null on failure
     */
    public function getConnection() {
        $this->conn = null;

        try {
            // Create PDO connection with UTF-8 charset
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password,
                array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
            );
            
            // Set error mode to exception for better error handling
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
        } catch(PDOException $exception) {
            // Log error (in production, log to file instead of displaying)
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>
