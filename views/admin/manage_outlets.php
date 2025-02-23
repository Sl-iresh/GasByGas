<?php
$title = "Dashboard | Lpgas ";
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


	table {
		width: 100%;
		border-collapse: collapse;
		background: white;
		border-radius: 10px;
		overflow: hidden;
	}

	table thead tr th {
		background-color: #007bff !important;
		color: white !important;
	}

	table th,
	table td {
		padding: 12px;
		text-align: center;
		border: 1px solid #dee2e6;
	}

	table tbody tr:nth-child(even) {
		background-color: #f8f9fa;
	}

	table tbody tr:hover {
		background-color: #e9ecef;
	}

	.table-container {
		margin-top: 20px;
		overflow-x: auto;
		/* Add horizontal scrolling for smaller screens */
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

		</section>















		<!-- Orders Section -->
		<section id="orders" class="mt-5">
			<?php
			if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
				header("Location: index.php");
				exit();
			}
			$db = new Database();
			$conn = $db->connect();


			// Fetch current managers
			$managers = $conn->query("SELECT user_id , name FROM users WHERE role = 'manager'");

			// Fetch all outlets
			$outlets = $conn->query("SELECT * FROM outlets");

			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				// Handle form submissions for adding or updating outlets
				if (isset($_POST['add_outlet'])) {
					// Add new outlet
					$outlet_name = $_POST['outlet_name'];
					$district = $_POST['district'];
					$manager_id = $_POST['manager_id'];

					$query = "INSERT INTO outlets (name, district, manager_id) VALUES ('$outlet_name', '$district', '$manager_id')";
					if ($conn->query($query)) {
						$success = "New outlet added successfully!";
					} else {
						//$error = "Error: " . $conn->error;
					}
				} elseif (isset($_POST['update_outlet'])) {
					// Update outlet details
					$outlet_id = $_POST['outlet_id'];
					$outlet_name = $_POST['outlet_name'];
					$district = $_POST['district'];
					$manager_id = $_POST['manager_id'];

					$query = "UPDATE outlets SET name = '$outlet_name', district = '$district', manager_id = '$manager_id' WHERE id = '$outlet_id'";
					if ($conn->query($query)) {
						$success = "Outlet details updated successfully!";
					} else {
						//	$error = "Error: " . $conn->error;
					}
				}
				header("Location: " . $_SERVER['PHP_SELF']);
				exit;
			}
			?>


			<?php
			if (isset($_SESSION['success'])) {
				echo "<p class='text-success'>" . $_SESSION['success'] . "</p>";
				unset($_SESSION['success']);
			}
			if (isset($_SESSION['error'])) {
				echo "<p class='text-danger'>" . $_SESSION['error'] . "</p>";
				unset($_SESSION['error']);
			}
			?>


			<!-- Outlet Form to Add New Outlet -->
			<h4>Add New Outlet</h4>
			<form method="POST" action=" ../../controllers/add_outlet.php">
				<div class="mb-3">
					<label for="outlet_name" class="form-label">Outlet Name</label>
					<input type="text" name="outlet_name" id="outlet_name" class="form-control" required>
				</div>



				<div class="mb-3">
					<label for="district" class="form-label">District</label>
					<select name="district" id="district" class="form-control" required>
						<option value="">Select District</option>
						<?php
						foreach ($districts as $district) {
							echo "<option value='$district'>$district</option>";
						}
						?>
					</select>
				</div>


				<div class="mb-3">
					<label for="manager_id" class="form-label">Select Manager</label>
					<select name="manager_id" id="manager_id" class="form-control" required>
						<?php while ($manager = $managers->fetch(PDO::FETCH_ASSOC)) { ?>
							<option value="<?= $manager['user_id'] ?>"><?= $manager['name'] ?></option>
						<?php } ?>
					</select>
				</div>
				<button type="submit" name="add_outlet" class="btn btn-primary">Add Outlet</button>
			</form>

			<!-- List of Existing Outlets -->
			<h4 class="mt-5">Existing Outlets</h4>
			<div class="table-container">
				<table class="table">
					<thead>
						<tr>
							<th>#</th>
							<th>Outlet Name</th>
							<th>District</th>
							<th>Manager</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody>
						<?php while ($outlet = $outlets->fetch(PDO::FETCH_ASSOC)) { ?>
							<tr>
								<td><?= $outlet['id'] ?></td>
								<td><?= $outlet['name'] ?></td>
								<td><?= $outlet['district'] ?></td>
								<td>
									<?php
									$manager_id = $outlet['manager_id'];
									$manager = $conn->query("SELECT name FROM users WHERE user_id = '$manager_id'")->fetch(PDO::FETCH_ASSOC);
									echo $manager ? $manager['name'] : 'No manager';
									?>
								</td>
								<td>
									<!-- Edit Outlet -->
									<button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editOutletModal<?= $outlet['id'] ?>">Edit</button>

									<!-- Edit Modal -->
									<div class="modal fade" id="editOutletModal<?= $outlet['id'] ?>" tabindex="-1" aria-labelledby="editOutletModalLabel" aria-hidden="true">
										<div class="modal-dialog">
											<div class="modal-content">
												<div class="modal-header">
													<h5 class="modal-title" id="editOutletModalLabel">Edit Outlet</h5>
													<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
												</div>
												<div class="modal-body">
													<form method="POST">
														<input type="hidden" name="outlet_id" value="<?= $outlet['id'] ?>">
														<div class="mb-3">
															<label for="outlet_name" class="form-label">Outlet Name</label>
															<input type="text" name="outlet_name" class="form-control" value="<?= $outlet['name'] ?>" required>
														</div>
														<div class="mb-3">
															<label for="district" class="form-label">District</label>
															<input type="text" name="district" class="form-control" value="<?= $outlet['district'] ?>" required>
														</div>
														<div class="mb-3">
															<label for="manager_id" class="form-label">Select Manager</label>
															<select name="manager_id" class="form-control" required>
																<?php
																// Re-fetch the list of managers to ensure it's up-to-date
																$managers = $conn->query("SELECT user_id , name FROM users WHERE role = 'manager'");
																while ($manager = $managers->fetch(PDO::FETCH_ASSOC)) {
																	$selected = $manager['user_id'] == $outlet['manager_id'] ? 'selected' : '';
																	echo "<option value='{$manager['user_id']}' $selected>{$manager['name']}</option>";
																}
																?>
															</select>
														</div>
														<button type="submit" name="update_outlet" class="btn btn-primary">Update Outlet</button>
													</form>
												</div>
											</div>
										</div>
									</div>
								</td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
	</div>






	</section>






















	</div>

</main>



<?php include_once '../../includes/footer.php'; ?>
<?php include_once '../../includes/end.php'; ?>