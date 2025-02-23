<?php
ob_start();
if (!isset($_SESSION['user'])) {
	header("Location: index.php");
	exit();
}
$user = $_SESSION['user'];
?>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-body-tertiary">
	<!-- Container wrapper -->
	<div class="container-fluid">
		<!-- Toggle button -->
		<button
			data-mdb-collapse-init
			class="navbar-toggler"
			type="button"
			data-mdb-target="#navbarSupportedContent"
			aria-controls="navbarSupportedContent"
			aria-expanded="false"
			aria-label="Toggle navigation">
			<i class="fas fa-bars"></i>
		</button>

		<!-- Collapsible wrapper -->
		<div class="collapse navbar-collapse" id="navbarSupportedContent">
			<!-- Navbar brand -->
			<a class="navbar-brand mt-2 mt-lg-0" href="#">
				<img
					src="../../assets/images/mdb-transaprent-noshadows.webp"
					height="50"
					alt="MDB Logo"
					loading="lazy" />
			</a>
			<!-- Left links -->

			<ul class="navbar-nav me-auto mb-2 mb-lg-0">
				<?php if ($user['role'] == 'admin') { ?>

					<li class="nav-item"><a class="nav-link  <?= $page == 'Dashboard' ? 'active' : '' ?>" href="Dashboard.php">Dashboard</a></li>
					<li class="nav-item"> <a class="nav-link  <?= $page == 'verfy_tokan' ? 'active' : '' ?>" href="verfy_tokan.php"> Verfy Tokan</a></li>
					<li class="nav-item"><a class="nav-link  <?= $page == 'business_approve_requests' ? 'active' : '' ?>" href="business_approve_requests.php">business Gas Request</a></li>
					<li class="nav-item"><a class="nav-link  <?= $page == 'admin_approve_requests' ? 'active' : '' ?>" href="admin_approve_requests.php">outlet Gas Request</a></li>
					<li class="nav-item"><a class="nav-link  <?= $page == 'manage_outlets' ? 'active' : '' ?>" href="manage_outlets.php">Manage Outlets</a></li>
					<!-- <li class="nav-item"><a class="nav-link  <?= $page == 'Manage Stock' ? 'active' : '' ?>" href="manage_stock.php">Manage Stock</a></li> -->
					<li class="nav-item"><a class="nav-link  <?= $page == 'manage_users' ? 'active' : '' ?>" href="manage_users.php">Manage Users</a></li>
					<li class="nav-item"><a class="nav-link  <?= $page == 'view_reports' ? 'active' : '' ?>" href="view_reports.php">View Reports</a></li>

				<?php } elseif ($user['role'] == 'manager') { ?>
					<li class="nav-item"> <a class="nav-link  <?= $page == 'Dashboard' ? 'active' : '' ?>" href="Dashboard.php">Dashboard</a></li>
					<li class="nav-item"> <a class="nav-link  <?= $page == 'verfy_tokan' ? 'active' : '' ?>" href="verfy_tokan.php"> Verfy Tokan</a></li>
					<li class="nav-item"> <a class="nav-link  <?= $page == 'manage_requests' ? 'active' : '' ?>" href="manage_requests.php">Manage Gas Requests</a></li>
					<li class="nav-item"> <a class="nav-link  <?= $page == 'outlet_request_gas' ? 'active' : '' ?>" href="outlet_request_gas.php"> Manage outlet Requests</a></li>
					<li class="nav-item"><a class="nav-link  <?= $page == 'view_reports' ? 'active' : '' ?>" href="view_reports.php">View Reports</a></li>
					<!-- <li class="nav-item"> <a class="nav-link  <?= $page == 'monitor_inventory' ? 'active' : '' ?>" href="monitor_inventory.php"> Monitor Inventory</a></li> -->

				<?php } elseif ($user['role'] == 'individual') { ?>
					<li class="nav-item"><a class="nav-link <?= $page == 'Dashboard' ? 'active' : '' ?>" href="Dashboard.php">Dashboard</a></li>
					<li class="nav-item"><a class="nav-link <?= $page == 'request_gas' ? 'active' : '' ?>" href="request_gas.php">Request Gas</a></li>
				<?php } elseif ($user['role'] == 'business') { ?>
					<li class="nav-item"><a class="nav-link  <?= $page == 'business_dashboard' ? 'active' : '' ?>" href="business_dashboard.php">Dashboard</a></li>
					<li class="nav-item"><a class="nav-link  <?= $page == 'business_request_gas' ? 'active' : '' ?>" href="business_request_gas.php">Request Gas</a></li>
				<?php } ?>
			</ul>

			</ul>


			<!-- Left links -->
		</div>
		<!-- Collapsible wrapper -->

		<!-- Right elements -->
		<div class="d-flex align-items-center">

			<!-- Notifications -->
			<!-- <div class="dropdown">
				<a
					data-mdb-dropdown-init
					class="link-secondary me-3 dropdown-toggle hidden-arrow"
					href="#"
					id="navbarDropdownMenuLink"
					role="button"
					aria-expanded="false">
					<i class="fas fa-bell"></i>
					<span class="badge rounded-pill badge-notification bg-danger">1</span>
				</a>
				<ul
					class="dropdown-menu dropdown-menu-end"
					aria-labelledby="navbarDropdownMenuLink">
					<li>
						<a class="dropdown-item" href="#">Some news</a>
					</li>
					<li>
						<a class="dropdown-item" href="#">Another news</a>
					</li>
					<li>
						<a class="dropdown-item" href="#">Something else here</a>
					</li>
				</ul>
			</div> -->
			<!-- Avatar -->
			<div class="dropdown">
				<a
					data-mdb-dropdown-init
					class="dropdown-toggle d-flex align-items-center hidden-arrow"
					href="#"
					id="navbarDropdownMenuAvatar"
					role="button"
					aria-expanded="false">
					<img
						src="../../assets/images/2.webp"
						class="rounded-circle"
						height="25"
						alt="Black and White Portrait of a Man"
						loading="lazy" />
				</a>
				<ul
					class="dropdown-menu dropdown-menu-end"
					aria-labelledby="navbarDropdownMenuAvatar">
					<li>
						<!-- <a class="dropdown-item" href="#">My profile</a> -->
					</li>
					<!-- <li>
						<a class="dropdown-item" href="#">Settings</a>
					</li> -->
					<li>
						<a class="dropdown-item" href="../../controllers/logout.php">Logout</a>
					</li>
				</ul>
			</div>
		</div>
		<!-- Right elements -->
	</div>
	<!-- Container wrapper -->
</nav>
<!-- Navbar -->