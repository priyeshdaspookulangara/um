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

// Handle form submissions for creating/editing posts
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_post'])) {
        $title = mysqli_real_escape_string($connection, $_POST['title']);
        $content = mysqli_real_escape_string($connection, $_POST['content']);
        $status = mysqli_real_escape_string($connection, $_POST['status']);
        $post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : null;

        if ($post_id) {
            // Update existing post
            $stmt = $connection->prepare("UPDATE blog_posts SET title = ?, content = ?, status = ? WHERE id = ?");
            $stmt->bind_param("sssi", $title, $content, $status, $post_id);
        } else {
            // Create new post
            $stmt = $connection->prepare("INSERT INTO blog_posts (title, content, status, author_id) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $title, $content, $status, $user_id);
        }
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['delete_post'])) {
        $post_id = (int)$_POST['post_id'];
        $stmt = $connection->prepare("DELETE FROM blog_posts WHERE id = ?");
        $stmt->bind_param("i", $post_id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: admin_blog.php");
    exit();
}

// Fetch all blog posts
$posts = [];
$query = "SELECT bp.id, bp.title, bp.status, bp.created_at, u.full_name as author_name
          FROM blog_posts bp
          JOIN staff_users u ON bp.author_id = u.id
          ORDER BY bp.created_at DESC";
$result = mysqli_query($connection, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $posts[] = $row;
    }
}

mysqli_close($connection);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Management - Siva Ganga</title>
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
                    <li class="nav-item"><a class="nav-link active" aria-current="page" href="admin_blog.php"><i class="bi bi-pencil-square"></i> Blog Management</a></li>
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
                <h1 class="h2">Blog Management</h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#postModal">
                    Create New Post
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($post['title']); ?></td>
                                <td><?php echo htmlspecialchars($post['author_name']); ?></td>
                                <td><span class="badge <?php echo $post['status'] === 'published' ? 'bg-success' : 'bg-secondary'; ?>"><?php echo htmlspecialchars($post['status']); ?></span></td>
                                <td><?php echo htmlspecialchars($post['created_at']); ?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#postModal" data-post-id="<?php echo $post['id']; ?>" data-title="<?php echo htmlspecialchars($post['title']); ?>" data-content="<?php echo htmlspecialchars($post['content']); ?>" data-status="<?php echo htmlspecialchars($post['status']); ?>">Edit</button>
                                    <form action="admin_blog.php" method="POST" class="d-inline">
                                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                        <button type="submit" name="delete_post" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<!-- Post Modal -->
<div class="modal fade" id="postModal" tabindex="-1" aria-labelledby="postModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="admin_blog.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="postModalLabel">Create/Edit Post</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="post_id" id="postId">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="content" class="form-label">Content</label>
                        <textarea class="form-control" id="content" name="content" rows="10" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="save_post" class="btn btn-primary">Save Post</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var postModal = document.getElementById('postModal');
    postModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var postId = button.getAttribute('data-post-id');
        var modalTitle = postModal.querySelector('.modal-title');
        var postIdInput = postModal.querySelector('#postId');
        var titleInput = postModal.querySelector('#title');
        var contentInput = postModal.querySelector('#content');
        var statusInput = postModal.querySelector('#status');

        if (postId) {
            modalTitle.textContent = 'Edit Post';
            postIdInput.value = postId;
            titleInput.value = button.getAttribute('data-title');
            contentInput.value = button.getAttribute('data-content');
            statusInput.value = button.getAttribute('data-status');
        } else {
            modalTitle.textContent = 'Create New Post';
            postIdInput.value = '';
            titleInput.value = '';
            contentInput.value = '';
            statusInput.value = 'draft';
        }
    });
});
</script>
</body>
</html>