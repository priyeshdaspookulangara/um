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

$connection = db_connect();

// Handle form submissions for creating/editing/deleting team members
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_member'])) {
        $full_name = $_POST['full_name'];
        $designation = $_POST['designation'];
        $member_id = isset($_POST['member_id']) ? (int)$_POST['member_id'] : null;
        $photo_path = $_POST['existing_photo_path']; // Default to existing path

        // Handle file upload
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
            $upload_dir = 'team_images/';
            $file_name = basename($_FILES['photo']['name']);
            $target_file = $upload_dir . $file_name;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
                $photo_path = $target_file;
            }
        }

        if ($member_id) {
            // Update existing member
            $stmt = $connection->prepare("UPDATE staff_users SET full_name = ?, designation = ?, photo_path = ? WHERE id = ?");
            $stmt->bind_param("sssi", $full_name, $designation, $photo_path, $member_id);
        } else {
            // Create new member
            $username = $_POST['username'];
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $connection->prepare("INSERT INTO staff_users (username, password, full_name, designation, photo_path, is_active) VALUES (?, ?, ?, ?, ?, 1)");
            $stmt->bind_param("sssss", $username, $password, $full_name, $designation, $photo_path);
        }
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['delete_member'])) {
        // Deleting a team member is equivalent to making them inactive
        $member_id = (int)$_POST['member_id'];
        $stmt = $connection->prepare("UPDATE staff_users SET is_active = 0 WHERE id = ?");
        $stmt->bind_param("i", $member_id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: admin_team.php");
    exit();
}

// Fetch all team members
$team_members = [];
$query = "SELECT id, full_name, designation, photo_path, is_active FROM staff_users";
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
    <title>Team Management - Siva Ganga</title>
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
                    <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-house-door"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-list-task"></i> My Tasks</a></li>
                    <li class="nav-item"><a class="nav-link" href="attendance_log.php"><i class="bi bi-calendar-check"></i> Attendance Log</a></li>
                    <li class="nav-item"><a class="nav-link" href="orders.php"><i class="bi bi-box-seam"></i> Order Management</a></li>
                    <li class="nav-item"><a class="nav-link" href="team.php"><i class="bi bi-people"></i> Team</a></li>
                    <li class="nav-item"><a class="nav-link active" aria-current="page" href="admin_team.php"><i class="bi bi-person-badge"></i> Team Management</a></li>
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
                <h1 class="h2">Team Management</h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#memberModal">
                    Create New Member
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Designation</th>
                            <th>Active</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($team_members as $member): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($member['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($member['designation']); ?></td>
                                <td><span class="badge <?php echo $member['is_active'] ? 'bg-success' : 'bg-secondary'; ?>"><?php echo $member['is_active'] ? 'Yes' : 'No'; ?></span></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#memberModal" data-member-id="<?php echo $member['id']; ?>" data-full-name="<?php echo htmlspecialchars($member['full_name']); ?>" data-designation="<?php echo htmlspecialchars($member['designation']); ?>" data-photo-path="<?php echo htmlspecialchars($member['photo_path']); ?>">Edit</button>
                                    <?php if ($member['is_active']): ?>
                                        <form action="admin_team.php" method="POST" class="d-inline">
                                            <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                            <button type="submit" name="delete_member" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to make this member inactive?')">Deactivate</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<!-- Member Modal -->
<div class="modal fade" id="memberModal" tabindex="-1" aria-labelledby="memberModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="admin_team.php" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="memberModalLabel">Create/Edit Team Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="member_id" id="memberId">
                    <div id="new-member-fields">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="fullName" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="fullName" name="full_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="designation" class="form-label">Designation</label>
                        <input type="text" class="form-control" id="designation" name="designation" required>
                    </div>
                    <div class="mb-3">
                        <label for="photo" class="form-label">Photo</label>
                        <input type="file" class="form-control" id="photo" name="photo">
                        <input type="hidden" name="existing_photo_path" id="existingPhotoPath">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="save_member" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var memberModal = document.getElementById('memberModal');
    memberModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var memberId = button.getAttribute('data-member-id');
        var modalTitle = memberModal.querySelector('.modal-title');
        var memberIdInput = memberModal.querySelector('#memberId');
        var newMemberFields = document.getElementById('new-member-fields');
        var usernameInput = memberModal.querySelector('#username');
        var passwordInput = memberModal.querySelector('#password');
        var fullNameInput = memberModal.querySelector('#fullName');
        var designationInput = memberModal.querySelector('#designation');
        var photoPathInput = memberModal.querySelector('#photoPath');

        if (memberId) {
            // Editing existing member
            modalTitle.textContent = 'Edit Team Member';
            newMemberFields.style.display = 'none';
            usernameInput.required = false;
            passwordInput.required = false;
            memberIdInput.value = memberId;
            fullNameInput.value = button.getAttribute('data-full-name');
            designationInput.value = button.getAttribute('data-designation');
            photoPathInput.value = button.getAttribute('data-photo-path');
        } else {
            // Creating new member
            modalTitle.textContent = 'Create New Member';
            newMemberFields.style.display = 'block';
            usernameInput.required = true;
            passwordInput.required = true;
            memberIdInput.value = '';
            fullNameInput.value = '';
            designationInput.value = '';
            photoPathInput.value = '';
        }
    });
});
</script>
</body>
</html>