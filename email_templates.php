<?php
require_once 'TemplateManager.php';

$templateManager = new TemplateManager();
$templates = $templateManager->getAllTemplates();
$edit_template = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_template'])) {
        $templateManager->createTemplate($_POST['name'], $_POST['subject'], $_POST['body'], $_POST['associated_event']);
    } elseif (isset($_POST['update_template'])) {
        $templateManager->updateTemplate($_POST['id'], $_POST['name'], $_POST['subject'], $_POST['body'], $_POST['associated_event'], isset($_POST['is_active']) ? 1 : 0);
    } elseif (isset($_POST['delete_template'])) {
        $templateManager->deleteTemplate($_POST['id']);
    }
    header("Location: email_templates.php");
    exit();
}

if (isset($_GET['edit'])) {
    $edit_template = $templateManager->getTemplateById($_GET['edit']);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Templates</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Email Template Management</h1>

        <div class="card mb-4">
            <div class="card-header">
                <?php echo $edit_template ? 'Edit Template' : 'Create New Template'; ?>
            </div>
            <div class="card-body">
                <form action="email_templates.php" method="post">
                    <?php if ($edit_template): ?>
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($edit_template['id']); ?>">
                    <?php endif; ?>
                    <div class="mb-3">
                        <label for="name" class="form-label">Template Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($edit_template['name'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject</label>
                        <input type="text" class="form-control" id="subject" name="subject" value="<?php echo htmlspecialchars($edit_template['subject'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="body" class="form-label">Body</label>
                        <textarea class="form-control" id="body" name="body" rows="5" required><?php echo htmlspecialchars($edit_template['body'] ?? ''); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="associated_event" class="form-label">Associated Event</label>
                        <input type="text" class="form-control" id="associated_event" name="associated_event" value="<?php echo htmlspecialchars($edit_template['associated_event'] ?? ''); ?>" required>
                    </div>
                    <?php if ($edit_template): ?>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?php echo ($edit_template['is_active'] ?? 0) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_active">
                                Active
                            </label>
                        </div>
                    <?php endif; ?>

                    <?php if ($edit_template): ?>
                        <button type="submit" name="update_template" class="btn btn-primary">Update Template</button>
                        <a href="email_templates.php" class="btn btn-secondary">Cancel</a>
                    <?php else: ?>
                        <button type="submit" name="create_template" class="btn btn-primary">Create Template</button>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                Existing Templates
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Subject</th>
                            <th>Associated Event</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($templates as $template): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($template['name']); ?></td>
                                <td><?php echo htmlspecialchars($template['subject']); ?></td>
                                <td><?php echo htmlspecialchars($template['associated_event']); ?></td>
                                <td><?php echo $template['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>'; ?></td>
                                <td>
                                    <a href="email_templates.php?edit=<?php echo $template['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                    <form action="email_templates.php" method="post" class="d-inline">
                                        <input type="hidden" name="id" value="<?php echo $template['id']; ?>">
                                        <button type="submit" name="delete_template" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this template?');">Delete</button>
                                    </form>
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
