<?php
require_once 'config.php';
require_once 'db_operations.php';

header('Content-Type: application/json');

$db = new DatabaseOperations($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['student_id'])) {
        $user = $db->getUserByStudentId($data['student_id']);
        
        if ($user) {
            // Log attendance
            $checkInPhoto = $data['photo'] ?? null;
            $db->logAttendance($user['id'], $checkInPhoto);
            
            echo json_encode([
                'success' => true,
                'user' => [
                    'student_id' => $user['student_id'],
                    'name' => $user['first_name'] . ' ' . $user['last_name']
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'User not found'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid request data'
        ]);
    }
}
?>