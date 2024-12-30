<?php
header('Content-Type: application/json');
require_once 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    $data = $_POST;
}

// Validate required fields
if (empty($data['name']) || empty($data['email']) || empty($data['message'])) {
    http_response_code(400);
    echo json_encode(['error' => 'All fields are required']);
    exit;
}

// Validate email
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email format']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO contacts (name, email, message, submission_date) VALUES (?, ?, ?, NOW())");
    $stmt->execute([
        $data['name'],
        $data['email'],
        $data['message']
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Message sent successfully']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error occurred']);
}