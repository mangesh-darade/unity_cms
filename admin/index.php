<?php
include '../includes/db.php';
include '../includes/auth.php';

// 1. Handle Logout
if (isset($_GET['logout']) && $_GET['logout'] === '1') {
    $_SESSION = [];
    session_destroy();
    header("Location: index.php");
    exit();
}

// 2. Handle Login Submit
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_submit'])) {
    requireCsrf();
    if (!rateLimit('admin_login', 5, 900)) {
        $error = 'Too many login attempts. Please wait 15 minutes and try again.';
    } else {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if (!empty($username) && !empty($password)) {
        try {
            $stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $user['username'];
                header("Location: index.php");
                exit();
            } else {
                $error = 'Invalid username or password.';
            }
        } catch (PDOException $e) {
            $error = APP_DEBUG ? 'Database error: ' . $e->getMessage() : 'Unable to sign in right now. Please try again.';
        }
    } else {
        $error = 'Please fill in all fields.';
    }
    }
}

// 3. Render Login Form if NOT logged in
if (!isAdminLoggedIn()) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Unity Lab</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-body">
    <div class="login-wrapper">
        <div class="login-card">
            <div style="text-align: center; margin-bottom: 30px;">
                <div class="logo-icon" style="margin: 0 auto 15px auto; width: 50px; height: 50px; font-size: 1.6rem;"><i class="fa-solid fa-flask"></i></div>
                <h2 style="font-size: 1.6rem; color: #0f172a;">Unity Lab Admin</h2>
                <p style="color: #64748b; font-size: 0.9rem; margin-top: 5px;">Sign in to access patient registry & bookings</p>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-error" style="padding: 10px 15px; font-size: 0.85rem;"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form action="index.php" method="POST">
                <?php echo csrfField(); ?>
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Enter username" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required>
                </div>
                <button type="submit" name="login_submit" class="btn btn-primary w-full" style="margin-top: 10px;">
                    <i class="fa-solid fa-right-to-bracket"></i> Login to Dashboard
                </button>
            </form>
            <div style="margin-top: 25px; text-align: center;">
                <a href="../index.php" style="font-size: 0.85rem; color: #0d9488; font-weight: 600;"><i class="fa-solid fa-arrow-left"></i> Back to Main Website</a>
            </div>
        </div>
    </div>
</body>
</html>
<?php
exit();
}

// 4. Render Admin Dashboard Dashboard if logged in
$admin_nav = 'dashboard';
$password_warning = ($cms['admin_password_changed'] ?? '0') !== '1'
    ? '<div class="alert alert-error" style="margin-bottom: 20px;"><i class="fa-solid fa-triangle-exclamation"></i> <strong>Security:</strong> Please change the default admin password in <a href="settings.php" style="color: inherit; font-weight: 700;">Settings</a> before going live.</div>'
    : '';

// Fetch count parameters
$total_bookings = $db->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$pending_bookings = $db->query("SELECT COUNT(*) FROM bookings WHERE status = 'Pending'")->fetchColumn();
$total_patients = $db->query("SELECT COUNT(*) FROM patients")->fetchColumn();
$new_inquiries = $db->query("SELECT COUNT(*) FROM inquiries WHERE status = 'New'")->fetchColumn();

// Fetch recent lists
$recent_bookings = $db->query("SELECT * FROM bookings ORDER BY id DESC LIMIT 5")->fetchAll();
$recent_inquiries = $db->query("SELECT * FROM inquiries ORDER BY id DESC LIMIT 5")->fetchAll();

require_once __DIR__ . '/../includes/ga4_analytics.php';
$ga4PropertyId = trim($cms['ga4_property_id'] ?? '');
$ga4MeasurementId = trim($cms['google_analytics_id'] ?? '');
$ga4Connected = $ga4PropertyId !== '' && ga4CredentialsConfigured();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Unity Lab</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .ga4-rt-pulse { width: 10px; height: 10px; border-radius: 50%; background: #22c55e; box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); animation: ga4Pulse 1.5s infinite; display: inline-block; }
        .ga4-rt-badge { background: #dcfce7; color: #15803d; font-size: 0.7rem; font-weight: 700; padding: 4px 10px; border-radius: 999px; }
        @keyframes ga4Pulse { 0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); } 70% { box-shadow: 0 0 0 8px rgba(34, 197, 94, 0); } 100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); } }
        .ga4-rt-mini-list { margin: 12px 0 0; padding-left: 18px; color: #475569; font-size: 0.9rem; }
        .ga4-rt-mini-list li { margin-bottom: 6px; }
    </style>
</head>
<body class="admin-body">

<?php include __DIR__ . '/includes/sidebar.php'; ?>

    <!-- Main Content Area -->
    <div class="admin-main">
        <div class="admin-header">
            <div class="admin-title">
                <h1>Dashboard Overview</h1>
                <p>Welcome back, Administrator. Here are today's laboratory analytics.</p>
            </div>
            <div class="admin-user-info">
                <i class="fa-solid fa-circle-user" style="font-size: 1.5rem; color: #0d9488;"></i>
                <span>Admin</span>
                <a href="index.php?logout=1" class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.85rem; border-radius: 4px;"><i class="fa-solid fa-power-off"></i> Logout</a>
            </div>
        </div>

        <?php echo $password_warning; ?>

        <!-- Metric Counter Grid -->
        <div class="stats-grid">
            <!-- Metric 1 -->
            <div class="stat-widget">
                <div class="stat-info">
                    <h3>Total Bookings</h3>
                    <p><?php echo $total_bookings; ?></p>
                </div>
                <div class="stat-icon blue"><i class="fa-solid fa-calendar-check"></i></div>
            </div>
            <!-- Metric 2 -->
            <div class="stat-widget">
                <div class="stat-info">
                    <h3>Pending Bookings</h3>
                    <p><?php echo $pending_bookings; ?></p>
                </div>
                <div class="stat-icon orange"><i class="fa-solid fa-clock"></i></div>
            </div>
            <!-- Metric 3 -->
            <div class="stat-widget">
                <div class="stat-info">
                    <h3>Registered Patients</h3>
                    <p><?php echo $total_patients; ?></p>
                </div>
                <div class="stat-icon green"><i class="fa-solid fa-users"></i></div>
            </div>
            <!-- Metric 4 -->
            <div class="stat-widget">
                <div class="stat-info">
                    <h3>New Inquiries</h3>
                    <p><?php echo $new_inquiries; ?></p>
                </div>
                <div class="stat-icon teal"><i class="fa-solid fa-envelope-open-text"></i></div>
            </div>
        </div>

        <div class="admin-panel-card" style="margin-top: 30px;" id="ga4-realtime-panel">
            <div class="admin-card-header">
                <h2 style="display:flex; align-items:center; gap:10px;">
                    <span class="ga4-rt-pulse"></span>
                    Live Website Visitors (GA4 Realtime)
                </h2>
                <div style="display:flex; align-items:center; gap:10px;">
                    <span class="ga4-rt-badge" id="ga4-rt-status">LIVE</span>
                    <a href="analytics.php" class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.8rem;">Full GA4 Dashboard</a>
                </div>
            </div>
            <?php if ($ga4MeasurementId === ''): ?>
                <p style="color:#64748b; margin:0;">Add your <strong>G-XXXXXXXX</strong> Measurement ID in <a href="cms.php#tab-marketing">CMS → Digital Marketing</a> to start tracking visitors on the public site.</p>
            <?php elseif (!$ga4Connected): ?>
                <p style="color:#64748b; margin:0;">Site tracking: <code><?php echo htmlspecialchars($ga4MeasurementId); ?></code>. Connect Property ID + service account on <a href="analytics.php">GA4 Analytics</a> to see <strong>live</strong> visitor counts here.</p>
            <?php else: ?>
                <p id="ga4-rt-error" class="alert alert-error" style="display:none; margin-bottom:12px;"></p>
                <div style="display:flex; align-items:center; gap:24px; flex-wrap:wrap;">
                    <div>
                        <div style="font-size:0.85rem; color:#64748b; text-transform:uppercase; font-weight:600;">Active users right now</div>
                        <div id="ga4-rt-active-users" style="font-size:2.5rem; font-weight:700; color:#0d9488; line-height:1.1;">—</div>
                        <div id="ga4-rt-updated" style="font-size:0.8rem; color:#94a3b8; margin-top:4px;">Loading realtime data…</div>
                    </div>
                    <div style="flex:1; min-width:220px;">
                        <div style="font-size:0.85rem; color:#64748b; font-weight:600; margin-bottom:6px;">Pages being viewed</div>
                        <ul class="ga4-rt-mini-list" id="ga4-rt-mini-pages">
                            <li style="color:#94a3b8;">Loading…</li>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="grid-2" style="margin-top: 30px;">
            <!-- Recent Bookings Widget -->
            <div class="admin-panel-card">
                <div class="admin-card-header">
                    <h2>Recent Home Collections</h2>
                    <a href="bookings.php" class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.8rem;">View All</a>
                </div>
                
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Test Type</th>
                                <th>Preferred Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_bookings)): ?>
                                <tr>
                                    <td colspan="4" class="text-center" style="color: #64748b;">No bookings received yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recent_bookings as $booking): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($booking['name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($booking['test_type']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['preferred_date']); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo strtolower($booking['status']); ?>">
                                                <?php echo htmlspecialchars($booking['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Inquiries Widget -->
            <div class="admin-panel-card">
                <div class="admin-card-header">
                    <h2>Recent Inquiries</h2>
                    <a href="inquiries.php" class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.8rem;">View All</a>
                </div>
                
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Subject</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_inquiries)): ?>
                                <tr>
                                    <td colspan="4" class="text-center" style="color: #64748b;">No inquiries received yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recent_inquiries as $inquiry): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($inquiry['name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($inquiry['subject']); ?></td>
                                        <td><?php echo date('d M Y', strtotime($inquiry['created_at'])); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo strtolower($inquiry['status']); ?>">
                                                <?php echo htmlspecialchars($inquiry['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

<?php if ($ga4Connected): ?>
<script src="js/ga4-realtime.js"></script>
<?php endif; ?>
</body>
</html>

