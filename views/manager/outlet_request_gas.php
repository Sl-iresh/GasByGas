<?php
$title = "Dashboard | Lpgas ";
include_once '../../includes/header.php';

$db = new Database();
$conn = $db->connect();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'manager') {
    header("Location: ../../public/login.php");
    exit();
}

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

$success = $error = "";
// Handling form submission securely
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $outlet_id = $_POST['outlet_id'];
    $request_date = $_POST['request_date'];

    
    $gas_data = [
        'LPG' => isset($_POST['LPG']) ? (int) $_POST['LPG'] : 0,
        'Propane' => isset($_POST['Propane']) ? (int) $_POST['Propane'] : 0,
        'Industrial' => isset($_POST['Industrial']) ? (int) $_POST['Industrial'] : 0
    ];
    $request_data = json_encode($gas_data);

    // Prepared statement
    $query = "INSERT INTO outlet_gas_requests (outlet_id, request_data,request_date) VALUES (:outlet_id, :request_data, :request_date)";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':outlet_id', $outlet_id, PDO::PARAM_INT);
    $stmt->bindParam(':request_data', $request_data, PDO::PARAM_STR);
    $stmt->bindParam(':request_date', $request_date, PDO::PARAM_STR);

    if ($stmt->execute()) {
        $success = "Gas request submitted successfully!";
    } else {
        $error = "Failed to submit request.";
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
        <input type="hidden" name="outlet_id" value="<?= htmlspecialchars($outlet_id); ?>">

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
            <label for="pickup_date" class="form-label">Request Date</label>
            <input type="date" name="request_date" id="request_date" class="form-control" required>
        </div>


        <button type="submit" class="btn btn-primary w-100">Submit Request</button>
    </form>
</div>
<br>
<br>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let outletId = <?= json_encode($outlet_id) ?>;
        fetch(`../../controllers/fetch_stock.php?outlet_id=${outletId}`)
            .then(response => response.json())
            .then(data => {
                let stockWarning = document.getElementById('stock-warning');
                stockWarning.innerHTML = "";
                let allDisabled = true;

                ['LPG', 'Propane', 'Industrial'].forEach(type => {
                    let inputField = document.getElementById(type);
                    if (data[type] >= 10) {
                        inputField.disabled = true;
                        inputField.classList.add('disabled');
                        stockWarning.innerHTML += `<p>${type} stock is sufficient (${data[type]} available).</p>`;
                    } else {
                        inputField.disabled = false;
                        inputField.classList.remove('disabled');
                        allDisabled = false;
                    }
                });

                document.querySelector('button[type="submit"]').disabled = allDisabled;
            });
    });
</script>

<?php include_once '../../includes/footer.php'; ?>
<?php include_once '../../includes/end.php'; ?>