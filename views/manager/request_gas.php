<?php
$title = "Request | GasbyGas ";
$page = "Request_gas";

include_once '../../includes/header.php';
$db = new Database();
$conn = $db->connect();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'manager') {
	header("Location: index.php");
	exit();
}
$user = $_SESSION['user'];


$user_id = $_SESSION['user']['user_id'];
// Fetch the outlet ID associated with the logged-in manager
$query = "SELECT id FROM outlets WHERE manager_id = :user_id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$outlet = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$outlet) {
	die("No outlet assigned to this manager.");
}
$outlet_id = $outlet['id'];
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


<main class="container mt-5">
	<div class="row justify-content-center">
		<div class="col-lg-6 col-md-8 col-sm-12">
			<div class="card p-4">
				<h3 class="text-center">Request Gas</h3>

				<!-- Display success or error messages -->
				<?php if (isset($_GET['success'])): ?>
					<div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
				<?php endif; ?>
				<?php if (isset($_GET['error'])): ?>
					<div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
				<?php endif; ?>

				<form action="../../controllers/process_request_business.php" method="POST">


				</br>
				<a target='_blank' href='../../public/Register.php'>	<button   style="background-color: #286ea7; color:#e9ecef;" type="button" class="btn order-btn w-100">Register New user </button> </a> </br>
				</br>




					<input type="hidden" name="outlet_id" value="<?= htmlspecialchars($outlet_id); ?>">
					<input type="hidden" name="new_user_id" id="new_user_id"> <!-- Added hidden input field -->

					<div class="form-group">
						<label for="user_search">Select New User</label>
						<input type="text" id="user_search" class="form-control" placeholder="Search by NIC or Name"  autocomplete="off" required>
						<div id="userResults" class="list-group mt-2"></div>
					</div>


					<div class="mb-3">
						<label for="gas_type" class="form-label">Select Gas Type</label>
						<select name="gas_type" id="gas_type" class="form-select" required>
							<option value="LPG">LPG</option>
							<option value="Industrial">Industrial</option>
							<option value="Propane">Propane</option>
						</select>
					</div>

					<div class="mb-3">
						<label for="pickup_date" class="form-label">Pickup Date</label>
						<input type="date" name="pickup_date" id="pickup_date" class="form-control" required>
					</div>
					
					<button type="submit" class="btn order-btn w-100">Submit Request</button>
				</form>
			</div>
		</div>
	</div>
</main>


<br>


<?php
// PHP code to check if 'gas_type' is set in URL parameters and add the value to the form
$gas_type = isset($_GET['gas_type']) ? $_GET['gas_type'] : '';

if (!empty($gas_type)) {
	echo "<script>document.getElementById('gas_type').value = '$gas_type';</script>";
}
?>

<?php include_once '../../includes/footer.php'; ?>



<script>
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
<?php include_once '../../includes/end.php'; ?>