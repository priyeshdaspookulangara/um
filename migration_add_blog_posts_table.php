<?php
require_once 'db.php';

function run_migration() {
    $connection = db_connect();
    if (!$connection) {
        echo "Error connecting to the database.\n";
        return;
    }

    $sql = "CREATE TABLE `blog_posts` (
              `id` INT AUTO_INCREMENT PRIMARY KEY,
              `title` VARCHAR(255) NOT NULL,
              `content` TEXT NOT NULL,
              `author_id` INT NOT NULL,
              `status` ENUM('draft', 'published') DEFAULT 'draft',
              `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              FOREIGN KEY (`author_id`) REFERENCES `staff_users`(`id`) ON DELETE CASCADE
            )";

    if (mysqli_query($connection, $sql)) {
        echo "Migration successful: 'blog_posts' table created.\n";
    } else {
        echo "Error running migration: " . mysqli_error($connection) . "\n";
    }

    mysqli_close($connection);
}

// You can run this migration by accessing this script from your browser
// or by running `php migration_add_blog_posts_table.php` in your terminal.
if (php_sapi_name() === 'cli') {
    run_migration();
}
?>