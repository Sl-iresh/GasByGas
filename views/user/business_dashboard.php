<?php
$title = "Dashboard | Lpgas ";
include_once '../../includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../public/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Connect to database
$db = new Database();
$conn = $db->connect();



// Fetch gas requests for the logged-in user
$query = "SELECT * FROM business_gas_requests WHERE user_id = ? ORDER BY pickup_date DESC";
$stmt = $conn->prepare($query);
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    #order-gas {
        background: linear-gradient(to right, #f8f9fa, #e9ecef);
        padding: 50px 20px;
        border-radius: 15px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }

    .gas-card {
        transition: transform 0.3s, box-shadow 0.3s;
        border: none;
        border-radius: 15px;
        overflow: hidden;
        background: white;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .gas-card:hover {
        transform: scale(1.05);
        box-shadow: 0 8px 12px rgba(0, 0, 0, 0.2);
    }

    .gas-img {
        width: 150px;
        animation: bounce 2s infinite;
    }

    @keyframes bounce {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-10px);
        }
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

        .gas-img {
            width: 80px;
        }
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
    include_once '../../includes/navbar.php'; ?>
</header>


<main>

    <?php

    if (isset($_SESSION['success_message'])) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">'
            . $_SESSION['success_message'] .
            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
        unset($_SESSION['success_message']); // Clear the message after displaying it
    }

    ?>







    <!-- Your main content -->
    <div class="container mt-4">
        <section id="dashboard">
            <h2>Welcome to GasByGas</h2>
        </section>


        <section id="all-orders" class="mt-5" style="background-color:#fff; padding: 10px; border-radius:12px;">
            <h2>All Orders</h2>

            <!-- DataTable for All Orders -->

            <div class="table-responsive">
                <table id="pendingRequestsTable" class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Request ID</th>

                            <th>Request Data</th>
                            <th>Status</th>
                            <th>Requested At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $request) { ?>
                            <tr>
                                <td><?= htmlspecialchars($request['id']) ?></td>

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

                                <td>
                                    <!-- View Order Details -->
                                    <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#orderDetailsModal<?= $request['id'] ?>">View Details</button>

                                    <!-- Cancel Order -->
                                    <?php if ($request['status'] === 'pending'): ?>
                                        <form method="POST" style="display:inline-block;">
                                            <input type="hidden" name="order_id" value="<?= $request['id'] ?>">
                                            <button type="submit" name="cancel_order" class="btn btn-sm btn-danger">Cancel</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>


                            <!-- Order Details Modal -->
                            <div class="modal fade" id="orderDetailsModal<?= $request['id'] ?>" tabindex="-1" aria-labelledby="orderDetailsLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="orderDetailsLabel">Order Details - ORD<?= str_pad($request['id'], 5, '0', STR_PAD_LEFT) ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">

                                            <hr>
                                            <?php
                                            $requested_data = json_decode($request['request_data'], true);
                                            foreach ($requested_data as $gas_type => $quantity) { ?>

                                                <p><strong><?= htmlspecialchars($gas_type) ?>:</strong> <?= htmlspecialchars($quantity) ?> units</p>

                                            <?php } ?>
                                            <hr>


                                            <p><strong>Pickup Date:</strong> <?= htmlspecialchars($request['pickup_date']) ?></p>
                                            <!-- <p><strong>Pickup End Date:</strong> <?= htmlspecialchars($request['tolerance_end_date']) ?></p> -->
                                            <p><strong>Status:</strong>
                                                <?php
                                                $status = ucfirst($request['status']);
                                                echo $status;
                                                ?>
                                            </p>
                                            <p><strong>Token ID:</strong> ORD<?= str_pad($request['id'], 5, '0', STR_PAD_LEFT) ?></p>
                                            <p><strong>Order placed on:</strong> <?= $request['pickup_date'] ?></p>
                                            <!-- You can add more details like address, additional notes, etc. -->
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <?php } ?>
                    </tbody>
                </table>
            </div>

        </section>

        <br>












    </div>

</main>



<?php include_once '../../includes/footer.php'; ?>



<script>
    // DataTable Initialization
    $(document).ready(function() {
        $('#pendingRequestsTable').DataTable();

    });
</script>

<script>
    function orderGas(gasType) {
        window.location.href = "request_gas.php?gas_type=" + encodeURIComponent(gasType);
    }
</script>

<script>
    $(document).ready(function() {
        $('#ordersTable').DataTable({
            responsive: true,
            language: {
                search: "Search Orders:"
            }
        });
    });
</script>


<?php include_once '../../includes/end.php'; ?>