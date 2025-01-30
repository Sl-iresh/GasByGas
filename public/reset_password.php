<?php
$title = "Reset Password | GasByGas";
include_once '../includes/header.php';

$db = new Database();
$conn = $db->connect();

$error = $success = "";

if (!isset($_SESSION['otp']) || !isset($_SESSION['otp_email'])) {
    header('Location: forgot_password.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';

    if (empty($password) || empty($confirm_password)) {
        $error = "Please fill in both password fields.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Hash the new password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Update the user's password
        $query = "UPDATE users SET password = ? WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$hashed_password, $_SESSION['otp_email']]);

        // Clear session variables
        unset($_SESSION['otp']);
        unset($_SESSION['otp_email']);

        $success = "Password reset successfully. You can now <a href='login.php'>login</a>.";
    }
}
?>

<style>
    body {
        background-color: #9A616D !important;
        height: 100vh !important;
    }

    .card {
        border-radius: 1rem;
    }

    .form-control {
        border-radius: 0.5rem;
    }
</style>

<section>
    <div class="container py-5 h-100">
        <div class="row d-flex justify-content-center align-items-center h-100">
            <div class="col col-xl-10">
                <div class="card" style="border-radius: 1rem;">
                    <div class="row g-0">
                        <div class="col-md-6 col-lg-5 d-none d-md-block">
                            <img src="../assets/images/img1.webp" alt="login form" class="img-fluid" style="border-radius: 1rem 0 0 1rem;" />
                        </div>
                        <div class="col-md-6 col-lg-7 d-flex align-items-center">
                            <div class="card-body p-4 p-lg-5 text-black">
                                <form method="POST" action="reset_password.php">
                                    <div class="d-flex align-items-center mb-3 pb-1">
                                        <i class="fas fa-cubes fa-2x me-3" style="color: #ff6219;"></i>
                                        <span class="h1 fw-bold mb-0">GasByGas</span>
                                    </div>

                                    <h5 class="fw-normal mb-3 pb-3" style="letter-spacing: 1px;">Reset Password</h5>

                                    <?php if (!empty($error)): ?>
                                        <div class="alert alert-danger"><?= $error ?></div>
                                    <?php endif; ?>

                                    <?php if (!empty($success)): ?>
                                        <div class="alert alert-success"><?= $success ?></div>
                                    <?php endif; ?>

                                    <div data-mdb-input-init class="form-outline mb-4">
                                        <input type="password" id="password" name="password" class="form-control form-control-lg" required />
                                        <label class="form-label" for="password">New Password</label>
                                    </div>

                                    <div data-mdb-input-init class="form-outline mb-4">
                                        <input type="password" id="confirm_password" name="confirm_password" class="form-control form-control-lg" required />
                                        <label class="form-label" for="confirm_password">Confirm Password</label>
                                    </div>

                                    <div class="pt-1 mb-2">
                                        <button type="submit" data-mdb-button-init data-mdb-ripple-init class="btn btn-dark btn-lg btn-block">Reset Password</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include_once '../includes/footer.php'; ?>
<?php include_once '../includes/end.php'; ?>