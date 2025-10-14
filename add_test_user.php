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

    $sql = "INSERT INTO `staff_users` (username, password, full_name, designation, photo_path)
            VALUES ('$username', '$password', '$full_name', '$designation', '$photo_path')";

    if (mysqli_query($connection, $sql)) {
        echo "Test user added successfully.\n";
    } else {
        echo "Error adding test user: " . mysqli_error($connection) . "\n";
    }

    mysqli_close($connection);
}

add_test_user();
?>