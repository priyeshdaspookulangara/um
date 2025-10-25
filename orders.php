<?php
session_start();
require_once 'db.php';

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$full_name = $_SESSION['full_name'];

// Fetch all orders with customer names
$connection = db_connect();
$orders = [];
$orders_query = "
    SELECT o.order_id, o.order_date, o.total_amount, o.is_paid, c.customer_name
    FROM orders o
    JOIN customers c ON o.customer_id = c.customer_id
    ORDER BY o.order_date DESC
";
$orders_result = mysqli_query($connection, $orders_query);
if ($orders_result) {
    while ($row = mysqli_fetch_assoc($orders_result)) {
        $orders[] = $row;
    }
}
mysqli_close($connection);

// Helper function to determine badge color for payment status
function get_status_badge_class($status) {
    switch (strtolower($status)) {
        case 'paid': return 'bg-success';
        case 'pending': return 'bg-warning text-dark';
        case 'partially': return 'bg-info text-dark';
        case 'error': return 'bg-danger';
        default: return 'bg-secondary';
    }
}
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
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid"><a class="navbar-brand" href="dashboard.php">Siva Ganga Dashboard</a></div>
</nav>

<div class="container-fluid">
    <div class="row">
        <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-house-door"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-list-task"></i> My Tasks</a></li>
                    <li class="nav-item"><a class="nav-link" href="attendance_log.php"><i class="bi bi-calendar-check"></i> Attendance Log</a></li>
                    <li class="nav-item"><a class="nav-link active" aria-current="page" href="orders.php"><i class="bi bi-box-seam"></i> Order Management</a></li>
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
                            <th scope="col">Payment Status</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr><td colspan="6" class="text-center">No orders found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                    <td><?php echo date("Y-m-d H:i", strtotime($order['order_date'])); ?></td>
                                    <td>$<?php echo number_format($order['total_amount']); ?></td>
                                    <td>
                                        <span class="badge <?php echo get_status_badge_class($order['is_paid']); ?>">
                                            <?php echo htmlspecialchars(ucfirst($order['is_paid'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-primary btn-sm view-details-btn" data-order-id="<?php echo $order['order_id']; ?>">
                                            View Details
                                        </button>
                                        <button type="button" class="btn btn-success btn-sm confirm-order-btn" data-order-id="<?php echo $order['order_id']; ?>">
                                            Confirm
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
            <div class="modal-body" id="modal-body-content">
                <!-- AJAX content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="update-status-btn" style="display: none;">Update Status</button>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmationModalLabel">Confirm Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to send a confirmation email to the customer?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="send-confirmation-btn">Yes, Send</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    var currentOrderId;
    var orderDetailsModal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
    var confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));

    // Handle "View Details" button click
    $('.view-details-btn').on('click', function() {
        currentOrderId = $(this).data('order-id');
        var modalBody = $('#modal-body-content');
        var updateBtn = $('#update-status-btn');

        // Show loading spinner
        modalBody.html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');
        updateBtn.hide();
        orderDetailsModal.show();

        // Fetch order details
        $.ajax({
            url: 'get_order_details.php',
            type: 'GET',
            data: { order_id: currentOrderId },
            dataType: 'json',
            success: function(response) {
                let content = `
                    <div id="status-update-alert"></div>
                    <h4>Order Information</h4>
                    <p><strong>Order ID:</strong> ${response.order.order_id}</p>
                    <p><strong>Customer:</strong> ${response.order.customer_name}</p>
                    <p><strong>Date:</strong> ${new Date(response.order.order_date).toLocaleString()}</p>
                    <p><strong>Total:</strong> $${parseInt(response.order.total_amount).toFixed(2)}</p>
                    <hr>
                    <h4>Items Purchased</h4>
                    <ul class="list-group mb-3">`;

                if (response.items.length > 0) {
                    response.items.forEach(item => {
                        content += `<li class="list-group-item">Item: ${item.item_id} | Quantity: ${item.quantity}</li>`;
                    });
                } else {
                    content += '<li class="list-group-item">No items found.</li>';
                }
                content += '</ul><hr><h4>Linked Task</h4>';

                if (response.task) {
                    content += `<p><strong>Task:</strong> ${response.task.description} (${response.task.conversation_status})</p>`;
                } else {
                    content += '<p class="text-muted">No task linked.</p>';
                }

                content += `<hr><h4>Update Payment Status</h4>
                            <div class="input-group">
                                <select class="form-select" id="order-status-select">
                                    <option value="pending" ${response.order.is_paid === 'pending' ? 'selected' : ''}>Pending</option>
                                    <option value="paid" ${response.order.is_paid === 'paid' ? 'selected' : ''}>Paid</option>
                                    <option value="partially" ${response.order.is_paid === 'partially' ? 'selected' : ''}>Partially</option>
                                    <option value="error" ${response.order.is_paid === 'error' ? 'selected' : ''}>Error</option>
                                </select>
                            </div>`;

                modalBody.html(content);
                updateBtn.show();
            },
            error: function() {
                modalBody.html('<div class="alert alert-danger">Error loading order details.</div>');
            }
        });
    });

    // Handle "Update Status" button click
    $('#update-status-btn').on('click', function() {
        var newStatus = $('#order-status-select').val();

        $.ajax({
            url: 'update_order_status.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ order_id: currentOrderId, status: newStatus }),
            dataType: 'json',
            success: function(response) {
                let alertClass = response.success ? 'alert-success' : 'alert-danger';
                $('#status-update-alert').html(`<div class="alert ${alertClass}">${response.message}</div>`).fadeIn().delay(3000).fadeOut();
                // Optionally, refresh the main page's order list after a short delay
                if(response.success) {
                    setTimeout(() => location.reload(), 1000);
                }
            },
            error: function(xhr) {
                let errorMsg = 'An unknown error occurred.';
                if(xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                }
                $('#status-update-alert').html(`<div class="alert alert-danger">${errorMsg}</div>`).fadeIn().delay(3000).fadeOut();
            }
        });
    });

    // Handle "Confirm" button click
    $('.confirm-order-btn').on('click', function() {
        currentOrderId = $(this).data('order-id');
        confirmationModal.show();
    });

    // Handle "Send Confirmation" button click in the modal
    $('#send-confirmation-btn').on('click', function() {
        $.ajax({
            url: 'send_confirmation_email.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ order_id: currentOrderId }),
            dataType: 'json',
            success: function(response) {
                confirmationModal.hide();
                alert(response.message);
            },
            error: function(xhr) {
                confirmationModal.hide();
                let errorMsg = 'An unknown error occurred.';
                if(xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                }
                alert(errorMsg);
            }
        });
    });
});
</script>
</body>
</html>