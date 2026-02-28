<?php
require_once 'config.php';

class Database {
    private $connection;
    
    public function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch(PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    // Check if roll number already exists
    public function checkRollNoExists($roll_no) {
        $stmt = $this->connection->prepare("SELECT * FROM registrations WHERE roll_no = ?");
        $stmt->execute([$roll_no]);
        return $stmt->fetch();
    }
    
    // Get total count of registrations
    public function getTotalRegistrations() {
        $stmt = $this->connection->query("SELECT COUNT(*) as count FROM registrations");
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    // Register new participant
    public function registerParticipant($name, $roll_no, $department, $email, $team) {
        $stmt = $this->connection->prepare(
            "INSERT INTO registrations (name, roll_no, department, email, team) VALUES (?, ?, ?, ?, ?)"
        );
        return $stmt->execute([$name, $roll_no, $department, $email, $team]);
    }
    
    // Get participant by roll number
    public function getParticipantByRollNo($roll_no) {
        $stmt = $this->connection->prepare("SELECT * FROM registrations WHERE roll_no = ?");
        $stmt->execute([$roll_no]);
        return $stmt->fetch();
    }
}
?>