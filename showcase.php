<?php
require_once 'CostumeManager.php';
$costumeManager = new CostumeManager();
$costumes = $costumeManager->getAll(20); // Get the 20 latest costumes
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Our Dance Costumes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1 class="text-center">Our Dance Costumes</h1>

        <!-- Our Latest Creations -->
        <h2 class="mt-5">Our Latest Creations</h2>
        <div class="row">
            <?php for ($i = 0; $i < min(3, count($costumes)); $i++): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="<?= htmlspecialchars($costumes[$i]['image_path']) ?>" class="card-img-top" alt="<?= htmlspecialchars($costumes[$i]['name']) ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($costumes[$i]['name']) ?></h5>
                            <a href="costume_detail.php?id=<?= $costumes[$i]['id'] ?>" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endfor; ?>
        </div>

        <!-- All Costumes Gallery -->
        <h2 class="mt-5">All Costumes</h2>
        <div class="row">
            <?php foreach ($costumes as $costume): ?>
                <div class="col-md-3 mb-4">
                    <div class="card">
                        <img src="<?= htmlspecialchars($costume['image_path']) ?>" class="card-img-top" alt="<?= htmlspecialchars($costume['name']) ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($costume['name']) ?></h5>
                            <a href="costume_detail.php?id=<?= $costume['id'] ?>" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>