<?php
session_start();
require_once 'functions.php';

// Check if the user is logged in
if (!isset($_SESSION["user_id"])) {
    http_response_code(403);
    echo json_encode(['error' => 'User not logged in.']);
    exit();
}

// Get the request data
$data = json_decode(file_get_contents('php://input'), true);
$order_id = $data['order_id'] ?? null;

if (!$order_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Order ID is required.']);
    exit();
}

// Send the confirmation email
if (send_order_confirmation_email($order_id)) {
    echo json_encode(['success' => true, 'message' => 'Confirmation email sent successfully.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to send confirmation email.']);
}
