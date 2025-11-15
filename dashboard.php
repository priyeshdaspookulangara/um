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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Siva Ganga</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body, html { height: 100%; }
        .main-layout { display: flex; height: calc(100vh - 56px); margin-top: 56px; }
        .conversation-list { flex: 0 0 320px; border-right: 1px solid #dee2e6; overflow-y: auto; }
        .conversation-view { flex-grow: 1; display: flex; flex-direction: column; }
        .message-history { flex-grow: 1; padding: 1rem; overflow-y: auto; }
        .reply-form { padding: 1rem; border-top: 1px solid #dee2e6; }
        .conversation-list .list-group-item-action { cursor: pointer; }
         .message { padding: 8px 12px; margin-bottom: 8px; border-radius: 12px; max-width: 70%; }
        .message.staff { background-color: #d1e7dd; align-self: flex-end; text-align: right; }
        .message.customer { background-color: #f8f9fa; border: 1px solid #dee2e6; align-self: flex-start; }
        .messages-container { display: flex; flex-direction: column; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">Staff Edge</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">Conversations</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="showcases.php">Showcases</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <main class="d-flex" style="height: 100vh; padding-top: 56px;">
            <!-- Left Pane: Conversation List -->
            <div id="conversation-list" class="col-md-4 conversation-list bg-light">
                <div class="p-3">
                    <h4>Conversations</h4>
                    <div id="task-list-container" class="list-group">
                        <!-- Tasks will be loaded here via AJAX -->
                    </div>
                </div>
            </div>

            <!-- Right Pane: Conversation View -->
            <div id="conversation-view" class="col-md-8 conversation-view">
                <div id="message-history" class="message-history">
                    <div class="text-center text-muted mt-5">Select a conversation to view messages.</div>
                </div>
                <div id="reply-form-container" class="reply-form bg-light" style="display: none;">
                    <form id="reply-form">
                        <input type="hidden" id="current-task-id" name="task_id">
                        <div class="input-group">
                            <textarea id="message-input" class="form-control" placeholder="Type your reply..." required></textarea>
                            <button type="submit" class="btn btn-primary">Send</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/staff-edge.js"></script>
</body>
</html>