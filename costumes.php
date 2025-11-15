<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'CostumeManager.php';
$costumeManager = new CostumeManager();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_costume'])) {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $colors = $_POST['colors'];
        $pant_type = $_POST['pant_type'];
        $fan_type = $_POST['fan_type'];

        // Handle file upload
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if($check !== false) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $costumeManager->create($name, $description, $target_file, $colors, $pant_type, $fan_type);
            }
        }
    } elseif (isset($_POST['delete_costume'])) {
        $costumeManager->delete($_POST['costume_id']);
    }
}

$costumes = $costumeManager->getAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Costumes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Manage Costumes</h2>

        <!-- Add Costume Form -->
        <div class="card mb-4">
            <div class="card-header">Add New Costume</div>
            <div class="card-body">
                <form action="costumes.php" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="name" class="form-label">Costume Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Costume Image</label>
                        <input type="file" class="form-control" id="image" name="image" required>
                    </div>
                    <div class="mb-3">
                        <label for="colors" class="form-label">Colors</label>
                        <input type="text" class="form-control" id="colors" name="colors">
                    </div>
                    <div class="mb-3">
                        <label for="pant_type" class="form-label">Pant Type</label>
                        <input type="text" class="form-control" id="pant_type" name="pant_type">
                    </div>
                    <div class="mb-3">
                        <label for="fan_type" class="form-label">Fan Type</label>
                        <input type="text" class="form-control" id="fan_type" name="fan_type">
                    </div>
                    <button type="submit" name="add_costume" class="btn btn-primary">Add Costume</button>
                </form>
            </div>
        </div>

        <!-- Existing Costumes Table -->
        <div class="card">
            <div class="card-header">Existing Costumes</div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($costumes as $costume): ?>
                        <tr>
                            <td><img src="<?= htmlspecialchars($costume['image_path']) ?>" alt="<?= htmlspecialchars($costume['name']) ?>" style="width: 50px; height: auto;"></td>
                            <td><?= htmlspecialchars($costume['name']) ?></td>
                            <td>
                                <!-- Edit and Delete buttons -->
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>