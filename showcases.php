<?php
session_start();
require_once 'db.php';
require_once 'functions.php';

// Check if the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];

// Handle form submission for new showcase
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_showcase'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $image = $_FILES['image'];

    if (!empty($title) && !empty($description) && $image['size'] > 0) {
        $upload_dir = 'uploads/';
        $image_name = uniqid() . '_' . basename($image['name']);
        $target_file = $upload_dir . $image_name;

        // Create uploads directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        if (move_uploaded_file($image['tmp_name'], $target_file)) {
            $image_url = $target_file;
            $connection = db_connect();
            $stmt = $connection->prepare("INSERT INTO customer_showcases (title, description, image_url) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $title, $description, $image_url);
            $stmt->execute();
            $stmt->close();
            $connection->close();
        }
    }
}

// Handle showcase deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $connection = db_connect();
    // First, get the image URL to delete the file
    $stmt = $connection->prepare("SELECT image_url FROM customer_showcases WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($image_url);
    if ($stmt->fetch()) {
        if (file_exists($image_url)) {
            unlink($image_url);
        }
    }
    $stmt->close();

    // Then, delete the record from the database
    $stmt = $connection->prepare("DELETE FROM customer_showcases WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    $connection->close();
    header("Location: showcases.php");
    exit();
}


// Fetch all showcases
$connection = db_connect();
$result = $connection->query("SELECT * FROM customer_showcases ORDER BY created_at DESC");
$showcases = $result->fetch_all(MYSQLI_ASSOC);
$connection->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Showcases - Siva Ganga</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">Staff Edge</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">Conversations</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="showcases.php">Showcases</a>
                </li>
                 <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h2>Manage Customer Showcases</h2>

    <!-- Add Showcase Form -->
    <div class="card mb-4">
        <div class="card-header">Add New Showcase</div>
        <div class="card-body">
            <form action="showcases.php" method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" class="form-control" id="title" name="title" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="image" class="form-label">Image</label>
                    <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                </div>
                <button type="submit" name="add_showcase" class="btn btn-primary">Add Showcase</button>
            </form>
        </div>
    </div>

    <!-- Existing Showcases -->
    <div class="card">
        <div class="card-header">Existing Showcases</div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($showcases as $showcase): ?>
                    <tr>
                        <td><img src="<?= htmlspecialchars($showcase['image_url']) ?>" alt="<?= htmlspecialchars($showcase['title']) ?>" style="width: 100px; height: auto;"></td>
                        <td><?= htmlspecialchars($showcase['title']) ?></td>
                        <td><?= htmlspecialchars($showcase['description']) ?></td>
                        <td>
                            <a href="showcases.php?delete=<?= $showcase['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this showcase?');">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
