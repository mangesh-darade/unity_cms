<?php
include '../includes/db.php';
include '../includes/auth.php';
requireAdmin();

$msg = '';

// Handle Status Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    requireCsrf();
    $inq_id = (int)$_POST['inq_id'];
    $status = trim($_POST['status']);
    
    if ($inq_id > 0 && in_array($status, ['New', 'Read', 'Replied'])) {
        try {
            $stmt = $db->prepare("UPDATE inquiries SET status = :status WHERE id = :id");
            $stmt->execute([':status' => $status, ':id' => $inq_id]);
            $msg = '<div class="alert alert-success">Inquiry status updated successfully.</div>';
        } catch (PDOException $e) {
            $msg = '<div class="alert alert-error">Database error: ' . $e->getMessage() . '</div>';
        }
    }
}

// Handle Delete Inquiry
if (isset($_GET['delete']) && (int)$_GET['delete'] > 0) {
    $inq_id = (int)$_GET['delete'];
    try {
        $stmt = $db->prepare("DELETE FROM inquiries WHERE id = :id");
        $stmt->execute([':id' => $inq_id]);
        $msg = '<div class="alert alert-success">Inquiry deleted successfully.</div>';
    } catch (PDOException $e) {
        $msg = '<div class="alert alert-error">Database deletion error: ' . $e->getMessage() . '</div>';
    }
}

// Fetch all inquiries
$inquiries = $db->query("SELECT * FROM inquiries ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inquiry Inbox - Unity Lab Admin</title>
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
            <li class="admin-menu-item active"><a href="inquiries.php"><i class="fa-solid fa-envelope-open-text"></i> <span>Inquiries</span></a></li>
            <li class="admin-menu-item"><a href="cms.php"><i class="fa-solid fa-file-pen"></i> <span>CMS Settings</span></a></li>
            <li class="admin-menu-item"><a href="settings.php"><i class="fa-solid fa-sliders"></i> <span>Settings</span></a></li>
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
                <h1>Inquiry Management</h1>
                <p>Review and reply to diagnostic general inquiries and pricing requests.</p>
            </div>
            <div class="admin-user-info">
                <i class="fa-solid fa-circle-user" style="font-size: 1.5rem; color: #0d9488;"></i>
                <span>Admin</span>
                <a href="index.php?logout=1" class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.85rem; border-radius: 4px;"><i class="fa-solid fa-power-off"></i> Logout</a>
            </div>
        </div>

        <?php echo $msg; ?>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <div class="filter-inputs">
                <i class="fa-solid fa-filter" style="color: #64748b;"></i>
                <input type="text" id="inqSearch" class="search-input-admin" placeholder="Search by name, email, subject...">
            </div>
            <span style="font-size: 0.9rem; color: #64748b;">Total Inquiries: <strong><?php echo count($inquiries); ?></strong></span>
        </div>

        <!-- Inquiries Table Card -->
        <div class="admin-panel-card" style="padding: 10px;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Sender Details</th>
                            <th>Subject</th>
                            <th>Message</th>
                            <th>Received On</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($inquiries)): ?>
                            <tr>
                                <td colspan="6" class="text-center" style="padding: 30px; color: #64748b;">No contact form messages logged.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($inquiries as $inq): 
                                $wa_text = urlencode("Hello " . $inq['name'] . ", this is Unity Clinical Laboratory helpdesk responding to your inquiry regarding '" . $inq['subject'] . "'. How can we help you today?");
                                $wa_url = "https://wa.me/91" . $inq['mobile'] . "?text=" . $wa_text;
                            ?>
                                <tr class="inquiry-row" data-search="<?php echo strtolower($inq['name'] . ' ' . $inq['email'] . ' ' . $inq['subject']); ?>">
                                    <td>
                                        <strong><?php echo htmlspecialchars($inq['name']); ?></strong><br>
                                        <span style="font-size: 0.85rem; color: #64748b;"><i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($inq['mobile']); ?></span><br>
                                        <span style="font-size: 0.85rem; color: #64748b;"><i class="fa-solid fa-envelope"></i> <?php echo htmlspecialchars($inq['email']); ?></span>
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($inq['subject']); ?></strong></td>
                                    <td style="max-width: 300px; font-size: 0.85rem; line-height: 1.4;"><?php echo nl2br(htmlspecialchars($inq['message'])); ?></td>
                                    <td style="font-size: 0.85rem; color: #64748b;"><?php echo date('d M Y h:i A', strtotime($inq['created_at'])); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo strtolower($inq['status']); ?>">
                                            <?php echo htmlspecialchars($inq['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 8px; flex-direction: column;">
                                            <!-- WhatsApp Chat -->
                                            <a href="<?php echo $wa_url; ?>" target="_blank" class="action-btn btn-action-whatsapp text-center" style="width: 100%; justify-content: center;">
                                                <i class="fa-brands fa-whatsapp"></i> Reply
                                            </a>
                                            
                                            <!-- Status Toggle Form -->
                                            <form action="inquiries.php" method="POST" style="margin-top: 4px;">
                                                <?php echo csrfField(); ?>
                                                <input type="hidden" name="inq_id" value="<?php echo $inq['id']; ?>">
                                                <select name="status" onchange="this.form.submit()" class="form-control" style="font-size: 0.8rem; padding: 4px 8px; height: auto;">
                                                    <option value="">-- Mark --</option>
                                                    <option value="New" <?php echo ($inq['status'] === 'New') ? 'selected' : ''; ?>>New</option>
                                                    <option value="Read" <?php echo ($inq['status'] === 'Read') ? 'selected' : ''; ?>>Read</option>
                                                    <option value="Replied" <?php echo ($inq['status'] === 'Replied') ? 'selected' : ''; ?>>Replied</option>
                                                </select>
                                                <input type="hidden" name="update_status" value="1">
                                            </form>
                                            
                                            <!-- Delete -->
                                            <a href="inquiries.php?delete=<?php echo $inq['id']; ?>" onclick="return confirm('Are you sure you want to delete this message record?')" class="action-btn btn-action-delete text-center" style="margin-top: 4px; justify-content: center;">
                                                <i class="fa-regular fa-trash-can"></i> Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Client-side filter script -->
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const search = document.getElementById('inqSearch');
        const rows = document.querySelectorAll('.inquiry-row');
        
        search.addEventListener('input', () => {
            const query = search.value.toLowerCase().trim();
            rows.forEach(row => {
                const searchData = row.getAttribute('data-search');
                if (searchData.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
    </script>
</body>
</html>
