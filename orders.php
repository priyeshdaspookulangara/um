<?php
session_start();
require_once 'db.php';

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$full_name = $_SESSION['full_name'];

// Fetch all orders
$connection = db_connect();
$orders = [];
$orders_query = "SELECT id, customer_name, order_date, total_amount FROM orders ORDER BY order_date DESC";
$orders_result = mysqli_query($connection, $orders_query);
if ($orders_result) {
    while ($row = mysqli_fetch_assoc($orders_result)) {
        $orders[] = $row;
    }
}
mysqli_close($connection);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - Siva Ganga</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { position: fixed; top: 0; left: 0; bottom: 0; z-index: 100; padding: 48px 0 0; box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1); }
        .main-content { margin-left: 220px; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">Siva Ganga Dashboard</a>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php"><i class="bi bi-house-door"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php"><i class="bi bi-list-task"></i> My Tasks</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="attendance_log.php"><i class="bi bi-calendar-check"></i> Attendance Log</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="orders.php"><i class="bi bi-box-seam"></i> Order Management</a>
                    </li>
                </ul>
                <hr>
                <div class="dropdown p-3">
                    <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle me-2"></i>
                        <strong><?php echo htmlspecialchars($full_name); ?></strong>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
                        <li><a class="dropdown-item" href="logout.php">Sign out</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-5">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Order Management</h1>
            </div>

            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">Order ID</th>
                            <th scope="col">Customer Name</th>
                            <th scope="col">Order Date</th>
                            <th scope="col">Total Amount</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="5" class="text-center">No orders found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($order['id']); ?></td>
                                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                    <td><?php echo date("Y-m-d H:i", strtotime($order['order_date'])); ?></td>
                                    <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-primary btn-sm view-details-btn" data-order-id="<?php echo $order['id']; ?>">
                                            View Details
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<!-- Order Details Modal -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderDetailsModalLabel">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Content will be loaded here via AJAX -->
                <div id="modal-content-loading" class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div id="modal-content-display" style="display: none;">
                    <h4>Order Information</h4>
                    <p><strong>Order ID:</strong> <span id="modal-order-id"></span></p>
                    <p><strong>Customer:</strong> <span id="modal-customer-name"></span></p>
                    <p><strong>Date:</strong> <span id="modal-order-date"></span></p>
                    <p><strong>Total:</strong> <span id="modal-total-amount"></span></p>

                    <hr>
                    <h4>Items Purchased</h4>
                    <ul id="modal-item-list" class="list-group"></ul>

                    <hr>
                    <h4>Linked Task</h4>
                    <div id="modal-task-details"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    $('.view-details-btn').on('click', function() {
        var orderId = $(this).data('order-id');
        var modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));

        // Reset and show loading spinner
        $('#modal-content-display').hide();
        $('#modal-content-loading').show();
        $('#modal-item-list').empty();
        $('#modal-task-details').empty();

        modal.show();

        $.ajax({
            url: 'get_order_details.php',
            type: 'GET',
            data: { order_id: orderId },
            dataType: 'json',
            success: function(response) {
                // Populate Order Info
                $('#modal-order-id').text(response.order.id);
                $('#modal-customer-name').text(response.order.customer_name);
                $('#modal-order-date').text(new Date(response.order.order_date).toLocaleString());
                $('#modal-total-amount').text('$' + parseFloat(response.order.total_amount).toFixed(2));

                // Populate Items
                if (response.items.length > 0) {
                    $.each(response.items, function(index, item) {
                        $('#modal-item-list').append('<li class="list-group-item">Item: ' + item.item_id + ' | Quantity: ' + item.quantity + '</li>');
                    });
                } else {
                    $('#modal-item-list').append('<li class="list-group-item">No items found for this order.</li>');
                }

                // Populate Task Info
                if (response.task) {
                    var taskHtml = '<p><strong>Task ID:</strong> ' + response.task.id + '</p>' +
                                   '<p><strong>Assigned To:</strong> ' + response.task.assigned_to + '</p>' +
                                   '<p><strong>Type:</strong> ' + response.task.task_type + '</p>' +
                                   '<p><strong>Description:</strong> ' + response.task.description + '</p>' +
                                   '<p><strong>Status:</strong> <span class="badge bg-info">' + response.task.conversation_status + '</span></p>';
                    $('#modal-task-details').html(taskHtml);
                } else {
                    $('#modal-task-details').html('<p class="text-muted">No task is linked to this order.</p>');
                }

                // Hide loading and show content
                $('#modal-content-loading').hide();
                $('#modal-content-display').show();
            },
            error: function(xhr, status, error) {
                var errorMessage = 'Error loading order details.';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }
                $('#modal-content-loading').html('<div class="alert alert-danger">' + errorMessage + '</div>');
            }
        });
    });
});
</script>
</body>
</html>