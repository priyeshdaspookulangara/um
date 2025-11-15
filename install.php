<?php
// A simple installation script to set up the database and seed it with data.
// WARNING: This will drop existing tables and recreate them. Do not run on a production server.

require_once 'db.php';
require_once 'functions.php';

function echo_message($message, $is_success = true) {
    $color = $is_success ? '#28a745' : '#dc3545';
    echo "<p style='color: $color;'>$message</p>";
}

echo "<h1>Siva Ganga Staff Manager Installation (v2.0)</h1>";

$connection = db_connect();

// 1. Drop existing tables in the correct order to avoid foreign key constraint issues
echo "<h2>Step 1: Dropping existing tables...</h2>";
$tables = ['attendance', 'order_details', 'tasks', 'orders', 'customers', 'staff_users', 'customer_showcases'];
foreach ($tables as $table) {
    if (mysqli_query($connection, "DROP TABLE IF EXISTS `$table`")) {
        echo_message("Table `$table` dropped successfully.");
    } else {
        echo_message("Error dropping table `$table`: " . mysqli_error($connection), false);
    }
}

// 2. Create tables from the updated schema.sql
echo "<h2>Step 2: Creating new tables from schema.sql...</h2>";
$schema_sql = file_get_contents('schema.sql');
if ($schema_sql === false) {
    die("<p style='color: #dc3545;'>Error: Could not read schema.sql file.</p>");
}

// Execute multi-query for schema creation
if (mysqli_multi_query($connection, $schema_sql)) {
    // It's important to clear results from multi_query before running new queries
    while (mysqli_next_result($connection)) {;}
    echo_message("Database schema created successfully.");
} else {
    die("<p style='color: #dc3545;'>Error creating schema: " . mysqli_error($connection) . "</p>");
}

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
    mysqli_query($connection, $query) ? echo_message("User '$username' created.") : echo_message("Failed to create user '$username'.", false);
}

// 4. Seed customers table
echo "<h2>Step 4: Seeding 'customers' table...</h2>";
$customers_data = [
    ['John Doe', 'john.doe@example.com', '111-222-3333'],
    ['Jane Smith', 'jane.smith@example.com', '444-555-6666'],
    ['Peter Jones', 'peter.jones@example.com', '777-888-9999'],
    ['Mary Williams', 'mary.w@example.com', null],
    ['David Brown', 'd.brown@example.com', null]
];
$customer_ids = [];
foreach ($customers_data as $customer) {
    $name_safe = mysqli_real_escape_string($connection, $customer[0]);
    $email_safe = mysqli_real_escape_string($connection, $customer[1]);
    $phone_safe = mysqli_real_escape_string($connection, $customer[2]);
    $query = "INSERT INTO customers (customer_name, email, phone) VALUES ('$name_safe', '$email_safe', '$phone_safe')";
    if (mysqli_query($connection, $query)) {
        $last_id = mysqli_insert_id($connection);
        $customer_ids[$customer[0]] = $last_id; // Store ID for linking orders
        echo_message("Customer '{$customer[0]}' created with ID $last_id.");
    } else {
        echo_message("Failed to create customer '{$customer[0]}'.", false);
    }
}

// 5. Seed orders and order_details tables
echo "<h2>Step 5: Seeding 'orders' and 'order_details' tables...</h2>";
$orders_data = [
    ['customer_id' => $customer_ids['John Doe'], 'total_amount' => 150, 'is_paid' => 'paid', 'items' => [['item_id' => 'SKU-DNC-001', 'quantity' => 1]]],
    ['customer_id' => $customer_ids['Jane Smith'], 'total_amount' => 250, 'is_paid' => 'pending', 'items' => [['item_id' => 'SKU-DNC-002', 'quantity' => 2], ['item_id' => 'SKU-ACC-004', 'quantity' => 1]]],
    ['customer_id' => $customer_ids['Peter Jones'], 'total_amount' => 75, 'is_paid' => 'partially', 'items' => [['item_id' => 'SKU-DNC-003', 'quantity' => 1]]],
];
$order_ids = [];
foreach ($orders_data as $order) {
    $cust_id_safe = mysqli_real_escape_string($connection, $order['customer_id']);
    $total_safe = mysqli_real_escape_string($connection, $order['total_amount']);
    $status_safe = mysqli_real_escape_string($connection, $order['is_paid']);
    $order_query = "INSERT INTO orders (customer_id, total_amount, is_paid) VALUES ('$cust_id_safe', '$total_safe', '$status_safe')";

    if (mysqli_query($connection, $order_query)) {
        $last_order_id = mysqli_insert_id($connection);
        $order_ids[] = $last_order_id;
        echo_message("Order #$last_order_id created.");

        foreach ($order['items'] as $item) {
            $item_id_safe = mysqli_real_escape_string($connection, $item['item_id']);
            $quantity_safe = mysqli_real_escape_string($connection, $item['quantity']);
            $details_query = "INSERT INTO order_details (order_id, item_id, quantity) VALUES ('$last_order_id', '$item_id_safe', '$quantity_safe')";
            mysqli_query($connection, $details_query);
        }
    } else {
        echo_message("Failed to create order for customer ID {$order['customer_id']}.", false);
    }
}

// 6. Seed tasks table using the updated function
echo "<h2>Step 6: Seeding 'tasks' table...</h2>";
$tasks = [
    ['CONTACT_CUSTOMER', 'Follow up on recent order.', $order_ids[0]],
    ['SEND_INVOICE', 'Send invoice for costume rental.', $order_ids[1]],
    ['CONTACT_CUSTOMER', 'Confirm shipping address for order.', $order_ids[2]],
    ['CONTACT_CUSTOMER', 'Inquire about custom design request.', null],
    ['SEND_INVOICE', 'Invoice for repair services (non-order related).', null]
];
foreach ($tasks as $task) {
    if (assign_task_to_user($task[0], $task[1], $task[2] ?? null)) {
        echo_message("Task '{$task[1]}' assigned.");
    } else {
        echo_message("Failed to assign task '{$task[1]}'.", false);
    }
}

// 7. Seed customer_showcases table
echo "<h2>Step 7: Seeding 'customer_showcases' table...</h2>";
$showcases = [
    ['Elegant Kuchipudi Dancer', 'A mesmerizing performance by one of our esteemed clients, showcasing the vibrant colors and intricate details of our Kuchipudi costume.', 'uploads/showcase1.jpg'],
    ['Bharatanatyam Recital', 'A powerful and graceful Bharatanatyam recital. The dancer is adorned in a custom-designed costume from our collection.', 'uploads/showcase2.jpg'],
    ['Kathakali Performance', 'A stunning Kathakali performance, featuring our traditional costume and elaborate headgear.', 'uploads/showcase3.jpg']
];
foreach ($showcases as $showcase) {
    $title = mysqli_real_escape_string($connection, $showcase[0]);
    $description = mysqli_real_escape_string($connection, $showcase[1]);
    $image_url = mysqli_real_escape_string($connection, $showcase[2]);
    $query = "INSERT INTO customer_showcases (title, description, image_url) VALUES ('$title', '$description', '$image_url')";
    mysqli_query($connection, $query) ? echo_message("Showcase '{$showcase[0]}' created.") : echo_message("Failed to create showcase '{$showcase[0]}'.", false);
}

echo "<h2>Installation Complete!</h2>";
echo "<p>You can now <a href='login.php'>log in</a> with one of the created users (e.g., username: <strong>jules</strong>, password: <strong>password123</strong>).</p>";

mysqli_close($connection);
?>
<style>
    body { font-family: sans-serif; line-height: 1.6; padding: 20px; }
    h1, h2 { color: #333; }
</style>