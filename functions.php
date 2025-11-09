<?php
require_once 'db.php';
require_once 'vendor/autoload.php';
require_once 'TemplateManager.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
 * Triggers an email event, fetches the template, substitutes variables, and sends the email.
 *
 * @param string $event_name The name of the event (e.g., 'ORDER_CONFIRMED').
 * @param array $data_array An associative array of data to substitute into the template.
 * @return bool True on success, false on failure.
 */
function trigger_email_event($event_name, $data_array) {
    $templateManager = new TemplateManager();
    $template = $templateManager->getTemplateByEvent($event_name);

    if ($template) {
        $subject = substitute_variables($template['subject'], $data_array);
        $body = substitute_variables($template['body'], $data_array);

        if (isset($data_array['to_address'])) {
            return send_notification_email($data_array['to_address'], $subject, $body);
        }
    }

    return false;
}

/**
 * Sends an order confirmation email to the customer using the new template system.
 *
 * @param int $order_id The ID of the order.
 * @return bool True on success, false on failure.
 */
function send_order_confirmation_email($order_id) {
    $connection = db_connect();

    // Fetch order and customer details
    $query = "
        SELECT o.order_id, o.order_date, o.total_amount, c.customer_name, c.email, c.phone
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
        mysqli_close($connection);

        $data_array = [
            'to_address' => $order['email'],
            'cust_name' => $order['customer_name'],
            'order_id' => $order['order_id'],
            'order_date' => date("Y-m-d H:i", strtotime($order['order_date'])),
            'total_amount' => number_format($order['total_amount'], 2),
            'phone_number' => $order['phone'],
            'delivery_address' => '123 Main St, Anytown, USA', // Placeholder
            'today' => date("Y-m-d"),
            'product' => 'Assorted Dance Costumes' // Placeholder
        ];

        return trigger_email_event('ORDER_CONFIRMED', $data_array);
    }

    mysqli_close($connection);
    return false; // Order not found
}

/**
 * Substitutes variables in a template with the provided data.
 *
 * @param string $template_body The template body with placeholders.
 * @param array $data_array An associative array where keys are variable names and values are the data.
 * @return string The template body with variables substituted.
 */
function substitute_variables($template_body, $data_array) {
    foreach ($data_array as $key => $value) {
        $template_body = str_replace('{{' . $key . '}}', $value, $template_body);
    }
    // Remove any remaining unresolved placeholders
    $template_body = preg_replace('/\{\{.*?\}\}/', '', $template_body);
    return $template_body;
}

/**
 * Sends an email using PHPMailer.
 *
 * @param string $to_address The recipient's email address.
 * @param string $subject The email subject.
 * @param string $body The email body.
 * @return bool True on success, false on failure.
 */
function send_notification_email($to_address, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;

        //Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to_address);

        //Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log the error message
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

function get_tasks_for_user($connection, $user_id) {
    $user_id_safe = mysqli_real_escape_string($connection, $user_id);
    $query = "SELECT id, task_type, description, conversation_status FROM tasks WHERE assigned_user_id = '$user_id_safe' ORDER BY created_at DESC";
    $result = mysqli_query($connection, $query);
    $tasks = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $tasks[] = $row;
        }
    }
    return $tasks;
}

function get_conversation_messages($connection, $task_id) {
    $task_id_safe = mysqli_real_escape_string($connection, $task_id);
    $query = "SELECT * FROM conversation_messages WHERE task_id = '$task_id_safe' ORDER BY created_at ASC";
    $result = mysqli_query($connection, $query);
    $messages = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $messages[] = $row;
        }
    }
    return $messages;
}

function is_user_assigned_to_task($connection, $user_id, $task_id) {
    $user_id_safe = mysqli_real_escape_string($connection, $user_id);
    $task_id_safe = mysqli_real_escape_string($connection, $task_id);
    $query = "SELECT id FROM tasks WHERE id = '$task_id_safe' AND assigned_user_id = '$user_id_safe'";
    $result = mysqli_query($connection, $query);
    return ($result && mysqli_num_rows($result) > 0);
}

function send_reply($connection, $task_id, $user_id, $message) {
    $task_id_safe = mysqli_real_escape_string($connection, $task_id);
    $user_id_safe = mysqli_real_escape_string($connection, $user_id);
    $message_safe = mysqli_real_escape_string($connection, $message);
    $query = "INSERT INTO conversation_messages (task_id, sender_id, sender_type, message) VALUES ('$task_id_safe', '$user_id_safe', 'staff', '$message_safe')";
    return mysqli_query($connection, $query);
}