<?php
session_start();
require_once 'db.php';

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];
$attendance_marked_today = false;

// --- Attendance Logger ---
$connection = db_connect();
$current_date = date('Y-m-d');
$current_time = date('H:i:s');

$user_id_safe = mysqli_real_escape_string($connection, $user_id);
$current_date_safe = mysqli_real_escape_string($connection, $current_date);

$check_query = "SELECT id FROM attendance WHERE user_id = '$user_id_safe' AND login_date = '$current_date_safe'";
$check_result = mysqli_query($connection, $check_query);

if ($check_result && mysqli_num_rows($check_result) == 0) {
    $insert_query = "INSERT INTO attendance (user_id, login_date, login_time) VALUES ('$user_id_safe', '$current_date_safe', '$current_time')";
    if (mysqli_query($connection, $insert_query)) {
        $attendance_marked_today = true;
    }
} else if ($check_result && mysqli_num_rows($check_result) > 0) {
    $attendance_marked_today = true;
}

// --- Task Fetcher (Refactored) ---
$tasks = [];
$tasks_query = "SELECT id, task_type, description, conversation_status FROM tasks WHERE assigned_user_id = '$user_id_safe' ORDER BY created_at DESC";
$tasks_result = mysqli_query($connection, $tasks_query);
if ($tasks_result) {
    while ($row = mysqli_fetch_assoc($tasks_result)) {
        $tasks[] = $row;
    }
}

mysqli_close($connection);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Siva Ganga</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { position: fixed; top: 0; left: 0; bottom: 0; z-index: 100; padding: 48px 0 0; box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1); }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid"><a class="navbar-brand" href="dashboard.php">Siva Ganga Dashboard</a></div>
</nav>

<div class="container-fluid">
    <div class="row">
        <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link active" aria-current="page" href="dashboard.php"><i class="bi bi-house-door"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-list-task"></i> My Tasks</a></li>
                    <li class="nav-item"><a class="nav-link" href="attendance_log.php"><i class="bi bi-calendar-check"></i> Attendance Log</a></li>
                    <li class="nav-item"><a class="nav-link" href="orders.php"><i class="bi bi-box-seam"></i> Order Management</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_blog.php"><i class="bi bi-pencil-square"></i> Blog Management</a></li>
                </ul>
                <hr>
                <div class="dropdown p-3">
                    <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle me-2"></i>
                        <strong><?php echo htmlspecialchars($full_name); ?></strong>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
                        <li><a class="dropdown-item" href="logout.php">Sign out</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-5">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Dashboard</h1>
                <div class="alert <?php echo $attendance_marked_today ? 'alert-success' : 'alert-warning'; ?>" role="alert">
                    Attendance Marked Today: <strong><?php echo $attendance_marked_today ? 'YES' : 'NO'; ?></strong>
                </div>
            </div>

            <h2 class="mt-4">My Tasks</h2>
            <div class="table-responsive">
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th scope="col">Task Type</th>
                            <th scope="col">Description</th>
                            <th scope="col">Status</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tasks)): ?>
                            <tr><td colspan="4" class="text-center">No tasks assigned to you.</td></tr>
                        <?php else: ?>
                            <?php foreach ($tasks as $task): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($task['task_type']); ?></td>
                                    <td><?php echo htmlspecialchars($task['description']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $task['conversation_status'] === 'DONE' ? 'bg-success' : 'bg-warning'; ?>">
                                            <?php echo htmlspecialchars($task['conversation_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form action="update_task.php" method="POST" class="d-inline">
                                            <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <button type="submit" class="btn btn-sm <?php echo $task['conversation_status'] === 'DONE' ? 'btn-secondary' : 'btn-success'; ?>">
                                                <?php echo $task['conversation_status'] === 'DONE' ? 'Mark as Pending' : 'Mark as Done'; ?>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>