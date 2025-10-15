<?php
require_once 'db.php';

function run_migration() {
    $connection = db_connect();
    if (!$connection) {
        echo "Error connecting to the database.\n";
        return;
    }

    $sql = "CREATE TABLE `testimonials` (
              `id` INT AUTO_INCREMENT PRIMARY KEY,
              `author_name` VARCHAR(255) NOT NULL,
              `content` TEXT NOT NULL,
              `status` ENUM('pending', 'approved') DEFAULT 'pending',
              `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";

    if (mysqli_query($connection, $sql)) {
        echo "Migration successful: 'testimonials' table created.\n";
    } else {
        echo "Error running migration: " . mysqli_error($connection) . "\n";
    }

    mysqli_close($connection);
}

// You can run this migration by accessing this script from your browser
// or by running `php migration_add_testimonials_table.php` in your terminal.
if (php_sapi_name() === 'cli') {
    run_migration();
}
?>