<?php
$title = "Dashboard | Lpgas ";
include_once '../../includes/header.php';

$db = new Database();
$conn = $db->connect();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'manager') {
    header("Location: ../../public/login.php");
    exit();
}


?>

<header>
    <?php
    $page = basename($_SERVER['PHP_SELF'], ".php");
    include_once '../../includes/navbar.php';
    ?>
</header>




<div class="container mt-5">
    <h2 class="mb-4">Verify Gas Request</h2>
    <form method="POST" class="mb-4">
        <div class="mb-3">
            <label for="token" class="form-label">Enter Token (e.g., ORD00018)</label>
            <input type="text" class="form-control" id="token" name="token" required>
        </div>
        <center><button type="submit" class="btn btn-primary">Verify</button></center>
    </form>

    <?php if (isset($row)) : ?>
        <?php if ($row) : ?>
            <div class="card">
                <div class="card-body">
                    <center><h5 class="card-title">Request Details</h5></center>
                    <p><strong>Token:</strong> <?= htmlspecialchars($token) ?></p>
                    <p><strong>Name:</strong> <?= htmlspecialchars($row['name']) ?></p>
                    <p><strong>NIC:</strong> <?= htmlspecialchars($row['nic_or_registration_number']) ?></p>
                    <p><strong>Gas Type:</strong> <?= htmlspecialchars($row['gas_type']) ?></p>
                    <p><strong>Quantity:</strong> <?= htmlspecialchars($row['qty']) ?></p>
                    <p><strong>Pickup Date:</strong> <?= htmlspecialchars($row['pickup_date']) ?></p>
                    <p><strong>Last Pickup Date:</strong> <?= htmlspecialchars($row['tolerance_end_date']) ?></p>
                    <p><strong>Status:</strong> <?= htmlspecialchars($row['status']) ?></p>
                </div>
            </div>
        <?php else : ?>
            <div class="alert alert-danger">No request found for the given token.</div>
        <?php endif; ?>
    <?php endif; ?>
</div>





</br>
</br>
</br>
<?php include_once '../../includes/footer.php'; ?>
<?php include_once '../../includes/end.php'; ?>