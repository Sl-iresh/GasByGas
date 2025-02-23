<?php
$title = "Verify OTP | GasByGas";
include_once '../includes/header.php';

$db = new Database();
$conn = $db->connect();

$error = $success = "";

if (!isset($_SESSION['otp']) || !isset($_SESSION['otp_email'])) {
    header('Location: forgot_password.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = isset($_POST['otp']) ? htmlspecialchars(trim($_POST['otp'])) : '';

    if (empty($otp)) {
        $error = "Please enter the OTP.";
    } else {
        if ($otp == $_SESSION['otp']) {
            header('Location: reset_password.php');
            exit;
        } else {
            $error = "Invalid OTP. Please try again.";
        }
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
                                <form method="POST" action="verify_otp.php">
                                    <div class="d-flex align-items-center mb-3 pb-1">
                                        <i class="fas fa-cubes fa-2x me-3" style="color: #ff6219;"></i>
                                        <span class="h1 fw-bold mb-0">GasByGas</span>
                                    </div>

                                    <h5 class="fw-normal mb-3 pb-3" style="letter-spacing: 1px;">Verify OTP</h5>

                                    <?php if (!empty($error)): ?>
                                        <div class="alert alert-danger"><?= $error ?></div>
                                    <?php endif; ?>

                                    <div data-mdb-input-init class="form-outline mb-4">
                                        <input type="text" id="otp" name="otp" class="form-control form-control-lg" required />
                                        <label class="form-label" for="otp">Enter OTP</label>
                                    </div>

                                    <div class="pt-1 mb-2">
                                        <button type="submit" data-mdb-button-init data-mdb-ripple-init class="btn btn-dark btn-lg btn-block">Verify OTP</button>
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