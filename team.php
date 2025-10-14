<?php
session_start();
require_once 'db.php';

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$full_name = $_SESSION['full_name'];

// --- Team Members Fetcher ---
$connection = db_connect();
$team_members = [];
$query = "SELECT full_name, designation, photo_path FROM staff_users WHERE is_active = 1";
$result = mysqli_query($connection, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $team_members[] = $row;
    }
}
mysqli_close($connection);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team - Siva Ganga</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { position: fixed; top: 0; left: 0; bottom: 0; z-index: 100; padding: 48px 0 0; box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1); }
        .team-member-card {
            margin-bottom: 20px;
        }
        .team-member-img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
        }
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
                    <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-house-door"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-list-task"></i> My Tasks</a></li>
                    <li class="nav-item"><a class="nav-link" href="attendance_log.php"><i class="bi bi-calendar-check"></i> Attendance Log</a></li>
                    <li class="nav-item"><a class="nav-link" href="orders.php"><i class="bi bi-box-seam"></i> Order Management</a></li>
                    <li class="nav-item"><a class="nav-link active" aria-current="page" href="team.php"><i class="bi bi-people"></i> Team</a></li>
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
                <h1 class="h2">Our Team</h1>
            </div>

            <div class="row">
                <?php if (empty($team_members)): ?>
                    <p>No team members found.</p>
                <?php else: ?>
                    <?php foreach ($team_members as $member): ?>
                        <div class="col-md-4 team-member-card">
                            <div class="card">
                                <img src="<?php echo htmlspecialchars($member['photo_path'] ?? 'team_images/default.png'); ?>" class="card-img-top team-member-img mx-auto d-block mt-3" alt="<?php echo htmlspecialchars($member['full_name']); ?>">
                                <div class="card-body text-center">
                                    <h5 class="card-title"><?php echo htmlspecialchars($member['full_name']); ?></h5>
                                    <p class="card-text"><?php echo htmlspecialchars($member['designation']); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>