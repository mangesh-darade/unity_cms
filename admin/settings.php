<?php
include '../includes/db.php';
include '../includes/auth.php';
requireAdmin();

$msg = '';

// Handle Password Reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_pass = trim($_POST['current_pass']);
    $new_pass = trim($_POST['new_pass']);
    $confirm_pass = trim($_POST['confirm_pass']);
    
    if (!empty($current_pass) && !empty($new_pass) && !empty($confirm_pass)) {
        if ($new_pass === $confirm_pass) {
            try {
                // Fetch current password hash
                $username = $_SESSION['admin_username'];
                $stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
                $stmt->execute([':username' => $username]);
                $user = $stmt->fetch();
                
                if ($user && password_verify($current_pass, $user['password'])) {
                    // Update Password
                    $new_hash = password_hash($new_pass, PASSWORD_BCRYPT);
                    $stmt = $db->prepare("UPDATE users SET password = :password WHERE username = :username");
                    $stmt->execute([
                        ':password' => $new_hash,
                        ':username' => $username
                    ]);
                    $msg = '<div class="alert alert-success">Password updated successfully!</div>';
                } else {
                    $msg = '<div class="alert alert-error">Current password is incorrect.</div>';
                }
            } catch (PDOException $e) {
                $msg = '<div class="alert alert-error">Database error: ' . $e->getMessage() . '</div>';
            }
        } else {
            $msg = '<div class="alert alert-error">New passwords do not match.</div>';
        }
    } else {
        $msg = '<div class="alert alert-error">Please fill in all password fields.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings - Unity Lab Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-body">

    <!-- Admin Sidebar -->
    <div class="admin-sidebar">
        <div class="admin-sidebar-header">
            <div class="logo-icon" style="width: 32px; height: 32px; font-size: 1.1rem;"><i class="fa-solid fa-flask"></i></div>
            <span style="font-family: 'Outfit', sans-serif; font-size: 1.25rem; font-weight: 700; color: #ffffff;">Unity Lab Admin</span>
        </div>
        
        <ul class="admin-menu">
            <li class="admin-menu-item"><a href="index.php"><i class="fa-solid fa-chart-line"></i> <span>Dashboard</span></a></li>
            <li class="admin-menu-item"><a href="bookings.php"><i class="fa-solid fa-calendar-check"></i> <span>Bookings</span></a></li>
            <li class="admin-menu-item"><a href="patients.php"><i class="fa-solid fa-users"></i> <span>Patients</span></a></li>
            <li class="admin-menu-item"><a href="reports.php"><i class="fa-solid fa-file-pdf"></i> <span>Upload Reports</span></a></li>
            <li class="admin-menu-item"><a href="inquiries.php"><i class="fa-solid fa-envelope-open-text"></i> <span>Inquiries</span></a></li>
            <li class="admin-menu-item"><a href="cms.php"><i class="fa-solid fa-file-pen"></i> <span>CMS Settings</span></a></li>
            <li class="admin-menu-item active"><a href="settings.php"><i class="fa-solid fa-sliders"></i> <span>Settings</span></a></li>
        </ul>
        
        <div class="admin-sidebar-footer">
            Logged in as:<br>
            <strong style="color: #ffffff;"><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong>
        </div>
    </div>

    <!-- Main Content -->
    <div class="admin-main">
        <div class="admin-header">
            <div class="admin-title">
                <h1>Admin Portal Settings</h1>
                <p>Configure security profiles and update laboratory settings.</p>
            </div>
            <div class="admin-user-info">
                <i class="fa-solid fa-circle-user" style="font-size: 1.5rem; color: #0d9488;"></i>
                <span>Admin</span>
                <a href="index.php?logout=1" class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.85rem; border-radius: 4px;"><i class="fa-solid fa-power-off"></i> Logout</a>
            </div>
        </div>

        <?php echo $msg; ?>

        <div class="grid-2" style="grid-template-columns: 1fr 1fr; align-items: start;">
            <!-- Change Password Card -->
            <div class="admin-panel-card">
                <div class="admin-card-header">
                    <h2>Change Admin Password</h2>
                </div>
                
                <form action="settings.php" method="POST">
                    <div class="form-group">
                        <label for="current_pass" class="form-label">Current Password</label>
                        <input type="password" id="current_pass" name="current_pass" class="form-control" placeholder="Enter current password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_pass" class="form-label">New Password</label>
                        <input type="password" id="new_pass" name="new_pass" class="form-control" placeholder="Minimum 6 characters" required>
                    </div>

                    <div class="form-group">
                        <label for="confirm_pass" class="form-label">Confirm New Password</label>
                        <input type="password" id="confirm_pass" name="confirm_pass" class="form-control" placeholder="Repeat new password" required>
                    </div>

                    <button type="submit" name="change_password" class="btn btn-teal w-full" style="margin-top: 10px;">
                        <i class="fa-solid fa-key"></i> Update Password
                    </button>
                </form>
            </div>
            
            <!-- Information / Instructions Card -->
            <div class="admin-panel-card">
                <div class="admin-card-header">
                    <h2>Security Policy Guidelines</h2>
                </div>
                <div style="font-size: 0.95rem; line-height: 1.7; color: var(--text-main);">
                    <p style="margin-bottom: 15px;"><i class="fa-solid fa-shield-halved" style="color: var(--brand-teal);"></i> <strong>Staff Login Details:</strong> It is highly recommended to change the default admin credentials immediately after deployment to block unauthorized access.</p>
                    <p style="margin-bottom: 15px;"><i class="fa-solid fa-user-lock" style="color: var(--brand-teal);"></i> <strong>Password Strength:</strong> Choose passwords containing lowercase, uppercase, numerical values, and special symbols for maximum strength protection.</p>
                    <p><i class="fa-solid fa-history" style="color: var(--brand-teal);"></i> <strong>Activity Logs:</strong> Security resets are recorded and sessions are cleared immediately, necessitating a fresh login.</p>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
