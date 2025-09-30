<?php
session_start();
require_once 'db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("HTTP/1.1 401 Unauthorized");
    exit("User not authenticated.");
}

// Ensure it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("HTTP/1.1 405 Method Not Allowed");
    exit("Invalid request method.");
}

$user_id = $_SESSION['user_id'];
$task_id = isset($_POST['task_id']) ? (int)$_POST['task_id'] : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($task_id > 0 && $action) {
    $connection = db_connect();

    // Sanitize inputs
    $task_id_safe = mysqli_real_escape_string($connection, $task_id);
    $user_id_safe = mysqli_real_escape_string($connection, $user_id);

    // First, verify the task belongs to the logged-in user to prevent unauthorized updates
    $verify_query = "SELECT id FROM tasks WHERE id = '$task_id_safe' AND assigned_user_id = '$user_id_safe'";
    $verify_result = mysqli_query($connection, $verify_query);

    if ($verify_result && mysqli_num_rows($verify_result) == 1) {
        $update_query = '';

        if ($action === 'toggle_status') {
            // This logic allows toggling between 'PENDING' and 'DONE'
            $update_query = "UPDATE tasks SET conversation_status = IF(conversation_status = 'DONE', 'PENDING', 'DONE') WHERE id = '$task_id_safe'";
        } elseif ($action === 'mark_paid') {
            // This logic sets the payment status to 1 (paid)
            $update_query = "UPDATE tasks SET payment_made = 1 WHERE id = '$task_id_safe'";
        }

        if ($update_query) {
            mysqli_query($connection, $update_query);
        }
    }

    mysqli_close($connection);
}

// Redirect back to the dashboard
header("Location: dashboard.php");
exit();