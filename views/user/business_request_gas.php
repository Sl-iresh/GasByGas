<?php
$title = "Dashboard | Lpgas ";
include_once '../../includes/header.php';

$db = new Database();
$conn = $db->connect();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'business') {
	header("Location: ../../public/login.php");
	exit();
}

$user_id = $_SESSION['user']['user_id'];
$success = $error = "";
// Handling form submission securely



?>
<style>
	body {
		background-color: #f8f9fa;
		font-family: 'Roboto', sans-serif;
	}

	.newcontainer {
		max-width: 600px;
		background: white;
		padding: 20px;
		border-radius: 10px;
		box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
	}

	.card {
		transition: transform 0.3s, box-shadow 0.3s;
		border-radius: 15px;
	}

	.card:hover {
		transform: scale(1.03);
		box-shadow: 0 8px 12px rgba(0, 0, 0, 0.2);
	}

	.disabled {
		background: #ccc !important;
		cursor: not-allowed;
	}
</style>
<header>
	<?php
	$page = basename($_SERVER['PHP_SELF'], ".php");
	include_once '../../includes/navbar.php';
	?>
</header>

<div class="container newcontainer newcontainer mt-5">
	<h2 class="text-center mb-4">Request Gas</h2>


	<!-- Success or Error Messages -->
	<?php if (!empty($success)) { ?>
		<div class="alert alert-success">
			<?= htmlspecialchars($success); ?>
		</div>
	<?php } elseif (!empty($error)) { ?>
		<div class="alert alert-danger">
			<?= htmlspecialchars($error); ?>
		</div>
	<?php } ?>


	<form method="POST">


		<div id="stock-warning" class="text-danger"></div>
		<div class="mb-3">
			<label for="LPG" class="form-label">LPG Quantity</label>
			<input type="number" name="LPG" id="LPG" class="form-control" required min="0">
		</div>
		<div class="mb-3">
			<label for="Propane" class="form-label">Propane Quantity</label>
			<input type="number" name="Propane" id="Propane" class="form-control" required min="0">
		</div>
		<div class="mb-3">
			<label for="Industrial" class="form-label">Industrial Quantity</label>
			<input type="number" name="Industrial" id="Industrial" class="form-control" required min="0">
		</div>

		<div class="mb-3">
			<label for="pickup_date" class="form-label">Pickup Date</label>
			<input type="date" name="pickup_date" id="pickup_date" class="form-control" required>
		</div>



		<button type="submit" class="btn btn-primary w-100">Submit Request</button>
	</form>
</div>
<br>
<br>


<?php include_once '../../includes/footer.php'; ?>
<?php include_once '../../includes/end.php'; ?>