<?php
include 'session_timeout.php';
include 'config.php';

$admin_id = (int)($_SESSION['admin_id'] ?? 0);
if ($admin_id < 1 || empty($_SESSION['admin'])) {
    header('Location: admin_login.php');
    exit();
}
$adminCheck = mysqli_prepare($conn, "SELECT password, fullname FROM users WHERE id = ? AND is_admin = 1 LIMIT 1");
if (!$adminCheck) { http_response_code(500); exit('Unable to verify admin account.'); }
mysqli_stmt_bind_param($adminCheck, 'i', $admin_id);
mysqli_stmt_execute($adminCheck);
$adminResult = mysqli_stmt_get_result($adminCheck);
$adminUser = $adminResult ? mysqli_fetch_assoc($adminResult) : null;
mysqli_stmt_close($adminCheck);
if (!$adminUser) {
    unset($_SESSION['admin'], $_SESSION['admin_id'], $_SESSION['admin_email']);
    header('Location: admin_login.php');
    exit();
}

if (empty($_SESSION['admin_password_csrf'])) {
    $_SESSION['admin_password_csrf'] = bin2hex(random_bytes(32));
}
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string)($_POST['csrf_token'] ?? '');
    if (!hash_equals($_SESSION['admin_password_csrf'], $token)) {
        http_response_code(403);
        $error = 'Security check failed. Refresh the page and try again.';
    } else {
        $old = (string)($_POST['old_password'] ?? '');
        $new = (string)($_POST['new_password'] ?? '');
        $confirm = (string)($_POST['confirm_password'] ?? '');
        $oldValid = password_verify($old, (string)$adminUser['password'])
            || hash_equals((string)$adminUser['password'], $old);

        if (!$oldValid) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($new) < 10 || strlen($new) > 200) {
            $error = 'New password must be at least 10 characters.';
        } elseif ($new !== $confirm) {
            $error = 'New password and confirmation do not match.';
        } elseif (hash_equals($old, $new)) {
            $error = 'Choose a password different from your current password.';
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $update = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ? AND is_admin = 1");
            if (!$update) {
                $error = 'Unable to update password right now.';
            } else {
                mysqli_stmt_bind_param($update, 'si', $hash, $admin_id);
                $updated = mysqli_stmt_execute($update);
                mysqli_stmt_close($update);
                if ($updated) {
                    session_regenerate_id(true);
                    $_SESSION['admin_password_csrf'] = bin2hex(random_bytes(32));
                    $success = 'Admin password changed successfully.';
                } else {
                    $error = 'Password could not be updated. Please try again.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Admin Password</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .password-card{max-width:460px;margin:50px auto;padding:28px;background:#fff;border-radius:16px;box-shadow:0 8px 28px rgba(0,0,0,.08)}
        .password-card h2{margin-bottom:18px;color:#294b37}
        .password-card input{display:block;width:100%;padding:12px;margin:10px 0;border:1px solid #dce7dc;border-radius:9px}
        .password-card button{padding:12px 18px;border:0;border-radius:9px;background:#315b45;color:#fff;font-weight:600;cursor:pointer}
        .notice{padding:10px 12px;margin:12px 0;border-radius:8px;background:#edf7ed;color:#245b32}
        .error{background:#fff0f0;color:#9b2929}
    </style>
</head>
<body>
<section class="password-card">
    <h2>Change Admin Password</h2>
    <?php if ($error !== ''): ?><div class="notice error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
    <?php if ($success !== ''): ?><div class="notice"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
    <form method="POST" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['admin_password_csrf'], ENT_QUOTES, 'UTF-8'); ?>">
        <input type="password" name="old_password" placeholder="Current password" autocomplete="current-password" required>
        <input type="password" name="new_password" placeholder="New password (minimum 10 characters)" minlength="10" maxlength="200" autocomplete="new-password" required>
        <input type="password" name="confirm_password" placeholder="Confirm new password" minlength="10" maxlength="200" autocomplete="new-password" required>
        <button type="submit">Change Password</button>
    </form>
    <p style="margin-top:16px"><a href="admin_dashboard.php">Back to Dashboard</a></p>
</section>
</body>
</html>
