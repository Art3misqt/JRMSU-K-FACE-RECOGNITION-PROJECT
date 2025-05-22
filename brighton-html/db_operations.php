<?php
require_once 'config.php';

class DatabaseOperations {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function addUser($studentId, $firstName, $lastName, $faceEncoding, $photoPath) {
        $sql = "INSERT INTO users (student_id, first_name, last_name, face_encoding, photo_path) 
                VALUES (:student_id, :first_name, :last_name, :face_encoding, :photo_path)";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':student_id' => $studentId,
            ':first_name' => $firstName,
            ':last_name' => $lastName,
            ':face_encoding' => $faceEncoding,
            ':photo_path' => $photoPath
        ]);
    }
    
    public function logAttendance($userId, $checkInPhoto) {
        $sql = "INSERT INTO attendance_logs (user_id, check_in_photo) VALUES (:user_id, :check_in_photo)";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':user_id' => $userId,
            ':check_in_photo' => $checkInPhoto
        ]);
    }
    
    public function getUserByStudentId($studentId) {
        $sql = "SELECT * FROM users WHERE student_id = :student_id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':student_id' => $studentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getAllUsers() {
        $sql = "SELECT * FROM users";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function findStudentByFaceDescriptor($descriptor) {
        global $conn;
        
        // Convert descriptor to string for SQL query
        $descriptorStr = json_encode($descriptor);
        
        $stmt = $conn->prepare("SELECT * FROM students WHERE face_descriptor LIKE ?");
        $searchParam = "%".$descriptorStr."%";
        $stmt->bind_param("s", $searchParam);
        $stmt->execute();
        
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    function updateLastLogin($studentId) {
        global $conn;
        
        $stmt = $conn->prepare("UPDATE students SET last_login = NOW() WHERE id = ?");
        $stmt->bind_param("i", $studentId);
        $stmt->execute();
    }
}
?>