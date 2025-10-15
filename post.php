<?php
require_once 'db.php';

if (!isset($_GET['id'])) {
    header("Location: blog.php");
    exit();
}

$post_id = (int)$_GET['id'];
$connection = db_connect();

// Fetch the blog post
$post = null;
$stmt = $connection->prepare("SELECT bp.title, bp.content, bp.created_at, u.full_name as author_name
                             FROM blog_posts bp
                             JOIN staff_users u ON bp.author_id = u.id
                             WHERE bp.id = ? AND bp.status = 'published'");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    $post = $result->fetch_assoc();
}
$stmt->close();
mysqli_close($connection);

if (!$post) {
    // Post not found or not published
    header("Location: blog.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> - Siva Ganga</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Siva Ganga</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="blog.php">Blog</a></li>
                <li class="nav-item"><a class="nav-link" href="login.php">Staff Login</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h1><?php echo htmlspecialchars($post['title']); ?></h1>
            <p class="text-muted">
                Posted by <?php echo htmlspecialchars($post['author_name']); ?> on <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
            </p>
            <hr>
            <div>
                <?php echo nl2br(htmlspecialchars($post['content'])); ?>
            </div>
            <a href="blog.php" class="btn btn-secondary mt-4">Back to Blog</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>