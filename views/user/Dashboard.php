<?php
$title = "Dashboard | Lpgas ";
include_once '../../includes/header.php';
?>
<style>

	body
	{
		background-color:rgb(225, 226, 228);
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
			<h2 class="text-center mb-4">Order Gas</h2>
			<div class="row g-4 justify-content-center">
				<!-- Cylinder Type 1 -->
				<div class="col-md-4 col-sm-6">
					<div class="card gas-card">
						<div class="card-body text-center">
							<img src="../../assets/images/large-cylinder.png" alt="Large Cylinder" class="img-fluid gas-img mb-3">
							<h5 class="card-title">Large Cylinder</h5>
							<p class="card-text">15kg - Perfect for home use</p>
							<button class="btn btn-primary order-btn">Order Now</button>
						</div>
					</div>
				</div>
				<!-- Cylinder Type 2 -->
				<div class="col-md-4 col-sm-6">
					<div class="card gas-card">
						<div class="card-body text-center">
							<img src="../../assets/images/medium-cylinder.png" alt="Medium Cylinder" class="img-fluid gas-img mb-3">
							<h5 class="card-title">Medium Cylinder</h5>
							<p class="card-text">12kg - Ideal for small families</p>
							<button class="btn btn-primary order-btn">Order Now</button>
						</div>
					</div>
				</div>
				<!-- Cylinder Type 3 -->
				<div class="col-md-4 col-sm-6">
					<div class="card gas-card">
						<div class="card-body text-center">
							<img src="../../assets/images/small-cylinder.png" alt="Small Cylinder" class="img-fluid gas-img mb-3">
							<h5 class="card-title">Small Cylinder</h5>
							<p class="card-text">5kg - Easy to carry</p>
							<button class="btn btn-primary order-btn">Order Now</button>
						</div>
					</div>
				</div>
			</div>
		</section>



		



		<!-- Orders Section -->
		<section id="orders" class="mt-5">
			<h2>My Orders</h2>
			<table class="table table-striped mt-3">
				<thead class="table-dark">
					<tr>
						<th>#</th>
						<th>Order ID</th>
						<th>Status</th>
						<th>Delivery Date</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>1</td>
						<td>ORD12345</td>
						<td>Pending</td>
						<td>2025-01-15</td>
						<td><button class="btn btn-sm btn-primary">View</button></td>
					</tr>
					<tr>
						<td>2</td>
						<td>ORD12346</td>
						<td>Completed</td>
						<td>2025-01-10</td>
						<td><button class="btn btn-sm btn-primary">View</button></td>
					</tr>
				</tbody>
			</table>
		</section>
























		<!-- Tokens Section -->
		<section id="tokens" class="mt-5">
			<h2>My Tokens</h2>
			<table class="table table-striped mt-3">
				<thead class="table-dark">
					<tr>
						<th>#</th>
						<th>Token ID</th>
						<th>Status</th>
						<th>Pickup Period</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>1</td>
						<td>TOK12345</td>
						<td>Active</td>
						<td>2025-01-20 to 2025-02-03</td>
						<td><button class="btn btn-sm btn-primary">View</button></td>
					</tr>
					<tr>
						<td>2</td>
						<td>TOK12346</td>
						<td>Completed</td>
						<td>2025-01-05 to 2025-01-19</td>
						<td><button class="btn btn-sm btn-primary">View</button></td>
					</tr>
				</tbody>
			</table>
		</section>






	</div>

</main>



<?php include_once '../../includes/footer.php'; ?>