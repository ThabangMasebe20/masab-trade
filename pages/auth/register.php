<?php
include '../../backend/includes/session-manager.php';
include '../../backend/config/database.php';

$error = '';

if (isset($_SESSION['user_id'])) {
    header('Location: /index.php');
    exit();
}

$came_from = $_GET['from'] ?? 'home';
$role_hint = $_GET['role'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username    = trim($_POST['username']        ?? '');
    $email       = trim($_POST['email']           ?? '');
    $phone       = trim($_POST['phone']           ?? '');
    $password    = $_POST['password']             ?? '';
    $confirm     = $_POST['confirm_password']     ?? '';
    $user_role   = $_POST['user_role']            ?? '';
    $came_post   = $_POST['came_from']            ?? 'home';
    $biz_name    = trim($_POST['business_name']   ?? '');
    $biz_desc    = trim($_POST['business_description'] ?? '');

    if (!$username || !$email || !$password || !$user_role) {
        $error = 'All required fields must be filled in.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif (!in_array($user_role, ['buyer', 'seller'])) {
        $error = 'Please select a valid account type.';
    } else {
        $chk = $conn->prepare("SELECT user_id FROM users WHERE email = ? LIMIT 1");
        $chk->bind_param("s", $email);
        $chk->execute();
        $chk->store_result();

        if ($chk->num_rows > 0) {
            $error = 'Email already registered. Please login instead.';
            $chk->close();
        } else {
            $chk->close();
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $ins = $conn->prepare("INSERT INTO users (username, email, password, phone, user_role, is_active) VALUES (?, ?, ?, ?, ?, 1)");
            $ins->bind_param("sssss", $username, $email, $hash, $phone, $user_role);

            if ($ins->execute()) {
                $new_id = $conn->insert_id;
                $ins->close();

                // ✅ Create seller profile with business name + description
                if ($user_role === 'seller') {
                    $sp = $conn->prepare("INSERT INTO seller_profiles (user_id, business_name, business_description, location, rating, total_sales) VALUES (?, ?, ?, '', 0.00, 0)");
                    $sp->bind_param("iss", $new_id, $biz_name, $biz_desc);
                    $sp->execute();
                    $sp->close();
                }

                $_SESSION['user_id']       = $new_id;
                $_SESSION['username']      = $username;
                $_SESSION['user_role']     = $user_role;
                $_SESSION['email']         = $email;
                $_SESSION['last_activity'] = time();

                if ($came_post === 'browse' || ($user_role === 'buyer' && $came_post !== 'seller')) {
                    header('Location: /pages/browse.php');
                } elseif ($user_role === 'seller' || $came_post === 'seller') {
                    header('Location: /pages/seller/dashboard.php');
                } else {
                    header('Location: /index.php');
                }
                exit();
            } else {
                $error = 'Registration failed. Please try again.';
                $ins->close();
            }
        }
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Masab Trade</title>
    <link rel="stylesheet" href="../../css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .seller-fields {
            display: none;
            border: 2px solid #ecf0f1;
            border-radius: 10px;
            padding: 18px;
            margin-bottom: 18px;
            background: #f8f9ff;
        }
        .seller-fields.visible { display: block; }
        .seller-fields-title {
            font-size: 0.88rem;
            font-weight: 700;
            color: #0F6E56;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 7px;
        }
        .seller-fields .form-group { margin-bottom: 14px; }
        .seller-fields .form-group:last-child { margin-bottom: 0; }
        .seller-fields textarea {
            width: 100%;
            padding: 11px 14px;
            border: 2px solid #ecf0f1;
            border-radius: 10px;
            font-size: 0.95rem;
            font-family: inherit;
            resize: vertical;
            min-height: 80px;
            background: #fafbff;
            box-sizing: border-box;
        }
        .seller-fields textarea:focus {
            outline: none;
            border-color: #0F6E56;
        }
        .seller-fields input {
            width: 100%;
            padding: 11px 14px;
            border: 2px solid #ecf0f1;
            border-radius: 10px;
            font-size: 0.95rem;
            font-family: inherit;
            background: #fafbff;
            box-sizing: border-box;
        }
        .seller-fields input:focus {
            outline: none;
            border-color: #0F6E56;
        }
        .seller-fields label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #2c3e50;
            display: block;
            margin-bottom: 6px;
        }
    </style>
</head>
<body>
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-logo">
            <h1><a href="/index.php">Masab Trade</a></h1>
            <p>Create your free account</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="came_from" value="<?php echo htmlspecialchars($came_from); ?>">

            <div class="form-group">
                <label for="username"><i class="fas fa-user"></i> Username *</label>
                <input type="text" id="username" name="username"
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                       placeholder="e.g. john_doe" required>
            </div>

            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email Address *</label>
                <input type="email" id="email" name="email"
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                       placeholder="your@email.com" required>
            </div>

            <div class="form-group">
                <label for="phone"><i class="fas fa-phone"></i> Phone Number</label>
                <input type="tel" id="phone" name="phone"
                       value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                       placeholder="e.g. 0712345678">
            </div>

            <div class="form-group">
                <label for="user_role"><i class="fas fa-user-tag"></i> I want to: *</label>
                <select id="user_role" name="user_role" required onchange="toggleSellerFields()">
                    <option value="">-- Select Account Type --</option>
                    <option value="buyer"
                        <?php echo ($role_hint === 'buyer' || $came_from === 'browse') ? 'selected' : ''; ?>>
                        🛒 Buy Products
                    </option>
                    <option value="seller"
                        <?php echo ($role_hint === 'seller' || $came_from === 'seller') ? 'selected' : ''; ?>>
                        🏪 Sell Products
                    </option>
                </select>
            </div>

            <!-- ✅ Seller-only fields -->
            <div class="seller-fields <?php echo (($role_hint === 'seller' || $came_from === 'seller') ? 'visible' : ''); ?>" id="sellerFields">
                <p class="seller-fields-title">
                    <i class="fas fa-store" style="color:#0F6E56"></i>
                    Seller Business Information
                </p>
                <div class="form-group">
                    <label for="business_name">Business / Shop Name</label>
                    <input type="text" id="business_name" name="business_name"
                           placeholder="e.g. Jerry's Electronics"
                           value="<?php echo htmlspecialchars($_POST['business_name'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="business_description">Business Description</label>
                    <textarea id="business_description" name="business_description"
                              placeholder="Tell buyers about your business, what you sell, and your location..."><?php echo htmlspecialchars($_POST['business_description'] ?? ''); ?></textarea>
                </div>
            </div>

            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Password *</label>
                <div class="password-field">
                    <input type="password" id="password" name="password"
                           placeholder="Minimum 6 characters" required>
                    <button type="button" onclick="togglePwd('password','icon1')">
                        <i class="fas fa-eye" id="icon1"></i>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password"><i class="fas fa-lock"></i> Confirm Password *</label>
                <div class="password-field">
                    <input type="password" id="confirm_password" name="confirm_password"
                           placeholder="Repeat your password" required>
                    <button type="button" onclick="togglePwd('confirm_password','icon2')">
                        <i class="fas fa-eye" id="icon2"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="submit-btn">
                <i class="fas fa-user-plus"></i> Create Account
            </button>
        </form>

        <div class="auth-footer">
            <p>Already have an account? <a href="/pages/auth/login.php">Login here</a></p>
            <p><a href="/index.php"><i class="fas fa-arrow-left"></i> Back to Home</a></p>
        </div>
    </div>
</div>

<script>
function togglePwd(fieldId, iconId) {
    var f = document.getElementById(fieldId);
    var i = document.getElementById(iconId);
    if (!f || !i) return;
    f.type      = (f.type === 'password') ? 'text' : 'password';
    i.className = (f.type === 'password') ? 'fas fa-eye' : 'fas fa-eye-slash';
}

function toggleSellerFields() {
    var role   = document.getElementById('user_role').value;
    var fields = document.getElementById('sellerFields');
    if (role === 'seller') {
        fields.classList.add('visible');
    } else {
        fields.classList.remove('visible');
    }
}

// Run on load in case role is pre-selected
toggleSellerFields();
</script>
</body>
</html>