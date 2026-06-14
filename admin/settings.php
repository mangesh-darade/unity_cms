<?php
include '../includes/db.php';
include '../includes/auth.php';
requireAdmin();

$msg = '';

// Handle notification & integration settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_notifications'])) {
    requireCsrf();
    try {
        $keys = [
            'notify_admin_email', 'mail_from_email', 'mail_from_name',
            'sms_provider', 'msg91_api_key', 'msg91_sender_id'
        ];
        $stmt = $db->prepare(
            dbDriver($db) === 'mysql'
                ? 'REPLACE INTO cms_settings (`key`, value) VALUES (:key, :value)'
                : 'INSERT OR REPLACE INTO cms_settings (key, value) VALUES (:key, :value)'
        );
        foreach ($keys as $k) {
            if (isset($_POST[$k])) {
                $stmt->execute([':key' => $k, ':value' => trim($_POST[$k])]);
            }
        }
        $flags = ['notify_on_booking', 'notify_on_inquiry', 'notify_on_report', 'report_otp_enabled', 'captcha_enabled'];
        foreach ($flags as $flag) {
            $stmt->execute([':key' => $flag, ':value' => isset($_POST[$flag]) ? '1' : '0']);
        }
        foreach ($db->query('SELECT * FROM cms_settings')->fetchAll() as $row) {
            $cms[$row['key']] = $row['value'];
        }
        $msg = '<div class="alert alert-success">Notification and security settings saved.</div>';
    } catch (PDOException $e) {
        $msg = '<div class="alert alert-error">Database error: ' . $e->getMessage() . '</div>';
    }
}

// Handle Password Reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    requireCsrf();
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
                    $db->prepare("INSERT OR REPLACE INTO cms_settings (key, value) VALUES ('admin_password_changed', '1')")->execute();
                    $cms['admin_password_changed'] = '1';
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
                    <?php echo csrfField(); ?>
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

        <div class="admin-panel-card" style="margin-top: 30px;">
            <div class="admin-card-header">
                <h2>Email, SMS &amp; Security Notifications</h2>
                <p style="font-size:0.85rem; color:#64748b;">Configure admin alerts, patient report OTP delivery, and form captcha.</p>
            </div>
            <form action="settings.php" method="POST">
                <?php echo csrfField(); ?>
                <div class="admin-form-row">
                    <div class="form-group">
                        <label class="form-label">Admin Notification Email</label>
                        <input type="email" name="notify_admin_email" class="form-control" value="<?php echo htmlspecialchars($cms['notify_admin_email'] ?? $cms['support_email'] ?? ''); ?>" placeholder="lab-admin@example.com">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Mail From Email</label>
                        <input type="email" name="mail_from_email" class="form-control" value="<?php echo htmlspecialchars($cms['mail_from_email'] ?? $cms['support_email'] ?? ''); ?>">
                    </div>
                </div>
                <div class="admin-form-row">
                    <div class="form-group">
                        <label class="form-label">Mail From Name</label>
                        <input type="text" name="mail_from_name" class="form-control" value="<?php echo htmlspecialchars($cms['mail_from_name'] ?? ($cms['site_name'] ?? 'Unity Clinical Laboratory')); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">SMS Provider</label>
                        <select name="sms_provider" class="form-control">
                            <option value="none" <?php echo ($cms['sms_provider'] ?? 'none') === 'none' ? 'selected' : ''; ?>>None (Email OTP only)</option>
                            <option value="msg91" <?php echo ($cms['sms_provider'] ?? '') === 'msg91' ? 'selected' : ''; ?>>MSG91</option>
                        </select>
                    </div>
                </div>
                <div class="admin-form-row">
                    <div class="form-group">
                        <label class="form-label">MSG91 API Key</label>
                        <input type="text" name="msg91_api_key" class="form-control" value="<?php echo htmlspecialchars($cms['msg91_api_key'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">MSG91 Sender ID</label>
                        <input type="text" name="msg91_sender_id" class="form-control" value="<?php echo htmlspecialchars($cms['msg91_sender_id'] ?? 'UNITY'); ?>">
                    </div>
                </div>
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:12px; margin: 20px 0;">
                    <label style="display:flex; gap:8px; align-items:center;"><input type="checkbox" name="notify_on_booking" value="1" <?php echo ($cms['notify_on_booking'] ?? '1') === '1' ? 'checked' : ''; ?>> Email admin on new booking</label>
                    <label style="display:flex; gap:8px; align-items:center;"><input type="checkbox" name="notify_on_inquiry" value="1" <?php echo ($cms['notify_on_inquiry'] ?? '1') === '1' ? 'checked' : ''; ?>> Email admin on new inquiry</label>
                    <label style="display:flex; gap:8px; align-items:center;"><input type="checkbox" name="notify_on_report" value="1" <?php echo ($cms['notify_on_report'] ?? '1') === '1' ? 'checked' : ''; ?>> Notify patient when report uploaded</label>
                    <label style="display:flex; gap:8px; align-items:center;"><input type="checkbox" name="report_otp_enabled" value="1" <?php echo ($cms['report_otp_enabled'] ?? '1') === '1' ? 'checked' : ''; ?>> Require OTP for report download</label>
                    <label style="display:flex; gap:8px; align-items:center;"><input type="checkbox" name="captcha_enabled" value="1" <?php echo ($cms['captcha_enabled'] ?? '1') === '1' ? 'checked' : ''; ?>> Captcha on public forms</label>
                </div>
                <p style="font-size:0.85rem; color:#64748b; margin-bottom:15px;"><i class="fa-solid fa-circle-info"></i> Email uses PHP <code>mail()</code>. OTP is sent to the patient's registered email, or via MSG91 SMS if configured.</p>
                <button type="submit" name="save_notifications" class="btn btn-teal"><i class="fa-solid fa-bell"></i> Save Notification Settings</button>
            </form>
        </div>

        <div class="admin-panel-card" style="margin-top: 30px;">
            <div class="admin-card-header">
                <h2>Database Configuration</h2>
            </div>
            <p style="font-size:0.95rem; line-height:1.7; color:var(--text-main);">
                Current driver: <strong><?php echo htmlspecialchars(strtoupper(DB_DRIVER)); ?></strong>.
                To use MySQL, copy <code>includes/config.local.php.example</code> to <code>includes/config.local.php</code>,
                set <code>DB_DRIVER</code> to <code>mysql</code>, create the database, and reload the site.
            </p>
        </div>
    </div>

</body>
</html>
