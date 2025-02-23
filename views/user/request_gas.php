<?php
$title = "Request | GasbyGas ";
$page = "Request_gas";

include_once '../../includes/header.php';
$db = new Database();
$conn = $db->connect();

// Check if user is logged in and is an individual
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'individual') {
    header("Location: index.php");
    exit();
}

$user = $_SESSION['user'];

// Fetch user's purchase limit
$role = $user['role'];
$limit_query = "SELECT order_limit FROM order_limits WHERE user_type = ?";
$stmt = $conn->prepare($limit_query);
$stmt->execute([$role]);
$purchase_limit = $stmt->fetchColumn();

// Count pending/scheduled orders for the user
$pending_orders_query = "SELECT COUNT(*) FROM gas_requests 
                         WHERE user_id = ? AND status IN ('pending', 'scheduled')";
$stmt = $conn->prepare($pending_orders_query);
$stmt->execute([$user['user_id']]);
$pending_orders_count = $stmt->fetchColumn();

// Calculate remaining purchase limit
$remaining_limit = $purchase_limit - $pending_orders_count;

// Fetch the latest request date for each outlet
$outlet_request_dates = [];
$query = $conn->query("SELECT outlet_id, MAX(request_date) as latest_request FROM outlet_gas_requests GROUP BY outlet_id");
while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
    $outlet_request_dates[$row['outlet_id']] = $row['latest_request'];
}

// Fetch all outlets
$outlets = $conn->query("SELECT * FROM outlets");
?>

<script>
    let outletRequestDates = <?= json_encode($outlet_request_dates) ?>;
    let remainingLimit = <?= $remaining_limit ?>;
</script>

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
        0%, 100% {
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

                <!-- Display warning if limit is reached -->
                <?php if ($remaining_limit <= 0): ?>
                    <div class="alert alert-warning">
                        You have reached your maximum allowed orders (Limit: <?= $purchase_limit ?>). 
                        Please complete or cancel existing orders to place new ones.
                    </div>
                <?php endif; ?>

                <form action="../../controllers/process_request.php" method="POST">
                    <div class="mb-3">
                        <label for="outlet_id" class="form-label">Select Outlet</label>
                        <select name="outlet_id" id="outlet_id" class="form-select" required onchange="updatePickupDate()">
                            <?php while ($outlet = $outlets->fetch(PDO::FETCH_ASSOC)) { ?>
                                <option value="<?= $outlet['id'] ?>"><?= $outlet['name'] ?> (<?= $outlet['district'] ?>)</option>
                            <?php } ?>
                        </select>
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
                        <label for="qty" class="form-label">Quantity</label>
                        <input type="number" name="qty" id="qty" class="form-control" 
                               min="1" max="<?= $remaining_limit ?>" 
                               required <?= $remaining_limit <= 0 ? 'disabled' : '' ?>>
                    </div>
                    <div class="mb-3">
                        <label for="pickup_date" class="form-label">Pickup Date</label>
                        <input type="date" name="pickup_date" id="pickup_date" class="form-control" required>
                    </div>
                    <button type="submit" class="btn order-btn w-100" <?= $remaining_limit <= 0 ? 'disabled' : '' ?>>
                        Submit Request
                    </button>
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
    function updatePickupDate() {
        let outletId = document.getElementById("outlet_id").value;
        let pickupDate = document.getElementById("pickup_date");

        if (outletRequestDates[outletId]) {
            let latestDate = new Date(outletRequestDates[outletId]); // Last request date
            latestDate.setDate(latestDate.getDate() + 1); // Add 1 day

            let minDate = latestDate.toISOString().split("T")[0]; // Format YYYY-MM-DD
            pickupDate.min = minDate;
        } else {
            let today = new Date();
            let minDate = today.toISOString().split("T")[0]; // Default to today if no previous requests
            pickupDate.min = minDate;
        }
    }

    // Set default min date on page load
    document.addEventListener("DOMContentLoaded", function () {
        updatePickupDate();
    });
</script>

<?php include_once '../../includes/end.php'; ?>