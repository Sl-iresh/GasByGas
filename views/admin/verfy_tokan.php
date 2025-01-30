<?php
$title = "Dashboard | Lpgas ";
include_once '../../includes/header.php';

$db = new Database();
$conn = $db->connect();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../../public/login.php");
    exit();
}

$success = $error = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = $_POST['token'];

    // Extract the numeric part of the token
    $request_id = intval(str_replace('ORD', '', $token));

    $query = "SELECT gr.id, gr.request_data, gr.pickup_date, gr.status, u.name, u.nic_or_registration_number 
              FROM business_gas_requests gr
              JOIN users u ON gr.user_id = u.user_id
              WHERE gr.id = :request_id";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(":request_id", $request_id, PDO::PARAM_INT);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
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
 


                                         <?php
                                            $requested_data = json_decode($row['request_data'], true);
                                            foreach ($requested_data as $gas_type => $quantity) { ?>

                                                <p><strong><?= htmlspecialchars($gas_type) ?>:</strong> <?= htmlspecialchars($quantity) ?> units</p>

                                            <?php } ?>


                    <p><strong>Pickup Date:</strong> <?= htmlspecialchars($row['pickup_date']) ?></p>
                
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