<?php
$title = "Dashboard | Lpgas ";
include_once '../../includes/header.php';

$db = new Database();
$conn = $db->connect();

// Fetch current managers
$users = $conn->query("SELECT * FROM users WHERE role != 'admin'");




if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['id'])) {
        // Delete user
        $id = $_POST['id'];
        $conn->query("DELETE FROM users WHERE user_id = '$id'");
        $_SESSION['success_message'] = "User deleted successfully.";
    } elseif (isset($_POST['name'])) {
        // Add new user
        $name = htmlspecialchars(trim($_POST['name']));
        $email = htmlspecialchars(trim($_POST['email']));
        $password = password_hash(trim($_POST['password']), PASSWORD_BCRYPT);
        $address = htmlspecialchars(trim($_POST['address']));
        $contact = htmlspecialchars(trim($_POST['contact_number']));
        $nic_reg = htmlspecialchars(trim($_POST['nic_or_registration_number']));
        $district = htmlspecialchars(trim($_POST['district']));

        $stmt = $conn->prepare("INSERT INTO users 
            (name, password, address, email, district, 
            nic_or_registration_number, role, contact_number)
            VALUES (?, ?, ?, ?, ?, ?, 'manager', ?)");
        
        if ($stmt->execute([$name, $password, $address, $email, 
                          $district, $nic_reg, $contact])) {
            $_SESSION['success_message'] = "Manager added successfully!";
        } else {
            $_SESSION['error_message'] = "Error adding manager";
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;

}
?>

<style>
    /* Custom CSS for better UI and card styles */
    body {
        background-color: rgb(225, 226, 228);
        font-family: 'Roboto', sans-serif;
    }

    .btn-primary {
        background-color: #007bff;
        border: none;
    }

    .btn-primary:hover {
        background-color: #0056b3;
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

    .modal-dialog {
        max-width: 600px;
    }

    /* Style for request data table */
    .request-data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .request-data-table th,
    .request-data-table td {
        padding: 10px;
        text-align: left;
        border: 1px solid #ddd;
    }

    .request-data-table th {
        background-color: #f2f2f2;
    }
</style>

<header>
    <?php
    $page = basename($_SERVER['PHP_SELF'], ".php");
    include_once '../../includes/navbar.php';
    ?>
</header>

<main>
    <div class="container mt-4">


        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
                <?= $_SESSION['success_message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success_message']); // Clear message after displaying 
            ?>
        <?php endif; ?>



        <section id="orders" class="mt-5" style="background-color:#fff; padding: 10px; border-radius:12px;">
            <h2>Uers</h2>



            <center>
            <button type="button" class="btn add-manager-btn btn-primary " data-bs-toggle="modal" data-bs-target="#addManagerModal">
    Add New Manager
</button>
            </center>
            <!-- Add this button below the success message -->


<br><br>

<!-- Add this modal at the bottom of the main section -->
<div class="modal fade" id="addManagerModal" tabindex="-1" aria-labelledby="addManagerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addManagerModalLabel">Add New Manager</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <input type="text" class="form-control" name="address" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contact Number</label>
                        <input type="tel" class="form-control" name="contact_number" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">NIC/Registration Number</label>
                        <input type="text" class="form-control" name="nic_or_registration_number" required>
                    </div>
                
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Manager</button>
                </div>
            </form>
        </div>
    </div>
</div>


            <!-- DataTable for Pending Requests -->
            <table id="pendingRequestsTable" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email Data</th>
                        <th>Contact Number </th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>

                    <?php while ($user = $users->fetch(PDO::FETCH_ASSOC)) { ?>
                        <tr>
                            <td><?= $user['user_id'] ?></td>
                            <td><?= $user['name'] ?></td>
                            <td><?= $user['email'] ?></td>
                            <td><?= $user['contact_number'] ?></td>
                            <td><?= ucfirst($user['role']) ?></td>
                            <td>
                                <form id="deleteForm" method="POST">
                                    <input type="hidden" name="id" id="deleteUserId">
                                </form>

                                <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete(<?= $user['user_id'] ?>)">Delete</button>
                            </td>
                            </td>
                        </tr>
                    <?php } ?>

                </tbody>
            </table>


        </section>



    </div>
</main>

<br><br><br>

<?php include_once '../../includes/footer.php'; ?>

<script>
    function confirmDelete(userId) {
        if (confirm("Are you sure you want to delete this user?")) {
            // Set user ID in the hidden input field
            document.getElementById("deleteUserId").value = userId;
            document.getElementById("deleteForm").submit();
        }
    }

    $(document).ready(function() {
        $('#pendingRequestsTable').DataTable();
    });
</script>


<?php include_once '../../includes/end.php'; ?>