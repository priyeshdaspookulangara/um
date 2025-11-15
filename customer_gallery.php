<?php
require_once 'db.php';

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
    <title>Customer Gallery - Siva Ganga</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .gallery-item {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Siva Ganga Dance Costumes</a>
         <ul class="navbar-nav ms-auto">
             <li class="nav-item">
                <a class="nav-link" href="login.php">Staff Login</a>
            </li>
        </ul>
    </div>
</nav>

<div class="container mt-4">
    <h1 class="text-center mb-5">Customer Showcase Gallery</h1>
    <div class="row">
        <?php if (empty($showcases)): ?>
            <p class="text-center">No showcases have been added yet.</p>
        <?php else: ?>
            <?php foreach ($showcases as $showcase): ?>
                <div class="col-md-4 gallery-item">
                    <div class="card">
                        <img src="<?= htmlspecialchars($showcase['image_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($showcase['title']) ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($showcase['title']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($showcase['description']) ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
