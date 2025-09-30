<?php
require_once 'db.php';

/**
 * Finds the staff user with the minimum number of active tasks and assigns a new task to them.
 *
 * @param string $task_type The type of the task.
 * @param string $description A description of the task.
 * @param string $target_customer The customer associated with the task.
 * @return bool True on success, false on failure.
 */
function assign_task_to_user($task_type, $description, $target_customer, $order_id = null) {
    $connection = db_connect();

    // This query finds the user with the minimum number of tasks that are NOT 'DONE'.
    // It joins staff_users and tasks, groups by user, and orders by the count of active tasks.
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
        $target_customer_safe = mysqli_real_escape_string($connection, $target_customer);
        $assigned_user_id_safe = mysqli_real_escape_string($connection, $assigned_user_id);
        $order_id_safe = ($order_id !== null) ? "'" . mysqli_real_escape_string($connection, $order_id) . "'" : "NULL";

        $insert_query = "
            INSERT INTO tasks (assigned_user_id, task_type, description, target_customer, order_id)
            VALUES ('$assigned_user_id_safe', '$task_type_safe', '$description_safe', '$target_customer_safe', $order_id_safe)
        ";

        $insert_result = mysqli_query($connection, $insert_query);
        mysqli_close($connection);
        return $insert_result;
    }

    mysqli_close($connection);
    return false; // No active user found or query failed
}