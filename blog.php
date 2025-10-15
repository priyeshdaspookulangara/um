<?php
require_once 'db.php';

$connection = db_connect();

// Fetch all published blog posts
$posts = [];
$query = "SELECT bp.id, bp.title, LEFT(bp.content, 150) as excerpt, bp.created_at, u.full_name as author_name
          FROM blog_posts bp
          JOIN staff_users u ON bp.author_id = u.id
          WHERE bp.status = 'published'
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
    <title>Blog - Siva Ganga</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .blog-post {
            margin-bottom: 2rem;
        }
    </style>
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
                <li class="nav-item"><a class="nav-link active" aria-current="page" href="blog.php">Blog</a></li>
                <li class="nav-item"><a class="nav-link" href="login.php">Staff Login</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h1 class="mb-4">Our Blog</h1>

    <div class="row">
        <?php if (empty($posts)): ?>
            <p>No blog posts have been published yet.</p>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <div class="col-md-8 offset-md-2 blog-post">
                    <h2><?php echo htmlspecialchars($post['title']); ?></h2>
                    <p class="text-muted">
                        Posted by <?php echo htmlspecialchars($post['author_name']); ?> on <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
                    </p>
                    <p><?php echo htmlspecialchars($post['excerpt']); ?>...</p>
                    <a href="post.php?id=<?php echo $post['id']; ?>" class="btn btn-primary">Read More</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>