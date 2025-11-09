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

$task_id = isset($_GET['task_id']) ? (int)$_GET['task_id'] : 0;

if ($task_id <= 0) {
    echo json_encode(['error' => 'Invalid task ID']);
    http_response_code(400);
    exit();
}

$user_id = $_SESSION['user_id'];
$connection = db_connect();

// Security check: Ensure the user is assigned to this task
if (!is_user_assigned_to_task($connection, $user_id, $task_id)) {
    echo json_encode(['error' => 'Access denied']);
    http_response_code(403);
    exit();
}

$messages = get_conversation_messages($connection, $task_id);

echo json_encode($messages);

mysqli_close($connection);
?>
