<?php
require_once 'config.php';

header('Content-Type: application/json');

try {
    // Validate input
    if (!isset($_POST['studentId']) || !isset($_POST['email']) || !isset($_POST['firstName']) || 
        !isset($_POST['lastName']) || !isset($_POST['program']) || !isset($_POST['yearLevel']) || 
        !isset($_POST['photo_data'])) {
        throw new Exception('Missing required fields');
    }

    // Sanitize input
    $studentId = $conn->real_escape_string($_POST['studentId']);
    $email = $conn->real_escape_string($_POST['email']);
    $firstName = $conn->real_escape_string($_POST['firstName']);
    $lastName = $conn->real_escape_string($_POST['lastName']);
    $program = $conn->real_escape_string($_POST['program']);
    $yearLevel = (int)$_POST['yearLevel'];
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    // Check if student ID already exists
    $stmt = $conn->prepare("SELECT id FROM students WHERE student_id = ?");
    $stmt->bind_param("s", $studentId);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception('Student ID already exists');
    }
    $stmt->close();

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM students WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception('Email already exists');
    }
    $stmt->close();

    // Process and save the photo
    $photo_data = $_POST['photo_data'];
    $photo_data = base64_decode($photo_data);
    
    // Create photos directory if it doesn't exist
    if (!file_exists('photos')) {
        mkdir('photos', 0777, true);
    }
    
    // Generate unique filename
    $photo_filename = $studentId . '_' . time() . '.jpeg';
    $photo_path = 'photos/' . $photo_filename;
    
    // Save the photo
    if (!file_put_contents($photo_path, $photo_data)) {
        throw new Exception('Failed to save photo');
    }

    // Insert student record
    $stmt = $conn->prepare("INSERT INTO students (student_id, email, first_name, last_name, program, year_level, photo_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $studentId, $email, $firstName, $lastName, $program, $yearLevel, $photo_path);
    
    if (!$stmt->execute()) {
        // Delete the uploaded photo if database insert fails
        unlink($photo_path);
        throw new Exception('Failed to save student record');
    }
    
    $stmt->close();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Registration successful'
    ]);

} catch (Exception $e) {
    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>