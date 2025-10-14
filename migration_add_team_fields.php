<?php
require_once 'db.php';

function run_migration() {
    $connection = db_connect();
    if (!$connection) {
        echo "Error connecting to the database.\n";
        return;
    }

    $sql = "ALTER TABLE `staff_users`
            ADD COLUMN `designation` VARCHAR(255) DEFAULT NULL,
            ADD COLUMN `photo_path` VARCHAR(255) DEFAULT NULL";

    if (mysqli_query($connection, $sql)) {
        echo "Migration successful: 'designation' and 'photo_path' columns added to 'staff_users' table.\n";
    } else {
        echo "Error running migration: " . mysqli_error($connection) . "\n";
    }

    mysqli_close($connection);
}

// You can run this migration by accessing this script from your browser
// or by running `php migration_add_team_fields.php` in your terminal.
if (php_sapi_name() === 'cli') {
    run_migration();
}
?>