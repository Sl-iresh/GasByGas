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

// Fetch business user's purchase limit
$limit_query = "SELECT order_limit FROM order_limits WHERE user_type = 'business'";
$stmt = $conn->query($limit_query);
$purchase_limit = $stmt->fetchColumn();

// Count pending/scheduled orders for the business user
$pending_orders_query = "SELECT SUM(CAST(JSON_UNQUOTE(JSON_EXTRACT(request_data, '$.LPG')) AS UNSIGNED)) + 
                                SUM(CAST(JSON_UNQUOTE(JSON_EXTRACT(request_data, '$.Propane')) AS UNSIGNED)) + 
                                SUM(CAST(JSON_UNQUOTE(JSON_EXTRACT(request_data, '$.Industrial')) AS UNSIGNED)) AS total_qty 
                         FROM business_gas_requests 
                         WHERE user_id = ? AND status IN ('pending', 'scheduled')";
$stmt = $conn->prepare($pending_orders_query);
$stmt->execute([$user_id]);
$pending_orders = $stmt->fetch(PDO::FETCH_ASSOC);
$pending_qty = (int)($pending_orders['total_qty'] ?? 0);

// Calculate remaining purchase limit
$remaining_limit = $purchase_limit - $pending_qty;

// Handling form submission securely
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $gas_data = [
        'LPG' => isset($_POST['LPG']) ? (int)$_POST['LPG'] : 0,
        'Propane' => isset($_POST['Propane']) ? (int)$_POST['Propane'] : 0,
        'Industrial' => isset($_POST['Industrial']) ? (int)$_POST['Industrial'] : 0
    ];
    $pickup_date = $_POST['pickup_date'];
    $total_qty = $gas_data['LPG'] + $gas_data['Propane'] + $gas_data['Industrial'];

    // Validate total quantity against remaining limit
    if ($total_qty <= $remaining_limit) {
        $request_data = json_encode($gas_data);

        // Insert request
        $query = "INSERT INTO business_gas_requests (request_data, pickup_date, user_id) 
                  VALUES (:request_data, :pickup_date, :user_id)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':request_data', $request_data, PDO::PARAM_STR);
        $stmt->bindParam(':pickup_date', $pickup_date, PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // Fetch user's phone number
            $stmt = $conn->prepare("SELECT contact_number FROM users WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
            $phoneNumber = $user_data['contact_number'];

            // Prepare SMS message
            $message = "Your gas order for $total_qty units (LPG: {$gas_data['LPG']}, Propane: {$gas_data['Propane']}, Industrial: {$gas_data['Industrial']}) has been submitted and is pending approval.";

            // Send SMS
            require_once '../../controllers/SMSApi.php';
            $smsApi = new SMSApi();
            $smsResponse = $smsApi->sendSMS([$phoneNumber], $message);

            // Log SMS response (optional)
            error_log("SMS Response: " . $smsResponse);

            $success = "Gas request submitted successfully!";
        } else {
            $error = "Failed to submit request.";
        }
    } else {
        $error = "Total quantity exceeds your remaining purchase limit.";
    }
}
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
            <input type="number" name="LPG" id="LPG" class="form-control" required min="0" max="<?= $remaining_limit ?>">
        </div>
        <div class="mb-3">
            <label for="Propane" class="form-label">Propane Quantity</label>
            <input type="number" name="Propane" id="Propane" class="form-control" required min="0" max="<?= $remaining_limit ?>">
        </div>
        <div class="mb-3">
            <label for="Industrial" class="form-label">Industrial Quantity</label>
            <input type="number" name="Industrial" id="Industrial" class="form-control" required min="0" max="<?= $remaining_limit ?>">
        </div>

        <div class="mb-3">
            <label for="pickup_date" class="form-label">Pickup Date</label>
            <input type="date" name="pickup_date" id="pickup_date" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary w-100" id="submit-btn" <?= $remaining_limit <= 0 ? 'disabled' : '' ?>>
            Submit Request
        </button>
    </form>
</div>

<br>
<br>

<?php include_once '../../includes/footer.php'; ?>
<?php include_once '../../includes/end.php'; ?>

<script>
    const lpgInput = document.getElementById('LPG');
    const propaneInput = document.getElementById('Propane');
    const industrialInput = document.getElementById('Industrial');
    const submitBtn = document.getElementById('submit-btn');
    const remainingLimit = <?= $remaining_limit ?>;

    function validateTotalQuantity() {
        const lpgQty = parseInt(lpgInput.value) || 0;
        const propaneQty = parseInt(propaneInput.value) || 0;
        const industrialQty = parseInt(industrialInput.value) || 0;
        const totalQty = lpgQty + propaneQty + industrialQty;

        if (totalQty > remainingLimit) {
            document.getElementById('stock-warning').textContent = `Total quantity exceeds your remaining limit of ${remainingLimit}.`;
            submitBtn.disabled = true;
        } else {
            document.getElementById('stock-warning').textContent = '';
            submitBtn.disabled = false;
        }
    }

    lpgInput.addEventListener('input', validateTotalQuantity);
    propaneInput.addEventListener('input', validateTotalQuantity);
    industrialInput.addEventListener('input', validateTotalQuantity);

    // Initial validation on page load
    validateTotalQuantity();
</script>