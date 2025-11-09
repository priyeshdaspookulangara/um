<?php
session_start();
require_once '../db.php';
require_once '../functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION["user_id"])) {
    echo json_encode(['error' => 'User not authenticated']);
    http_response_code(401);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    http_response_code(405);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$task_id = isset($data['task_id']) ? (int)$data['task_id'] : 0;
$message = isset($data['message']) ? trim($data['message']) : '';

if ($task_id <= 0 || empty($message)) {
    echo json_encode(['error' => 'Task ID and message are required']);
    http_response_code(400);
    exit();
}

$user_id = $_SESSION['user_id'];
$connection = db_connect();

if (!is_user_assigned_to_task($connection, $user_id, $task_id)) {
    echo json_encode(['error' => 'Access denied to this task']);
    http_response_code(403);
    exit();
}

if (send_reply($connection, $task_id, $user_id, $message)) {
    echo json_encode(['success' => true, 'message' => 'Reply sent.']);
} else {
    echo json_encode(['error' => 'Failed to send reply']);
    http_response_code(500);
}

mysqli_close($connection);
?>
