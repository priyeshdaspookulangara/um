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

$user_id = $_SESSION['user_id'];
$connection = db_connect();

$tasks = get_tasks_for_user($connection, $user_id);

echo json_encode($tasks);

mysqli_close($connection);
?>
