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
    // Add this method to your existing Database class in db.php
public function getVerificationStatsByTeam() {
    $sql = "
        SELECT 
            r.team,
            COUNT(r.id) as total_registrations,
            COALESCE(v.verified_count, 0) as verified_count
        FROM registrations r
        LEFT JOIN (
            SELECT team, COUNT(*) as verified_count
            FROM registrations reg
            INNER JOIN verification_status vs ON reg.id = vs.registration_id
            WHERE vs.verified = TRUE
            GROUP BY team
        ) v ON r.team = v.team
        GROUP BY r.team, v.verified_count
        ORDER BY r.team
    ";
    $stmt = $this->connection->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
    public function getAdminByUsername($username) {
        $stmt = $this->connection->prepare("SELECT id, username, password_hash, created_at FROM admin WHERE username = ?");
        $stmt->execute([$username]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null; // Return the row or null if not found
    }


}
?>