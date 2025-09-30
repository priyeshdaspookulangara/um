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
$tables = ['attendance', 'tasks', 'staff_users'];
foreach (array_reverse($tables) as $table) {
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

// Execute multi-query
if (mysqli_multi_query($connection, $schema_sql)) {
    // To process the results of multi_query, we need to clear them.
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
    if (mysqli_query($connection, $query)) {
        echo_message("User '$username' created.");
    } else {
        echo_message("Failed to create user '$username': " . mysqli_error($connection), false);
    }
}

// 4. Seed tasks table using the load balancer function
// We need to re-establish connection inside the function, or pass it.
// For simplicity, the function handles its own connection.
echo "<h2>Step 4: Seeding 'tasks' table using load balancer...</h2>";
$tasks = [
    ['CONTACT_CUSTOMER', 'Follow up on recent order #12345', 'John Doe'],
    ['SEND_INVOICE', 'Send invoice for costume rental', 'Jane Smith'],
    ['CONTACT_CUSTOMER', 'Confirm shipping address for order #12346', 'Peter Jones'],
    ['CONTACT_CUSTOMER', 'Inquire about custom design request', 'Mary Williams'],
    ['SEND_INVOICE', 'Invoice for repair services', 'David Brown'],
    ['CONTACT_CUSTOMER', 'Check satisfaction with recent purchase', 'Emily Davis']
];

foreach ($tasks as $task) {
    if (assign_task_to_user($task[0], $task[1], $task[2])) {
        echo_message("Task '{$task[1]}' assigned.");
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