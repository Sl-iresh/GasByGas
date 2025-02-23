<?php
$title = "Dashboard | Lpgas ";
include_once '../../includes/header.php';

$db = new Database();
$conn = $db->connect();

// Get all pending gas requests using a prepared statement
$stmt = $conn->prepare("SELECT * FROM outlet_gas_requests WHERE status = 'pending' OR status = 'approved' ");
$stmt->execute();
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT * FROM outlet_gas_requests WHERE status != 'pending' ORDER BY id DESC ");
$stmt->execute();
$all_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $request_id = $_POST['request_id'];
    $status = $_POST['status'];

    // Validate the status value
    if (!in_array($status, ['approved', 'rejected'])) {
        die("Invalid status value.");
    }

    // Fetch the request details securely
    $stmt = $conn->prepare("SELECT * FROM outlet_gas_requests WHERE id = :id");
    $stmt->bindParam(':id', $request_id, PDO::PARAM_INT);
    $stmt->execute();
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$request) {
        die("Request not found.");
    }

    $requested_data = json_decode($request['request_data'], true);
    if ($requested_data === null) {
        die("Invalid JSON format in request data.");
    }

    // Fetch the current stock of the outlet securely
    $stmt = $conn->prepare("SELECT stock FROM gas_stock WHERE outlet_id = :outlet_id");
    $stmt->bindParam(':outlet_id', $request['outlet_id'], PDO::PARAM_INT);
    $stmt->execute();
    $stock = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$stock) {


        if ($status == 'approved') {


            $stmt = $conn->prepare("UPDATE outlet_gas_requests SET status = 'approved' WHERE id = :id");
            $stmt->bindParam(':id', $request_id, PDO::PARAM_INT);
            $stmt->execute();

            $updated_stock = json_encode($requested_data);
            $stmt = $conn->prepare("Insert gas_stock SET stock = :stock ,outlet_id = :outlet_id ");
            $stmt->bindParam(':stock', $updated_stock, PDO::PARAM_STR);
            $stmt->bindParam(':outlet_id', $request['outlet_id'], PDO::PARAM_INT);
            $stmt->execute();
            //   die("Stock new record");

        }
    } else {

        $gas_stock = json_decode($stock['stock'], true);
        if ($gas_stock === null) {
            die("Invalid JSON format in stock data.");

            $stmt = $conn->prepare("UPDATE outlet_gas_requests SET status = 'approved' WHERE id = :id");
            $stmt->bindParam(':id', $request_id, PDO::PARAM_INT);
            $stmt->execute();
        }

        if ($status == 'approved') {
            // Validate stock before approval
            $can_approve = true;
            foreach ($requested_data as $gas_type => $quantity) {
                if (!isset($gas_stock[$gas_type])) {
                    $can_approve = false;
                    break;
                }
            }

            if ($can_approve) {
                // Approve the request
                $stmt = $conn->prepare("UPDATE outlet_gas_requests SET status = 'approved' WHERE id = :id");
                $stmt->bindParam(':id', $request_id, PDO::PARAM_INT);
                $stmt->execute();

                // Update stock
                foreach ($requested_data as $gas_type => $quantity) {
                    $gas_stock[$gas_type] = isset($gas_stock[$gas_type]) ? $gas_stock[$gas_type] + $quantity : $quantity;
                }

                $updated_stock = json_encode($gas_stock);
                $stmt = $conn->prepare("UPDATE gas_stock SET stock = :stock WHERE outlet_id = :outlet_id");
                $stmt->bindParam(':stock', $updated_stock, PDO::PARAM_STR);
                $stmt->bindParam(':outlet_id', $request['outlet_id'], PDO::PARAM_INT);
                $stmt->execute();
            } else {
                // Reject if stock validation fails
                $stmt = $conn->prepare("UPDATE outlet_gas_requests SET status = 'rejected' WHERE id = :id");
                $stmt->bindParam(':id', $request_id, PDO::PARAM_INT);
                $stmt->execute();
            }
        } else {
            // Reject the request if not approved
            $stmt = $conn->prepare("UPDATE outlet_gas_requests SET status = 'rejected' WHERE id = :id");
            $stmt->bindParam(':id', $request_id, PDO::PARAM_INT);
            $stmt->execute();
        }
    }



    // Redirect to avoid resubmission
    header("Location: admin_approve_requests.php");
    exit();
}
?>

<style>
    /* Custom CSS for better UI and card styles */
    body {
        background-color: rgb(225, 226, 228);
        font-family: 'Roboto', sans-serif;
    }

    .btn-primary {
        background-color: #007bff;
        border: none;
    }

    .btn-primary:hover {
        background-color: #0056b3;
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

    .modal-dialog {
        max-width: 600px;
    }

    /* Style for request data table */
    .request-data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .request-data-table th,
    .request-data-table td {
        padding: 10px;
        text-align: left;
        border: 1px solid #ddd;
    }

    .request-data-table th {
        background-color: #f2f2f2;
    }
</style>

<header>
    <?php
    $page = basename($_SERVER['PHP_SELF'], ".php");
    include_once '../../includes/navbar.php';
    ?>
</header>

<main>
    <div class="container mt-4">
        <section id="orders" class="mt-5" style="background-color:#fff; padding: 10px; border-radius:12px;">
            <h2>Pending Gas Requests</h2>

            <!-- DataTable for Pending Requests -->
            <table id="pendingRequestsTable" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Request ID</th>
                        <th>Requested By</th>
                        <th>Request Data</th>
                        <th>Requested At</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $request) { ?>
                        <tr>
                            <td><?= htmlspecialchars($request['id']) ?></td>
                            <td><?= htmlspecialchars($request['outlet_id']) ?></td>
                            <td>
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Gas Type</th>
                                            <th>Quantity</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $requested_data = json_decode($request['request_data'], true);
                                        foreach ($requested_data as $gas_type => $quantity) { ?>
                                            <tr>
                                                <td><?= htmlspecialchars($gas_type) ?></td>
                                                <td><?= htmlspecialchars($quantity) ?> units</td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </td>
                            <td><?= htmlspecialchars($request['requested_at']) ?></td>
                            <td><?= htmlspecialchars($request['status']) ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="request_id" value="<?= htmlspecialchars($request['id']) ?>">

                                    <!-- Only enable Approve and Reject for pending status -->
                                    <button type="submit" name="status" value="approved" class="btn btn-success" <?= ($request['status'] != 'pending') ? 'disabled' : '' ?>>Approve</button>
                                    <button type="submit" name="status" value="rejected" class="btn btn-danger" <?= ($request['status'] != 'pending') ? 'disabled' : '' ?>>Reject</button>
                                </form>
                                <!-- Reschedule button always enabled -->
                                <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#rescheduleModal" data-id="<?= htmlspecialchars($request['id']) ?>">Reschedule</button>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

            <!-- Modal for Rescheduling -->
            <div class="modal fade" id="rescheduleModal" tabindex="-1" aria-labelledby="rescheduleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="rescheduleModalLabel">Reschedule Request</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form method="POST" id="rescheduleForm">
                                <input type="hidden" name="request_id" id="rescheduleRequestId">
                                <div class="mb-3">
                                    <label for="newDate" class="form-label">New Request Date</label>
                                    <input type="date" class="form-control" name="new_date" id="newDate" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Reschedule</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <br>

        <section id="all-orders" class="mt-5" style="background-color:#fff; padding: 10px; border-radius:12px;">
            <h2>All Orders</h2>

            <!-- DataTable for All Orders -->
            <table id="allRequestsTable" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Request ID</th>
                        <th>Requested By</th>
                        <th>Request Data</th>
                        <th>Status</th>
                        <th>Requested At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_requests as $request) { ?>
                        <tr>
                            <td><?= htmlspecialchars($request['id']) ?></td>
                            <td><?= htmlspecialchars($request['outlet_id']) ?></td>
                            <td>
                                <table class="request-data-table">
                                    <thead>
                                        <tr>
                                            <th>Gas Type</th>
                                            <th>Quantity</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $requested_data = json_decode($request['request_data'], true);
                                        foreach ($requested_data as $gas_type => $quantity) { ?>
                                            <tr>
                                                <td><?= htmlspecialchars($gas_type) ?></td>
                                                <td><?= htmlspecialchars($quantity) ?> units</td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </td>
                            <td><?= htmlspecialchars($request['status']) ?></td>
                            <td><?= htmlspecialchars($request['requested_at']) ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </section>
    </div>
</main>

<br><br><br>

<?php include_once '../../includes/footer.php'; ?>

<script>
    // DataTable Initialization
    $(document).ready(function() {
        $('#pendingRequestsTable').DataTable();

        // Set request ID in reschedule modal
        $('#rescheduleModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var requestId = button.data('id'); // Extract request ID
            var modal = $(this);
            modal.find('#rescheduleRequestId').val(requestId);
        });

        // Handle rescheduling form submission
        $('#rescheduleForm').submit(function(e) {
            e.preventDefault();
            var requestId = $('#rescheduleRequestId').val();
            var newDate = $('#newDate').val();
            // Send AJAX request to update the date (you can add server-side processing here)
            $.ajax({
                url: '../../controllers/reschedule_request.php',
                method: 'POST',
                data: {
                    request_id: requestId,
                    new_date: newDate
                },
                success: function(response) {
                    alert('Request rescheduled successfully.');
                    location.reload(); // Reload the page to reflect changes
                },
                error: function() {
                    alert('Error rescheduling request.');
                }
            });
        });
    });
</script>

<?php include_once '../../includes/end.php'; ?>