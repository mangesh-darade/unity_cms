<?php
include '../includes/db.php';
include '../includes/auth.php';
requireAdmin();

$msg = '';

// Secure admin-only PDF download (no direct /uploads/ URLs)
if (isset($_GET['download']) && (int) $_GET['download'] > 0) {
    $report_id = (int) $_GET['download'];
    try {
        $stmt = $db->prepare("SELECT file_path, test_name, patient_id FROM reports WHERE id = :id");
        $stmt->execute([':id' => $report_id]);
        $report = $stmt->fetch();

        if ($report) {
            $file_path = realpath(__DIR__ . '/../' . $report['file_path']);
            $uploads_root = realpath(__DIR__ . '/../uploads');

            if ($file_path && $uploads_root && str_starts_with($file_path, $uploads_root) && is_file($file_path)) {
                header('Content-Type: application/pdf');
                header('Content-Disposition: inline; filename="' . $report['patient_id'] . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $report['test_name']) . '.pdf"');
                header('Content-Length: ' . filesize($file_path));
                readfile($file_path);
                exit();
            }
        }
    } catch (PDOException $e) {
        // fall through to page render
    }
    $msg = '<div class="alert alert-error">Report file could not be opened.</div>';
}

// Handle Report Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_report'])) {
    requireCsrf();
    $patient_id = trim($_POST['patient_id']);
    $test_name = trim($_POST['test_name']);
    
    if (!empty($patient_id) && !empty($test_name) && isset($_FILES['report_file'])) {
        $file = $_FILES['report_file'];
        
        // Check for upload errors
        if ($file['error'] === 0) {
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            // Only allow PDF
            if ($file_ext === 'pdf') {
                // Generate a unique file name to prevent collision
                $clean_test = preg_replace('/[^a-zA-Z0-9]/', '_', strtolower($test_name));
                $new_filename = $patient_id . '_' . $clean_test . '_' . time() . '.pdf';
                
                // Destination path relative to project root
                $db_file_path = 'uploads/' . $new_filename;
                $server_destination = __DIR__ . '/../uploads/' . $new_filename;
                
                if (!is_dir(__DIR__ . '/../uploads')) {
                    mkdir(__DIR__ . '/../uploads', 0755, true);
                }

                if (move_uploaded_file($file['tmp_name'], $server_destination)) {
                    try {
                        $stmt = $db->prepare("INSERT INTO reports (patient_id, test_name, file_path) VALUES (:patient_id, :test_name, :file_path)");
                        $stmt->execute([
                            ':patient_id' => $patient_id,
                            ':test_name' => $test_name,
                            ':file_path' => $db_file_path
                        ]);
                        $msg = '<div class="alert alert-success">Report PDF uploaded and linked to <strong>' . htmlspecialchars($patient_id) . '</strong> successfully!</div>';
                        require_once __DIR__ . '/../includes/notifications.php';
                        notifyPatientReport($db, $cms, $patient_id, $test_name);
                    } catch (PDOException $e) {
                        // Clean up file if DB fails
                        unlink($server_destination);
                        $msg = '<div class="alert alert-error">Database error: ' . $e->getMessage() . '</div>';
                    }
                } else {
                    $msg = '<div class="alert alert-error">Failed to move uploaded file to destination directory.</div>';
                }
            } else {
                $msg = '<div class="alert alert-error">Invalid file format. Only PDF files are allowed.</div>';
            }
        } else {
            $msg = '<div class="alert alert-error">File upload error code: ' . $file['error'] . '</div>';
        }
    } else {
        $msg = '<div class="alert alert-error">Please fill in all fields and select a PDF file.</div>';
    }
}

// Handle Report Delete
if (isset($_GET['delete']) && (int)$_GET['delete'] > 0) {
    $report_id = (int)$_GET['delete'];
    try {
        // Fetch file path to delete from disk
        $stmt = $db->prepare("SELECT file_path FROM reports WHERE id = :id");
        $stmt->execute([':id' => $report_id]);
        $report_file = $stmt->fetchColumn();
        
        if ($report_file) {
            $full_path = __DIR__ . '/../' . $report_file;
            if (file_exists($full_path)) {
                unlink($full_path);
            }
            
            $stmt = $db->prepare("DELETE FROM reports WHERE id = :id");
            $stmt->execute([':id' => $report_id]);
            $msg = '<div class="alert alert-success">Report record deleted successfully.</div>';
        }
    } catch (PDOException $e) {
        $msg = '<div class="alert alert-error">Database deletion error: ' . $e->getMessage() . '</div>';
    }
}

// Fetch all reports joined with patient details
$reports = $db->query("
    SELECT r.*, p.name as patient_name, p.mobile 
    FROM reports r 
    INNER JOIN patients p ON r.patient_id = p.patient_id 
    ORDER BY r.id DESC
")->fetchAll();

// Fetch patients list for selector dropdown
$patients_list = $db->query("SELECT patient_id, name FROM patients ORDER BY name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Lab Reports - Unity Lab Admin</title>
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
            <li class="admin-menu-item active"><a href="reports.php"><i class="fa-solid fa-file-pdf"></i> <span>Upload Reports</span></a></li>
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
                <h1>Report Upload Hub</h1>
                <p>Upload pathology test results and link them to patient records.</p>
            </div>
            <div class="admin-user-info">
                <i class="fa-solid fa-circle-user" style="font-size: 1.5rem; color: #0d9488;"></i>
                <span>Admin</span>
                <a href="index.php?logout=1" class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.85rem; border-radius: 4px;"><i class="fa-solid fa-power-off"></i> Logout</a>
            </div>
        </div>

        <?php echo $msg; ?>

        <div class="grid-2" style="grid-template-columns: 1.3fr 1fr; align-items: start;">
            <!-- Uploaded Reports List -->
            <div>
                <div class="filter-bar">
                    <div class="filter-inputs">
                        <i class="fa-solid fa-magnifying-glass" style="color: #64748b;"></i>
                        <input type="text" id="reportSearch" class="search-input-admin" placeholder="Search by name, ID or test..." style="width: 280px;">
                    </div>
                    <span style="font-size: 0.9rem; color: #64748b;">Total Reports: <strong><?php echo count($reports); ?></strong></span>
                </div>

                <div class="admin-panel-card" style="padding: 10px;">
                    <div class="table-responsive">
                        <table class="table" id="reportsTable">
                            <thead>
                                <tr>
                                    <th>Patient</th>
                                    <th>Test / Panel</th>
                                    <th>File Link</th>
                                    <th>Uploaded On</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($reports)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center" style="padding: 20px; color: #64748b;">No clinical reports uploaded yet.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($reports as $report): ?>
                                        <tr class="report-row" data-search="<?php echo strtolower($report['patient_id'] . ' ' . $report['patient_name'] . ' ' . $report['test_name']); ?>">
                                            <td>
                                                <strong style="color: var(--brand-teal);"><?php echo htmlspecialchars($report['patient_id']); ?></strong><br>
                                                <strong><?php echo htmlspecialchars($report['patient_name']); ?></strong>
                                            </td>
                                            <td><strong><?php echo htmlspecialchars($report['test_name']); ?></strong></td>
                                            <td>
                                                <a href="reports.php?download=<?php echo (int) $report['id']; ?>" target="_blank" style="color: var(--brand-blue); font-weight: 600;">
                                                    <i class="fa-solid fa-file-pdf" style="color: #ef4444;"></i> View PDF
                                                </a>
                                            </td>
                                            <td style="font-size: 0.85rem; color: #64748b;"><?php echo date('d M Y h:i A', strtotime($report['uploaded_at'])); ?></td>
                                            <td>
                                                <a href="reports.php?delete=<?php echo $report['id']; ?>" onclick="return confirm('Are you sure you want to delete this report record and its file?')" class="action-btn btn-action-delete">
                                                    <i class="fa-regular fa-trash-can"></i> Delete
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Upload Panel Form -->
            <div class="admin-panel-card">
                <div class="admin-card-header">
                    <h2>Upload Report PDF</h2>
                </div>
                
                <form action="reports.php" method="POST" enctype="multipart/form-data">
                    <?php echo csrfField(); ?>
                    <div class="form-group">
                        <label for="patient_id" class="form-label">Select Patient <span style="color: #ef4444;">*</span></label>
                        <select id="patient_id" name="patient_id" class="form-control" required>
                            <option value="">-- Choose Registered Patient --</option>
                            <?php foreach ($patients_list as $pat): ?>
                                <option value="<?php echo htmlspecialchars($pat['patient_id']); ?>">
                                    <?php echo htmlspecialchars($pat['name']); ?> (<?php echo htmlspecialchars($pat['patient_id']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p style="font-size: 0.8rem; color: #64748b; margin-top: 5px;"><i class="fa-solid fa-info-circle"></i> Patient must be registered in the <a href="patients.php" style="color: var(--brand-teal); font-weight: 600;">Registry</a> first.</p>
                    </div>

                    <div class="form-group">
                        <label for="test_name" class="form-label">Test / Panel Name <span style="color: #ef4444;">*</span></label>
                        <input type="text" id="test_name" name="test_name" class="form-control" placeholder="e.g. Lipid Profile, Complete Blood Count" required>
                    </div>

                    <div class="form-group">
                        <label for="report_file" class="form-label">Select PDF Report File <span style="color: #ef4444;">*</span></label>
                        <input type="file" id="report_file" name="report_file" class="form-control" accept=".pdf" required style="padding: 8px;">
                        <p style="font-size: 0.8rem; color: #64748b; margin-top: 5px;">Only PDF file formats are supported.</p>
                    </div>

                    <button type="submit" name="upload_report" class="btn btn-teal w-full" style="margin-top: 10px;">
                        <i class="fa-solid fa-cloud-arrow-up"></i> Upload and Authorize Report
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Client-side filter script -->
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const search = document.getElementById('reportSearch');
        const rows = document.querySelectorAll('.report-row');
        
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
