<?php
require_once 'CostumeManager.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: showcase.php');
    exit;
}

$costumeManager = new CostumeManager();
$costume = $costumeManager->getById($_GET['id']);

if (!$costume) {
    header('Location: showcase.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($costume['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-6">
                <img src="<?= htmlspecialchars($costume['image_path']) ?>" class="img-fluid" alt="<?= htmlspecialchars($costume['name']) ?>">
            </div>
            <div class="col-md-6">
                <h2><?= htmlspecialchars($costume['name']) ?></h2>
                <p><?= nl2br(htmlspecialchars($costume['description'])) ?></p>
                <ul>
                    <li><strong>Colors:</strong> <?= htmlspecialchars($costume['colors']) ?></li>
                    <li><strong>Pant Type:</strong> <?= htmlspecialchars($costume['pant_type']) ?></li>
                    <li><strong>Fan Type:</strong> <?= htmlspecialchars($costume['fan_type']) ?></li>
                </ul>
                <a href="showcase.php" class="btn btn-primary">Back to Showcase</a>
            </div>
        </div>
    </div>
</body>
</html>