<?php
include '../../backend/includes/session-manager.php';
include '../../backend/config/database.php';

$error = '';

if (isset($_SESSION['user_id'])) {
    header('Location: /index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    
    $redirect = trim($_POST['redirect'] ?? '');
    $pid      = intval($_POST['pid']    ?? 0);

    if (!$email || !$password) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1 LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user_id']       = $user['user_id'];
            $_SESSION['username']      = $user['username'];
            $_SESSION['user_role']     = $user['user_role'];
            $_SESSION['email']         = $user['email'];
            $_SESSION['last_activity'] = time();

            // Redirect to checkout if came from Buy Now
            if ($redirect === 'checkout' && $pid > 0) {
                header("Location: /pages/buyer/checkout.php?product_id=$pid");
                exit();
            }

            // Default redirect by role
            if ($user['user_role'] === 'admin') {
                header('Location: /pages/admin/dashboard.php');
                exit();
            }
            if ($user['user_role'] === 'seller') {
                header('Location: /pages/seller/dashboard.php');
                exit();
            }
            header('Location: /pages/buyer/dashboard.php');
            exit();

        } else {
            $error = 'Invalid email or password. Please try again.';
        }
    }
}

// Get redirect params from URL (GET) to pass into hidden form fields
$get_redirect = $_GET['redirect'] ?? '';
$get_pid      = intval($_GET['pid'] ?? 0);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Masab Trade</title>
    <link rel="stylesheet" href="../../css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-logo">
            <h1><a href="/index.php">Masab Trade</a></h1>
            <p>Sign in to your account</p>
        </div>

        <?php if (isset($_GET['timeout'])): ?>
            <div class="alert alert-info">
                <i class="fas fa-clock"></i> Session expired due to inactivity. Please login again.
            </div>
        <?php endif; ?>

        <?php if ($get_redirect === 'checkout' && $get_pid > 0): ?>
            <div class="alert alert-info">
                <i class="fas fa-shopping-cart"></i>
                Please login to complete your purchase.
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">            
            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($get_redirect); ?>">
            <input type="hidden" name="pid"      value="<?php echo $get_pid; ?>">

            <div class="form-group">
                <label for="email">
                    <i class="fas fa-envelope"></i> Email Address
                </label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                    placeholder="your@email.com"
                    required
                    autofocus>
            </div>

            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i> Password
                </label>
                <div class="password-field">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Your password"
                        required>
                    <button type="button" onclick="togglePwd('password','eyeIcon1')">
                        <i class="fas fa-eye" id="eyeIcon1"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="submit-btn">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
        </form>

        <div class="auth-footer">
            <p>No account? <a href="/pages/auth/register.php">Register here</a></p>
            <p><a href="/index.php"><i class="fas fa-arrow-left"></i> Back to Home</a></p>
        </div>
    </div>
</div>

<script>
function togglePwd(fieldId, iconId) {
    const f = document.getElementById(fieldId);
    const i = document.getElementById(iconId);
    if (!f || !i) return;
    f.type      = (f.type === 'password') ? 'text' : 'password';
    i.className = (f.type === 'password') ? 'fas fa-eye' : 'fas fa-eye-slash';
}
</script>
</body>
</html>
