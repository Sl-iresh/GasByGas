<?php
$title = "Dashboard | GasbyGas ";
$page = "Dashboard";

include_once '../../includes/header.php';



$db = new Database();
$conn = $db->connect();



// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_limits'])) {
        // Update purchase limits
        $individual_limit = (int)$_POST['individual_limit'];
        $business_limit = (int)$_POST['business_limit'];
        
        $stmt = $conn->prepare("UPDATE order_limits SET order_limit = ? WHERE user_type = ?");
        $stmt->execute([$individual_limit, 'individual']);
        $stmt->execute([$business_limit, 'business']);
    } elseif (isset($_POST['update_prices'])) {
        // Update gas prices
        foreach ($_POST['prices'] as $gas_type => $prices) {
            $stmt = $conn->prepare("UPDATE gas_prices 
                                   SET cost_price = ?, selling_price = ?
                                   WHERE gas_type = ?");
            $stmt->execute([
                $prices['cost'],
                $prices['selling'],
                $gas_type
            ]);
        }
    }
}

$limits_stmt = $conn->query("SELECT * FROM order_limits");
$raw_limits = $limits_stmt->fetchAll(PDO::FETCH_UNIQUE);

$limits = [];
foreach ($raw_limits as $id => $row) {
    $limits[$row['user_type']] = $row;
}

$prices_stmt = $conn->query("SELECT * FROM gas_prices");
$gas_prices = [];
while ($row = $prices_stmt->fetch(PDO::FETCH_ASSOC)) {
    $gas_prices[$row['gas_type']] = $row;
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
				<div class="col-md-4">
					<div class="card p-3 text-center">
						<h5>Total Orders</h5>
						<p class="fs-4">25</p>
					</div>
				</div>
				<div class="col-md-4">
					<div class="card p-3 text-center">
						<h5>Pending Tokens</h5>
						<p class="fs-4">10</p>
					</div>
				</div>
				<div class="col-md-4">
					<div class="card p-3 text-center">
						<h5>Nearby Outlets</h5>
						<p class="fs-4">15</p>
					</div>
				</div>
			</div>
		</section>






		<section id="order-gas" class="mt-5">
		<h2 class="mb-4">Admin Dashboard</h2>

<!-- Purchase Limits Section -->
<div class="card mb-4">
	<div class="card-header bg-primary text-white">
		<h4>Purchase Limits Management</h4>
	</div>
	<div class="card-body">
		<form method="POST">
			<div class="row">
				<div class="col-md-6">
					<div class="mb-3">
						<label class="form-label">Individual User Limit</label>
						<input type="number" class="form-control" 
							   name="individual_limit" 
							   value="<?= $limits['individual']['order_limit'] ?? 2 ?>" 
							   min="1" required>
					</div>
				</div>
				<div class="col-md-6">
					<div class="mb-3">
						<label class="form-label">Business User Limit</label>
						<input type="number" class="form-control" 
							   name="business_limit" 
							   value="<?= $limits['business']['order_limit'] ?? 10 ?>" 
							   min="1" required>
					</div>
				</div>
			</div>
			<button type="submit" name="update_limits" class="btn btn-primary">
				Update Limits
			</button>
		</form>
	</div>
</div>

<!-- Gas Prices Section -->
<div class="card">
	<div class="card-header bg-success text-white">
		<h4>Gas Prices Management</h4>
	</div>
	<div class="card-body">
		<form method="POST">
			<div class="row">
				<?php foreach ($gas_prices as $gas_type => $price): ?>
				<div class="col-md-4 mb-4">
					<div class="card">
						<div class="card-header">
							<h5><?= $gas_type ?> Prices</h5>
						</div>
						<div class="card-body">
							<div class="mb-3">
								<label class="form-label">Cost Price ($)</label>
								<input type="number" step="0.01" class="form-control"
									   name="prices[<?= $gas_type ?>][cost]"
									   value="<?= $price['cost_price'] ?>" required>
							</div>
							<div class="mb-3">
								<label class="form-label">Selling Price ($)</label>
								<input type="number" step="0.01" class="form-control"
									   name="prices[<?= $gas_type ?>][selling]"
									   value="<?= $price['selling_price'] ?>" required>
							</div>
						</div>
					</div>
				</div>
				<?php endforeach; ?>
			</div>
			<button type="submit" name="update_prices" class="btn btn-success">
				Update Prices
			</button>
		</form>
	</div>
</div>
		</section>
	</div>

</main>
<br>


<?php include_once '../../includes/footer.php'; ?>
<?php include_once '../../includes/end.php'; ?>