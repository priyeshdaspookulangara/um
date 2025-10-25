<?php
require_once 'db.php';

/**
 * Finds the staff user with the minimum number of active tasks and assigns a new task to them.
 *
 * @param string $task_type The type of the task.
 * @param string $description A description of the task.
 * @param int|null $order_id The ID of a linked order, if any.
 * @return bool True on success, false on failure.
 */
function assign_task_to_user($task_type, $description, $order_id = null) {
    $connection = db_connect();

    // This query finds the user with the minimum number of tasks that are NOT 'DONE'.
    $query = "
        SELECT u.id, COUNT(t.id) AS open_tasks
        FROM staff_users u
        LEFT JOIN tasks t ON u.id = t.assigned_user_id AND t.conversation_status != 'DONE'
        WHERE u.is_active = 1
        GROUP BY u.id
        ORDER BY open_tasks ASC, RAND() -- RAND() helps break ties randomly
        LIMIT 1
    ";

    $result = mysqli_query($connection, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $assigned_user_id = $user['id'];

        // Escape inputs for the insert query
        $task_type_safe = mysqli_real_escape_string($connection, $task_type);
        $description_safe = mysqli_real_escape_string($connection, $description);
        $assigned_user_id_safe = mysqli_real_escape_string($connection, $assigned_user_id);
        $order_id_safe = ($order_id !== null) ? "'" . mysqli_real_escape_string($connection, $order_id) . "'" : "NULL";

        $insert_query = "
            INSERT INTO tasks (assigned_user_id, task_type, description, order_id)
            VALUES ('$assigned_user_id_safe', '$task_type_safe', '$description_safe', $order_id_safe)
        ";

        $insert_result = mysqli_query($connection, $insert_query);
        mysqli_close($connection);
        return $insert_result;
    }

    mysqli_close($connection);
    return false; // No active user found or query failed
}

/**
 * Sends an order confirmation email to the customer.
 *
 * @param int $order_id The ID of the order.
 * @return bool True on success, false on failure.
 */
function send_order_confirmation_email($order_id) {
    $connection = db_connect();

    // Fetch order and customer details
    $query = "
        SELECT o.order_id, o.order_date, o.total_amount, c.customer_name, c.email
        FROM orders o
        JOIN customers c ON o.customer_id = c.customer_id
        WHERE o.order_id = ?
    ";

    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "i", $order_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $order = mysqli_fetch_assoc($result);

        $to = $order['email'];
        $subject = "Order Confirmation - Your Order #" . $order['order_id'] . " has been received!";
        $message = "
            Dear " . htmlspecialchars($order['customer_name']) . ",

            Thank you for your order. We've received it and are getting it ready for you.

            Order Details:
            Order ID: " . $order['order_id'] . "
            Order Date: " . date("Y-m-d H:i", strtotime($order['order_date'])) . "
            Total Amount: $" . number_format($order['total_amount'], 2) . "

            We will notify you again once your order has shipped.

            Thanks,
            Siva Ganga Dance Costumes
        ";
        $headers = 'From: no-reply@sivaganga.com' . "\r\n" .
                   'Reply-To: no-reply@sivaganga.com' . "\r\n" .
                   'X-Mailer: PHP/' . phpversion();

        mysqli_close($connection);

        // Use mail() function to send email
        return mail($to, $subject, $message, $headers);
    }

    mysqli_close($connection);
    return false; // Order not found
}