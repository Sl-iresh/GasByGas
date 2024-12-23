<?php
$title = "Dashboard | Lpgas ";
include_once '../../includes/header.php';

$db = new Database();
$conn = $db->connect();

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
                            <td><?= htmlspecialchars($request['name']) ?></td>
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
                            <td><?= htmlspecialchars($request['name']) ?></td>
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



<?php include_once '../../includes/end.php'; ?>