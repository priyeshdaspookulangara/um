<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

// Basic error response function
function send_json_response($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit();
}

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    send_json_response(['error' => 'Unauthorized'], 401);
}

// Ensure it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(['error' => 'Invalid request method.'], 405);
}

// Get and validate input
$input = json_decode(file_get_contents('php://input'), true);
$order_id = isset($input['order_id']) ? (int)$input['order_id'] : 0;
$new_status = isset($input['status']) ? $input['status'] : '';

if ($order_id <= 0) {
    send_json_response(['error' => 'Invalid Order ID.'], 400);
}

// Validate the status against the allowed ENUM values
$allowed_statuses = ['pending', 'paid', 'partially', 'error'];
if (!in_array($new_status, $allowed_statuses)) {
    send_json_response(['error' => 'Invalid status value.'], 400);
}

$connection = db_connect();

// Sanitize inputs for the query
$order_id_safe = mysqli_real_escape_string($connection, $order_id);
$new_status_safe = mysqli_real_escape_string($connection, $new_status);

// Update the order status
$update_query = "UPDATE orders SET is_paid = '$new_status_safe' WHERE order_id = '$order_id_safe'";

if (mysqli_query($connection, $update_query)) {
    // Check if any row was actually updated
    if (mysqli_affected_rows($connection) > 0) {
        send_json_response(['success' => true, 'message' => "Order #$order_id status updated to '$new_status'."]);
    } else {
        send_json_response(['success' => false, 'message' => "Order #$order_id not found or status is already '$new_status'."], 404);
    }
} else {
    send_json_response(['error' => 'Database update failed: ' . mysqli_error($connection)], 500);
}

mysqli_close($connection);
?>