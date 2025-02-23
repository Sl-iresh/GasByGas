<?php
$title = "Dashboard | GasbyGas ";
$page = "Dashboard";

include_once '../../includes/header.php';
require_once '../../controllers/SMSApi.php'; // Include the SMSApi class

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../public/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Connect to database
$db = new Database();
$conn = $db->connect();

// Handle cancel order request
if (isset($_POST['cancel_order'])) {
    $order_id = $_POST['order_id'];

    // Fetch order details and user phone number before updating
    $fetch_order_query = "
        SELECT gr.outlet_id, gr.gas_type, gr.qty, u.contact_number AS phone_number 
        FROM gas_requests gr
        JOIN users u ON gr.user_id = u.user_id
        WHERE gr.id = ? AND gr.user_id = ? AND gr.status = 'pending'
    ";
    $stmt = $conn->prepare($fetch_order_query);
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($order) {
        $outlet_id = $order['outlet_id'];
        $gas_type = $order['gas_type'];
        $qty = $order['qty'];
        $phoneNumber = $order['phone_number']; // Get phone number from the 'users' table

        // Update order status to 'canceled'
        $cancel_query = "UPDATE gas_requests SET status = 'canceled' WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($cancel_query);
        $stmt->execute([$order_id, $user_id]);

        // Retrieve current stock for the outlet
        $stock_query = "SELECT stock FROM gas_stock WHERE outlet_id = ?";
        $stmt = $conn->prepare($stock_query);
        $stmt->execute([$outlet_id]);
        $stock_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($stock_data) {
            // Decode JSON stock data
            $stock = json_decode($stock_data['stock'], true);
            
            // Increase stock for the canceled gas type
            if (isset($stock[$gas_type])) {
                $stock[$gas_type] += $qty;
            } else {
                $stock[$gas_type] = $qty; // If gas type doesn't exist, initialize it
            }

            // Update stock in database
            $update_stock_query = "UPDATE gas_stock SET stock = ? WHERE outlet_id = ?";
            $stmt = $conn->prepare($update_stock_query);
            $stmt->execute([json_encode($stock), $outlet_id]);
        }

        // Send SMS notification
      
        $message = "Your gas order for $qty x $gas_type has been canceled. If this was a mistake, please reorder.";
        $smsApi = new SMSApi();
        $smsResponse = $smsApi->sendSMS([$phoneNumber], $message);
    }

    // Redirect after canceling
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}



// Fetch gas requests for the logged-in user
$query = "SELECT * FROM gas_requests WHERE user_id = ? ORDER BY pickup_date DESC";
$stmt = $conn->prepare($query);
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);




// Fetch user's role
$role_query = "SELECT role FROM users WHERE user_id = ?";
$stmt = $conn->prepare($role_query);
$stmt->execute([$user_id]);
$user_role = $stmt->fetchColumn();

// Fetch user's purchase limit
$limit_query = "SELECT order_limit FROM order_limits WHERE user_type = ?";
$stmt = $conn->prepare($limit_query);
$stmt->execute([$user_role]);
$purchase_limit = $stmt->fetchColumn();

// Count pending/scheduled orders for the user
$pending_orders_query = "SELECT COUNT(*) FROM gas_requests 
                         WHERE user_id = ? AND status IN ('pending', 'scheduled')";
$stmt = $conn->prepare($pending_orders_query);
$stmt->execute([$user_id]);
$pending_orders_count = $stmt->fetchColumn();

// Check if user has reached their purchase limit
$disable_order_button = ($pending_orders_count >= $purchase_limit);
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
</style>

<header>
	<?php
	$page = basename($_SERVER['PHP_SELF'], ".php");
	include_once '../../includes/navbar.php'; ?>
</header>


<main>
	<!-- Your main content -->
	<div class="container mt-4">
		<section id="dashboard">
			<h2>Welcome to GasByGas</h2>
			<div class="row mt-4">
				<!-- <div class="col-md-4">
					<div class="card p-3 text-center">
						<h5>Total Orders</h5>
						<p class="fs-4">25</p>
					</div>
				</div> -->
				<!-- <div class="col-md-4">
					<div class="card p-3 text-center">
						<h5>Pending Tokens</h5>
						<p class="fs-4">10</p>
					</div>
				</div> -->
				<!-- <div class="col-md-4">
					<div class="card p-3 text-center">
						<h5>Nearby Outlets</h5>
						<p class="fs-4">15</p>
					</div>
				</div> -->
			</div>
		</section>















		<section id="order-gas" class="mt-5">
    <h2 class="text-center mb-4">Order Gas</h2>
    
    <!-- Display warning if limit is reached -->
    <?php if ($disable_order_button): ?>
        <div class="alert alert-warning mt-3">
            You have reached your maximum allowed orders (Limit: <?= $purchase_limit ?>). 
            Please complete or cancel existing orders to place new ones.
        </div>
    <?php endif; ?>

    <div class="row g-4 justify-content-center">
        <!-- Cylinder Type 1 -->
        <div class="col-md-4 col-sm-6">
            <div class="card gas-card">
                <div class="card-body text-center">
                    <img src="../../assets/images/large-cylinder.png" alt="Large Cylinder" class="img-fluid gas-img mb-3">
                    <h5 class="card-title">LPG Cylinder</h5>
                    <p class="card-text">15kg - Perfect for home use</p>
                    <button class="btn btn-primary order-btn" 
                            onclick="orderGas('LPG')" 
                            <?= $disable_order_button ? 'disabled' : '' ?>>
                        Order Now
                    </button>
                </div>
            </div>
        </div>
        <!-- Cylinder Type 2 -->
        <div class="col-md-4 col-sm-6">
            <div class="card gas-card">
                <div class="card-body text-center">
                    <img src="../../assets/images/medium-cylinder.png" alt="Medium Cylinder" class="img-fluid gas-img mb-3">
                    <h5 class="card-title">Industrial Cylinder</h5>
                    <p class="card-text">12kg - Ideal for small families</p>
                    <button class="btn btn-primary order-btn" 
                            onclick="orderGas('Industrial')" 
                            <?= $disable_order_button ? 'disabled' : '' ?>>
                        Order Now
                    </button>
                </div>
            </div>
        </div>
        <!-- Cylinder Type 3 -->
        <div class="col-md-4 col-sm-6">
            <div class="card gas-card">
                <div class="card-body text-center">
                    <img src="../../assets/images/small-cylinder.png" alt="Small Cylinder" class="img-fluid gas-img mb-3">
                    <h5 class="card-title">Propane Cylinder</h5>
                    <p class="card-text">5kg - Easy to carry</p>
                    <button class="btn btn-primary order-btn" 
                            onclick="orderGas('Propane')" 
                            <?= $disable_order_button ? 'disabled' : '' ?>>
                        Order Now
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>









		<section id="orders" class="mt-5" style=" background-color:#fff; padding: 10px; border-radius:12px;">
			<h2>My Orders</h2>
			<div class="table-responsive">
				<table id="ordersTable" class="table table-striped mt-3">
					<thead class="table-dark">
						<tr>
							<th>#</th>
							<th>Token ID</th>
							<th>Gas Type</th>
							<th>Status</th>
							<th>Pickup Date</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody>
						<?php if (count($orders) > 0): ?>
							<?php foreach ($orders as $index => $order): ?>
								<tr>
									<td><?= $index + 1 ?></td>
									<td>ORD<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></td>
									<td><?= htmlspecialchars($order['gas_type']) ?></td>
									<td>
										<?php
										$statusClass = '';
										if ($order['status'] === 'pending') {
											$statusClass = 'text-warning';
										} elseif ($order['status'] === 'completed') {
											$statusClass = 'text-success';
										} elseif ($order['status'] === 'rejected') {
											$statusClass = 'text-danger';
										}
										elseif ($order['status'] === 'canceled') {
											$statusClass = 'text-danger';
										}
										elseif ($order['status'] === 'reallocated') {
											$statusClass = 'text-danger';
										}
										?>
										<span class="<?= $statusClass ?>"><?= ucfirst($order['status'] ?? 'N/A') ?></span>
									</td>
									<td><?= htmlspecialchars($order['pickup_date'] ?? 'N/A') ?></td>
									<td>
										<!-- View Order Details -->
										<button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#orderDetailsModal<?= $order['id'] ?>">View Details</button>

										<!-- Cancel Order -->
										<?php if ($order['status'] === 'pending'): ?>
											<form method="POST" style="display:inline-block;">
												<input type="hidden" name="order_id" value="<?= $order['id'] ?>">
												<button type="submit" name="cancel_order" class="btn btn-sm btn-danger">Cancel</button>
											</form>
										<?php endif; ?>
									</td>
								</tr>

								<!-- Order Details Modal -->
								<div class="modal fade" id="orderDetailsModal<?= $order['id'] ?>" tabindex="-1" aria-labelledby="orderDetailsLabel" aria-hidden="true">
									<div class="modal-dialog">
										<div class="modal-content">
											<div class="modal-header">
												<h5 class="modal-title" id="orderDetailsLabel">Order Details - ORD<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></h5>
												<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
											</div>
											<div class="modal-body">
												<p><strong>Gas Type:</strong> <?= htmlspecialchars($order['gas_type']) ?></p>
												<p><strong>Pickup Date:</strong> <?= htmlspecialchars($order['pickup_date']) ?></p>
												<p><strong>Pickup End Date:</strong> <?= htmlspecialchars($order['tolerance_end_date']) ?></p>
												<p><strong>Status:</strong>
													<?php
													$status = ucfirst($order['status']);
													echo $status;
													?>
												</p>
												<p><strong>Token ID:</strong> ORD<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></p>
												<p><strong>Order placed on:</strong> <?= $order['pickup_date'] ?></p>
												<!-- You can add more details like address, additional notes, etc. -->
											</div>
											<div class="modal-footer">
												<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
											</div>
										</div>
									</div>
								</div>
							<?php endforeach; ?>
						<?php else: ?>
							<tr>
								<td colspan="6" class="text-center">No orders found.</td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</section>






		<br>












	</div>

</main>



<?php include_once '../../includes/footer.php'; ?>

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