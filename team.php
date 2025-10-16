<?php
require_once 'db.php';

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

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Siva Ganga</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link active" aria-current="page" href="team.php">Team</a></li>
                <li class="nav-item"><a class="nav-link" href="blog.php">Blog</a></li>
                <li class="nav-item"><a class="nav-link" href="faq.php">FAQ</a></li>
                <li class="nav-item"><a class="nav-link" href="contact.php">Contact Us</a></li>
                <li class="nav-item"><a class="nav-link" href="login.php">Staff Login</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <div class="row">
        <main class="col-md-12">
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