<?php
$title = "Dashboard | Lpgas ";
include_once '../../includes/header.php';

$db = new Database();
$conn = $db->connect();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'manager') {
    header("Location: ../../public/login.php");
    exit();
}

$user = $_SESSION['user'];
$manager_id = $user['user_id'];

// Fetch requests for the manager's outlet using PDO


// Handle payment status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_payment'])) {
    $request_id = $_POST['request_id'];
    $payment_status = $_POST['payment_status'];

    $stmt = $conn->prepare("UPDATE gas_requests SET payment = :payment WHERE id = :id");
    $stmt->bindParam(':payment', $payment_status, PDO::PARAM_INT);
    $stmt->bindParam(':id', $request_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $success = "Payment status updated successfully!";
    } else { 
        $error = "Error updating payment status.";
    }
}

// Handle empty return status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_empty_return'])) {
    $request_id = $_POST['request_id'];
    $empty_return_status = $_POST['empty_return_status'];

    $stmt = $conn->prepare("UPDATE gas_requests SET empty_return = :empty_return WHERE id = :id");
    $stmt->bindParam(':empty_return', $empty_return_status, PDO::PARAM_INT);
    $stmt->bindParam(':id', $request_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $success = "Empty return status updated successfully!";
    } else {
        $error = "Error updating empty return status.";
    }
}



if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reschedule_request'])) {

    $request_id = $_POST['request_id'];
    $new_user_id = $_POST['new_user_id']; // The new user ID selected in the modal

    // Fetch the current request details
    $stmt = $conn->prepare("SELECT * FROM gas_requests WHERE id = :id");
    $stmt->bindParam(':id', $request_id, PDO::PARAM_INT);
    $stmt->execute();
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    // Update request with old and new user IDs
    $update_query = "UPDATE gas_requests SET old_user_id = :old_user_id, new_user_id = :new_user_id, user_id  = :user_id  WHERE id = :id";
    $stmt = $conn->prepare($update_query);
    $stmt->bindParam(':old_user_id', $request['user_id'], PDO::PARAM_INT);
    $stmt->bindParam(':new_user_id', $new_user_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $new_user_id, PDO::PARAM_INT);
    $stmt->bindParam(':id', $request_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        // Handle stock update (if necessary)
        // You can use the same logic for updating stock if needed, as per your initial code.

        // Redirect to the same page to refresh the request list
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $error = "Error rescheduling the request.";
    }
}


$query = "
    SELECT gr.id, gr.pickup_date, gr.status, gr.gas_type, gr.payment, gr.empty_return ,u.name AS consumer_name, o.name AS outlet_name 
    FROM gas_requests gr
    JOIN users u ON gr.user_id = u.user_id
    JOIN outlets o ON gr.outlet_id = o.id 
    WHERE o.manager_id = :manager_id
    ORDER BY gr.status, gr.pickup_date ASC";

$stmt = $conn->prepare($query);
$stmt->bindParam(':manager_id', $manager_id, PDO::PARAM_INT);
$stmt->execute();
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);


//Update  butteone request status and stock
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $request_id = $_POST['request_id'];
    $new_status = $_POST['status'];

    // Fetch current request details
    $stmt = $conn->prepare("SELECT * FROM gas_requests WHERE id = :id");
    $stmt->bindParam(':id', $request_id, PDO::PARAM_INT);
    $stmt->execute();
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch the current stock for the outlet
    $stmt = $conn->prepare("SELECT stock FROM gas_stock WHERE outlet_id = :outlet_id");
    $stmt->bindParam(':outlet_id', $request['outlet_id'], PDO::PARAM_INT);
    $stmt->execute();
    $outlet = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($outlet) {
        $gas_stock = json_decode($outlet['stock'], true);
        $gas_type = $request['gas_type']; // gas type from the current request

        // Handle stock update based on status change
        if ($request['status'] == 'scheduled' || $request['status'] == 'pending') {
            if ($new_status == 'canceled') {
                // Increment stock when status is updated from 'scheduled' to 'canceled' or 'reallocated'
                if (isset($gas_stock[$gas_type])) {
                    $gas_stock[$gas_type] += 1;
                    $updated_stock = json_encode($gas_stock);

                    // Update the stock in the database
                    $stmt = $conn->prepare("UPDATE gas_stock SET stock = ? WHERE outlet_id = ?");
                    $stmt->execute([$updated_stock, $request['outlet_id']]);
                }
            }
        } elseif ($new_status == 'scheduled') {
            // Decrement stock when status is updated to 'scheduled'
            if (isset($gas_stock[$gas_type]) && $gas_stock[$gas_type] > 0) {
                $gas_stock[$gas_type] -= 1;
                $updated_stock = json_encode($gas_stock);

                // Update the stock in the database
                $stmt = $conn->prepare("UPDATE gas_stock SET stock = ? WHERE outlet_id = ?");
                $stmt->execute([$updated_stock, $request['outlet_id']]);
            }
        }
    }

    // Update request status
    $update_query = "UPDATE gas_requests SET status = :status WHERE id = :id";
    $stmt = $conn->prepare($update_query);
    $stmt->bindParam(':status', $new_status, PDO::PARAM_STR);
    $stmt->bindParam(':id', $request_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $success = "Request status updated successfully!";
    } else {
        $error = "Error updating request status.";
    }

    // Refresh requests after update
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':manager_id', $manager_id, PDO::PARAM_INT);
    $stmt->execute();
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}



?>

<style>
    body {
        background-color: rgb(225, 226, 228);
        font-family: 'Roboto', sans-serif;
    }

    .card {
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .btn-primary {
        background-color: #007bff;
        border: none;
    }

    .btn-primary:hover {
        background-color: #0056b3;
    }

    .gas-card {
        transition: transform 0.3s, box-shadow 0.3s;
        border-radius: 15px;
        overflow: hidden;
        background: white;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .gas-card:hover {
        transform: scale(1.05);
        box-shadow: 0 8px 12px rgba(0, 0, 0, 0.2);
    }

    .order-btn {
        background-color: #28a745;
        border: none;
        font-size: 16px;
        font-weight: bold;
        transition: background-color 0.3s ease;
    }

    .order-btn:hover {
        background-color: #218838;
        color: #fff;
    }

    @media (max-width: 768px) {
        .gas-card {
            margin-bottom: 20px;
        }
    }
</style>

<header>
    <?php
    $page = basename($_SERVER['PHP_SELF'], ".php");
    include_once '../../includes/navbar.php'; ?>
</header>

<main>
    <div class="container mt-4" style="    background-color: white;
    border-radius: 12px;">
        <section id="tokens" class="mt-5">
            <h2>Manage Gas Requests</h2>
            <?php if (isset($success)) echo "<p class='text-success'>$success</p>"; ?>
            <?php if (isset($error)) echo "<p class='text-danger'>$error</p>"; ?>

            <div class="table-responsive">
                <table id="gasRequestsTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Consumer Name</th>
                            <th>Outlet</th>
                            <th>Pickup Date</th>
                            <th>Status</th>
                            <th>Payment Status</th> <!-- New Column -->
                            <th>Empty Return Status</th> <!-- New Column -->
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $request) { ?>
                            <tr>
                                <td><?= htmlspecialchars($request['id']) ?></td>
                                <td><?= htmlspecialchars($request['consumer_name']) ?></td>
                                <td><?= htmlspecialchars($request['outlet_name']) ?></td>
                                <td><?= htmlspecialchars($request['pickup_date']) ?></td>
                                <td><span class="badge bg-<?= $request['status'] == 'completed' ? 'success' : ($request['status'] == 'canceled' ? 'danger' : 'warning') ?>">
                                        <?= ucfirst(htmlspecialchars($request['status'])) ?></span>
                                </td>


                                <td>
                                    <!-- Display payment status -->
                                    <span class="badge bg-<?= $request['payment'] > 0 ? 'success' : 'warning' ?>">
                                        <?= $request['payment'] > 0 ? 'Paid' : 'Pending' ?>
                                    </span>
                                </td>
                                <td>
                                    <!-- Display empty return status -->
                                    <span class="badge bg-<?= $request['empty_return'] ? 'success' : 'danger' ?>">
                                        <?= $request['empty_return'] ? 'Returned' : 'Not Returned' ?>
                                    </span>
                                </td>





                                <td>
                                    <?php if ($request['status'] != 'completed' && $request['status'] != 'canceled') { ?>
                                        <form method="POST" class="d-flex align-items-center gap-2">
                                            <input type="hidden" name="request_id" value="<?= htmlspecialchars($request['id']) ?>">
                                            <select name="status" class="form-select form-select-sm" required>
                                                <option value="scheduled" <?= $request['status'] == 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                                                <option value="completed" <?= $request['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                                                <option value="canceled" <?= $request['status'] == 'canceled' ? 'selected' : '' ?>>Canceled</option>
                                            </select>
                                            <button type="submit" name="update_status" class="btn btn-sm btn-primary">Update</button>
                                        </form>
                                        
                                        <button type="button" class="btn btn-sm btn-warning mt-1" data-bs-toggle="modal" data-bs-target="#rescheduleModal" data-request-id="<?= htmlspecialchars($request['id']) ?>">Reschedule</button>
                                        <button type="button" class="btn btn-sm btn-info mt-1" data-bs-toggle="modal" data-bs-target="#updatePaymentModal" data-request-id="<?= htmlspecialchars($request['id']) ?>">Update Payment</button>
                                        <button type="button" class="btn btn-sm btn-warning mt-1" data-bs-toggle="modal" data-bs-target="#updateEmptyReturnModal" data-request-id="<?= htmlspecialchars($request['id']) ?>">Update Empty Return</button>

                                    <?php } ?>
                                    <br>

                                

                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>



<!-- Reschedule Modal -->
<div class="modal fade" id="rescheduleModal" tabindex="-1" aria-labelledby="rescheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rescheduleModalLabel">Reschedule Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="rescheduleForm" method="POST">
                    <input type="hidden" name="request_id" id="request_id">
                    <input type="hidden" name="new_user_id" id="new_user_id"> <!-- Added hidden input field -->

                    <div class="form-group">
                        <label for="user_search">Select New User</label>
                        <input type="text" id="user_search" class="form-control" placeholder="Search by NIC or Name" required>
                        <div id="userResults" class="list-group mt-2"></div>
                    </div>
                    <button type="submit" name="reschedule_request" class="btn btn-primary mt-3">Reschedule</button>
                </form>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="updatePaymentModal" tabindex="-1" aria-labelledby="updatePaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updatePaymentModalLabel">Update Payment Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updatePaymentForm" method="POST">
                    <input type="hidden" name="request_id" id="payment_request_id">
                    <div class="form-group">
                        <label for="payment_status">Select Payment Status</label>
                        <select name="payment_status" class="form-control" required>
                            <option value="0">Pending</option>
                            <option value="1">Paid</option>
                        </select>
                    </div>
                    <button type="submit" name="update_payment" class="btn btn-primary mt-3">Update Payment</button>
                </form>
            </div>
        </div>
    </div>
</div>

<br />


<div class="modal fade" id="updateEmptyReturnModal" tabindex="-1" aria-labelledby="updateEmptyReturnModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateEmptyReturnModalLabel">Update Empty Return Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updateEmptyReturnForm" method="POST">
                    <input type="hidden" name="request_id" id="empty_return_request_id">
                    <div class="form-group">
                        <label for="empty_return_status">Select Empty Return Status</label>
                        <select name="empty_return_status" class="form-control" required>
                            <option value="0">Not Returned</option>
                            <option value="1">Returned</option>
                        </select>
                    </div>
                    <button type="submit" name="update_empty_return" class="btn btn-primary mt-3">Update Empty Return</button>
                </form>
            </div>
        </div>
    </div>
</div>



<script>
    // Set request ID when the modal is opened
    document.addEventListener('DOMContentLoaded', function() {
        const rescheduleButtons = document.querySelectorAll('button[data-bs-toggle="modal"][data-bs-target="#rescheduleModal"]');
        rescheduleButtons.forEach(button => {
            button.addEventListener('click', function() {
                const requestId = this.getAttribute('data-request-id');
                document.getElementById('request_id').value = requestId;
            });
        });
    });



    document.addEventListener('DOMContentLoaded', function() {
    const paymentButtons = document.querySelectorAll('button[data-bs-toggle="modal"][data-bs-target="#updatePaymentModal"]');
    paymentButtons.forEach(button => {
        button.addEventListener('click', function() {
            const requestId = this.getAttribute('data-request-id');
            document.getElementById('payment_request_id').value = requestId;
        });
    });

    const emptyReturnButtons = document.querySelectorAll('button[data-bs-toggle="modal"][data-bs-target="#updateEmptyReturnModal"]');
    emptyReturnButtons.forEach(button => {
        button.addEventListener('click', function() {
            const requestId = this.getAttribute('data-request-id');
            document.getElementById('empty_return_request_id').value = requestId;
        });
    });
});

    // Search and select new user
    document.getElementById('user_search').addEventListener('input', function() {
        const searchQuery = this.value.trim();
        if (searchQuery.length > 1) {
            fetch('../../controllers/search_users.php?q=' + searchQuery)
                .then(response => response.json())
                .then(data => {
                    const userResults = document.getElementById('userResults');
                    userResults.innerHTML = '';
                    data.forEach(user => {
                        const userItem = document.createElement('div');
                        userItem.className = 'list-group-item list-group-item-action';
                        userItem.textContent = user.name;
                        userItem.setAttribute('data-user-id', user.user_id);
                        userItem.addEventListener('click', function() {
                            document.getElementById('user_search').value = user.name;
                            document.getElementById('new_user_id').value = user.user_id; // Set the new user ID
                            userResults.innerHTML = '';
                        });
                        userResults.appendChild(userItem);
                    });
                });
        } else {
            document.getElementById('userResults').innerHTML = '';
        }
    });
</script>





<?php include_once '../../includes/footer.php'; ?>
<script>
    $(document).ready(function() {
        $('#gasRequestsTable').DataTable();
    });
</script>

<?php include_once '../../includes/end.php'; ?>