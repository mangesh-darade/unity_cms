<?php
include '../includes/db.php';
include '../includes/auth.php';
requireAdmin();

// Handle Status Updates
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $booking_id = (int)$_POST['booking_id'];
    $status = trim($_POST['status']);
    
    if ($booking_id > 0 && in_array($status, ['Pending', 'Approved', 'Completed', 'Cancelled'])) {
        try {
            $stmt = $db->prepare("UPDATE bookings SET status = :status WHERE id = :id");
            $stmt->execute([':status' => $status, ':id' => $booking_id]);
            $msg = '<div class="alert alert-success">Booking status updated successfully!</div>';
        } catch (PDOException $e) {
            $msg = '<div class="alert alert-error">Database error: ' . $e->getMessage() . '</div>';
        }
    }
}

// Fetch all bookings
$bookings = $db->query("SELECT * FROM bookings ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - Unity Lab Admin</title>
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
            <li class="admin-menu-item active"><a href="bookings.php"><i class="fa-solid fa-calendar-check"></i> <span>Bookings</span></a></li>
            <li class="admin-menu-item"><a href="patients.php"><i class="fa-solid fa-users"></i> <span>Patients</span></a></li>
            <li class="admin-menu-item"><a href="reports.php"><i class="fa-solid fa-file-pdf"></i> <span>Upload Reports</span></a></li>
            <li class="admin-menu-item"><a href="inquiries.php"><i class="fa-solid fa-envelope-open-text"></i> <span>Inquiries</span></a></li>
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
                <h1>Test Bookings Management</h1>
                <p>Track and manage patient home collection appointments.</p>
            </div>
            <div class="admin-user-info">
                <i class="fa-solid fa-circle-user" style="font-size: 1.5rem; color: #0d9488;"></i>
                <span>Admin</span>
                <a href="index.php?logout=1" class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.85rem; border-radius: 4px;"><i class="fa-solid fa-power-off"></i> Logout</a>
            </div>
        </div>

        <?php echo $msg; ?>

        <!-- Search and Filter Bar -->
        <div class="filter-bar">
            <div class="filter-inputs">
                <i class="fa-solid fa-filter" style="color: #64748b;"></i>
                <input type="text" id="bookingSearch" class="search-input-admin" placeholder="Search by name or mobile...">
            </div>
            <span style="font-size: 0.9rem; color: #64748b;">Total Bookings: <strong><?php echo count($bookings); ?></strong></span>
        </div>

        <!-- Bookings Data Table -->
        <div class="admin-panel-card" style="padding: 10px;">
            <div class="table-responsive">
                <table class="table" id="bookingsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Patient Details</th>
                            <th>Test / Package Requested</th>
                            <th>Schedule Date</th>
                            <th>Address</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($bookings)): ?>
                            <tr>
                                <td colspan="7" class="text-center" style="padding: 30px; color: #64748b;">No bookings logged.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($bookings as $booking): 
                                // Prefilled whatsapp message URL
                                $wa_text = urlencode("Hello " . $booking['name'] . ", this is Unity Clinical Laboratory support. We received your home collection request for " . $booking['test_type'] . " on " . $booking['preferred_date'] . ". Please confirm your location address so we can assign our collector. Thank you!");
                                $wa_url = "https://wa.me/91" . $booking['mobile'] . "?text=" . $wa_text;
                            ?>
                                <tr class="booking-row" data-search="<?php echo strtolower($booking['name'] . ' ' . $booking['mobile']); ?>">
                                    <td>#<?php echo $booking['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($booking['name']); ?></strong><br>
                                        <span style="font-size: 0.85rem; color: #64748b;"><i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($booking['mobile']); ?></span><br>
                                        <?php if (!empty($booking['email'])): ?>
                                            <span style="font-size: 0.85rem; color: #64748b;"><i class="fa-solid fa-envelope"></i> <?php echo htmlspecialchars($booking['email']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($booking['test_type']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($booking['preferred_date']); ?></td>
                                    <td style="max-width: 200px; font-size: 0.85rem; line-height: 1.4;"><?php echo htmlspecialchars($booking['address']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo strtolower($booking['status']); ?>">
                                            <?php echo htmlspecialchars($booking['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 8px; flex-direction: column;">
                                            <!-- WhatsApp Button -->
                                            <a href="<?php echo $wa_url; ?>" target="_blank" class="action-btn btn-action-whatsapp text-center" style="width: 100%; justify-content: center;">
                                                <i class="fa-brands fa-whatsapp"></i> Chat
                                            </a>
                                            
                                            <!-- Status Update Form Toggle -->
                                            <form action="bookings.php" method="POST" style="margin-top: 4px;">
                                                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                <select name="status" onchange="this.form.submit()" class="form-control" style="font-size: 0.8rem; padding: 4px 8px; height: auto;">
                                                    <option value="">-- Update --</option>
                                                    <option value="Pending" <?php echo ($booking['status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="Approved" <?php echo ($booking['status'] === 'Approved') ? 'selected' : ''; ?>>Approved</option>
                                                    <option value="Completed" <?php echo ($booking['status'] === 'Completed') ? 'selected' : ''; ?>>Completed</option>
                                                    <option value="Cancelled" <?php echo ($booking['status'] === 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                                </select>
                                                <input type="hidden" name="update_status" value="1">
                                            </form>
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
        const search = document.getElementById('bookingSearch');
        const rows = document.querySelectorAll('.booking-row');
        
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
