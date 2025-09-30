<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

// Basic error response function
function send_error($message) {
    http_response_code(400);
    echo json_encode(['error' => $message]);
    exit();
}

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Check if order_id is provided
if (!isset($_GET['order_id'])) {
    send_error('Order ID is missing.');
}

$order_id = (int)$_GET['order_id'];
if ($order_id <= 0) {
    send_error('Invalid Order ID.');
}

$connection = db_connect();
$order_id_safe = mysqli_real_escape_string($connection, $order_id);

$response = [
    'order' => null,
    'items' => [],
    'task' => null
];

// 1. Fetch main order details
$order_query = "SELECT id, customer_name, order_date, total_amount FROM orders WHERE id = '$order_id_safe'";
$order_result = mysqli_query($connection, $order_query);
if ($order_result && mysqli_num_rows($order_result) > 0) {
    $response['order'] = mysqli_fetch_assoc($order_result);
} else {
    mysqli_close($connection);
    send_error('Order not found.');
}

// 2. Fetch order items (details)
$items_query = "SELECT item_id, quantity FROM order_details WHERE order_id = '$order_id_safe'";
$items_result = mysqli_query($connection, $items_query);
if ($items_result) {
    while ($row = mysqli_fetch_assoc($items_result)) {
        $response['items'][] = $row;
    }
}

// 3. Fetch linked task details
$task_query = "
    SELECT t.id, t.task_type, t.description, t.conversation_status, u.full_name as assigned_to
    FROM tasks t
    JOIN staff_users u ON t.assigned_user_id = u.id
    WHERE t.order_id = '$order_id_safe'
    LIMIT 1
";
$task_result = mysqli_query($connection, $task_query);
if ($task_result && mysqli_num_rows($task_result) > 0) {
    $response['task'] = mysqli_fetch_assoc($task_result);
}

mysqli_close($connection);

echo json_encode($response);
?>