<?php
// A simple installation script to set up the database and seed it with data.
// WARNING: This will drop existing tables and recreate them. Do not run on a production server.

require_once 'db.php';
require_once 'functions.php'; // For assign_task_to_user

// --- Helper function to display messages ---
function echo_message($message, $is_success = true) {
    $color = $is_success ? '#28a745' : '#dc3545';
    echo "<p style='color: $color;'>$message</p>";
}

// --- Main Installation Logic ---
echo "<h1>Siva Ganga Staff Manager Installation</h1>";

$connection = db_connect();

// 1. Drop existing tables (optional, but good for a clean install)
echo "<h2>Step 1: Dropping existing tables...</h2>";
// The order is important to respect foreign key constraints.
$tables = ['attendance', 'order_details', 'tasks', 'orders', 'staff_users'];
foreach ($tables as $table) {
    if (mysqli_query($connection, "DROP TABLE IF EXISTS `$table`")) {
        echo_message("Table `$table` dropped successfully.");
    } else {
        echo_message("Error dropping table `$table`: " . mysqli_error($connection), false);
    }
}

// 2. Create tables from schema.sql
echo "<h2>Step 2: Creating new tables from schema.sql...</h2>";
$schema_sql = file_get_contents('schema.sql');
if ($schema_sql === false) {
    die("<p style='color: #dc3545;'>Error: Could not read schema.sql file.</p>");
}

// The schema now contains multiple statements; execute them one by one.
$sql_statements = explode(';', $schema_sql);
foreach ($sql_statements as $statement) {
    $statement = trim($statement);
    if (!empty($statement)) {
        if (!mysqli_query($connection, $statement)) {
            die("<p style='color: #dc3545;'>Error executing statement: " . htmlspecialchars($statement) . "<br>" . mysqli_error($connection) . "</p>");
        }
    }
}
echo_message("Database schema created/updated successfully.");

// 3. Seed staff_users table
echo "<h2>Step 3: Seeding 'staff_users' table...</h2>";
$users = [
    ['jules', 'password123', 'Jules Verne'],
    ['siva', 'ganga456', 'Siva Ganga'],
    ['testuser', 'test', 'Test User']
];

foreach ($users as $user) {
    $username = mysqli_real_escape_string($connection, $user[0]);
    $password = mysqli_real_escape_string($connection, $user[1]);
    $full_name = mysqli_real_escape_string($connection, $user[2]);
    $query = "INSERT INTO staff_users (username, password, full_name) VALUES ('$username', '$password', '$full_name')";
    if (!mysqli_query($connection, $query)) {
        echo_message("Failed to create user '$username': " . mysqli_error($connection), false);
    } else {
        echo_message("User '$username' created.");
    }
}

// 4. Seed orders and order_details tables
echo "<h2>Step 4: Seeding 'orders' and 'order_details' tables...</h2>";
$orders_data = [
    ['customer_name' => 'John Doe', 'total_amount' => 150.00, 'items' => [['item_id' => 'SKU-DNC-001', 'quantity' => 1]]],
    ['customer_name' => 'Jane Smith', 'total_amount' => 250.50, 'items' => [['item_id' => 'SKU-DNC-002', 'quantity' => 2], ['item_id' => 'SKU-ACC-004', 'quantity' => 1]]],
    ['customer_name' => 'Peter Jones', 'total_amount' => 75.25, 'items' => [['item_id' => 'SKU-DNC-003', 'quantity' => 1]]],
];

$order_ids = [];
foreach ($orders_data as $order) {
    $customer_name_safe = mysqli_real_escape_string($connection, $order['customer_name']);
    $total_amount_safe = mysqli_real_escape_string($connection, $order['total_amount']);
    $order_query = "INSERT INTO orders (customer_name, total_amount) VALUES ('$customer_name_safe', '$total_amount_safe')";

    if (mysqli_query($connection, $order_query)) {
        $last_order_id = mysqli_insert_id($connection);
        $order_ids[$order['customer_name']] = $last_order_id;
        echo_message("Order #$last_order_id for {$order['customer_name']} created.");

        foreach ($order['items'] as $item) {
            $item_id_safe = mysqli_real_escape_string($connection, $item['item_id']);
            $quantity_safe = mysqli_real_escape_string($connection, $item['quantity']);
            $details_query = "INSERT INTO order_details (order_id, item_id, quantity) VALUES ('$last_order_id', '$item_id_safe', '$quantity_safe')";
            if (!mysqli_query($connection, $details_query)) {
                echo_message("--- Failed to add item '{$item['item_id']}' to order #$last_order_id: " . mysqli_error($connection), false);
            }
        }
    } else {
        echo_message("Failed to create order for {$order['customer_name']}: " . mysqli_error($connection), false);
    }
}

// 5. Seed tasks table using the load balancer function
echo "<h2>Step 5: Seeding 'tasks' table using load balancer...</h2>";
$tasks = [
    ['CONTACT_CUSTOMER', 'Follow up on recent order', 'John Doe', $order_ids['John Doe']],
    ['SEND_INVOICE', 'Send invoice for costume rental', 'Jane Smith', $order_ids['Jane Smith']],
    ['CONTACT_CUSTOMER', 'Confirm shipping address for order', 'Peter Jones', $order_ids['Peter Jones']],
    ['CONTACT_CUSTOMER', 'Inquire about custom design request', 'Mary Williams', null],
    ['SEND_INVOICE', 'Invoice for repair services', 'David Brown', null],
    ['CONTACT_CUSTOMER', 'Check satisfaction with recent purchase', 'Emily Davis', null]
];

foreach ($tasks as $task) {
    if (assign_task_to_user($task[0], $task[1], $task[2], $task[3] ?? null)) {
        echo_message("Task '{$task[1]}' for {$task[2]} assigned.");
    } else {
        echo_message("Failed to assign task '{$task[1]}'.", false);
    }
}

echo "<h2>Installation Complete!</h2>";
echo "<p>You can now <a href='login.php'>log in</a> with one of the created users (e.g., username: <strong>jules</strong>, password: <strong>password123</strong>).</p>";

mysqli_close($connection);
?>
<style>
    body { font-family: sans-serif; line-height: 1.6; padding: 20px; }
    h1, h2 { color: #333; }
</style>