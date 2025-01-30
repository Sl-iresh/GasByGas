<?php
$title = "Forgot Password | GasByGas";
include_once '../includes/header.php';

$db = new Database();
$conn = $db->connect();

$error = $success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? htmlspecialchars(trim($_POST['email'])) : '';

    if (empty($email)) {
        $error = "Please enter your email address.";
    } else {
        // Fetch user by email
        $query = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Generate OTP
            $otp = rand(100000, 999999); // 6-digit OTP
            $_SESSION['otp'] = $otp;
            $_SESSION['otp_email'] = $email;

            // Send OTP via SMS
            require_once '../controllers/SMSApi.php';
            $smsApi = new SMSApi();
            $message = "Your OTP for password reset is: $otp";
            $smsResponse = $smsApi->sendSMS([$user['contact_number']], $message);

            if ($smsResponse) {
                $success = "OTP has been sent to your registered phone number.";
                header('Location: verify_otp.php');
                exit;
            } else {
                $error = "Failed to send OTP. Please try again.";
            }
        } else {
            $error = "No user found with this email address.";
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
                                <form method="POST" action="forgot_password.php">
                                    <div class="d-flex align-items-center mb-3 pb-1">
                                        <i class="fas fa-cubes fa-2x me-3" style="color: #ff6219;"></i>
                                        <span class="h1 fw-bold mb-0">GasByGas</span>
                                    </div>

                                    <h5 class="fw-normal mb-3 pb-3" style="letter-spacing: 1px;">Forgot Password</h5>

                                    <?php if (!empty($error)): ?>
                                        <div class="alert alert-danger"><?= $error ?></div>
                                    <?php endif; ?>

                                    <?php if (!empty($success)): ?>
                                        <div class="alert alert-success"><?= $success ?></div>
                                    <?php endif; ?>

                                    <div data-mdb-input-init class="form-outline mb-4">
                                        <input type="email" id="email" name="email" class="form-control form-control-lg" required />
                                        <label class="form-label" for="email">Email address</label>
                                    </div>

                                    <div class="pt-1 mb-2">
                                        <button type="submit" data-mdb-button-init data-mdb-ripple-init class="btn btn-dark btn-lg btn-block">Send OTP</button>
                                    </div>

                                    <p class="mb-5 pb-lg-2" style="color: #393f81;">
                                        Remember your password? <a href="login.php" style="color: #393f81;">Login here</a>
                                    </p>
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