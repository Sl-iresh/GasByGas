<?php
$title = "Dashboard | GasbyGas ";
$page = "Dashboard";

include_once '../../includes/header.php';
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






	
	</div>

</main>



<?php include_once '../../includes/footer.php'; ?>
<?php include_once '../../includes/end.php'; ?>