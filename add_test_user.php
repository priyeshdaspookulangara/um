<?php
require_once 'db.php';

function add_test_user() {
    $connection = db_connect();
    if (!$connection) {
        echo "Error connecting to the database.\n";
        return;
    }

    $username = 'testuser';
    $password = password_hash('password', PASSWORD_DEFAULT);
    $full_name = 'Test User';
    $designation = 'Tester';
    $photo_path = 'team_images/test.png';

    $stmt = $connection->prepare("INSERT INTO `staff_users` (username, password, full_name, designation, photo_path) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $password, $full_name, $designation, $photo_path);

    if ($stmt->execute()) {
        echo "Test user added successfully.\n";
    } else {
        echo "Error adding test user: " . $stmt->error . "\n";
    }

    $stmt->close();

    mysqli_close($connection);
}

add_test_user();
?>