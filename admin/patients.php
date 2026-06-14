<?php
include '../includes/db.php';
include '../includes/auth.php';
requireAdmin();

$msg = '';

// Handle Patient Registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_patient'])) {
    requireCsrf();
    $name = trim($_POST['name']);
    $mobile = trim($_POST['mobile']);
    $email = trim($_POST['email']);
    $gender = trim($_POST['gender']);
    $age = (int)$_POST['age'];
    
    if (!empty($name) && !empty($mobile)) {
        if (preg_match('/^[0-9]{10}$/', $mobile)) {
            try {
                // Generate Patient ID
                $new_id = generatePatientID($db);
                
                $stmt = $db->prepare("INSERT INTO patients (patient_id, name, email, mobile, gender, age) VALUES (:patient_id, :name, :email, :mobile, :gender, :age)");
                $stmt->execute([
                    ':patient_id' => $new_id,
                    ':name' => htmlspecialchars($name),
                    ':email' => !empty($email) ? htmlspecialchars($email) : null,
                    ':mobile' => htmlspecialchars($mobile),
                    ':gender' => htmlspecialchars($gender),
                    ':age' => $age > 0 ? $age : null
                ]);
                $msg = '<div class="alert alert-success">Patient registered successfully! Patient ID is <strong>' . $new_id . '</strong>.</div>';
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $msg = '<div class="alert alert-error">A patient with this ID or detail already exists.</div>';
                } else {
                    $msg = '<div class="alert alert-error">Database error: ' . $e->getMessage() . '</div>';
                }
            }
        } else {
            $msg = '<div class="alert alert-error">Mobile number must be a valid 10-digit number.</div>';
        }
    } else {
        $msg = '<div class="alert alert-error">Please fill in all mandatory fields (Name and Mobile).</div>';
    }
}

// Fetch all patients
$patients = $db->query("SELECT * FROM patients ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Management - Unity Lab Admin</title>
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
            <li class="admin-menu-item active"><a href="patients.php"><i class="fa-solid fa-users"></i> <span>Patients</span></a></li>
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
                <h1>Patient Management</h1>
                <p>Register new clients and search existing diagnostic profiles.</p>
            </div>
            <div class="admin-user-info">
                <i class="fa-solid fa-circle-user" style="font-size: 1.5rem; color: #0d9488;"></i>
                <span>Admin</span>
                <a href="index.php?logout=1" class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.85rem; border-radius: 4px;"><i class="fa-solid fa-power-off"></i> Logout</a>
            </div>
        </div>

        <?php echo $msg; ?>

        <div class="grid-2" style="grid-template-columns: 1.3fr 1fr; align-items: start;">
            <!-- Patients List Card -->
            <div>
                <div class="filter-bar">
                    <div class="filter-inputs">
                        <i class="fa-solid fa-magnifying-glass" style="color: #64748b;"></i>
                        <input type="text" id="patientSearch" class="search-input-admin" placeholder="Search by ID, name or mobile..." style="width: 280px;">
                    </div>
                    <span style="font-size: 0.9rem; color: #64748b;">Total Patients: <strong><?php echo count($patients); ?></strong></span>
                </div>

                <div class="admin-panel-card" style="padding: 10px;">
                    <div class="table-responsive">
                        <table class="table" id="patientsTable">
                            <thead>
                                <tr>
                                    <th>Patient ID</th>
                                    <th>Name</th>
                                    <th>Contact Info</th>
                                    <th>Demographics</th>
                                    <th>Registered</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($patients)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center" style="padding: 20px; color: #64748b;">No patients registered yet.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($patients as $patient): ?>
                                        <tr class="patient-row" data-search="<?php echo strtolower($patient['patient_id'] . ' ' . $patient['name'] . ' ' . $patient['mobile']); ?>">
                                            <td><strong style="color: var(--brand-teal);"><?php echo htmlspecialchars($patient['patient_id']); ?></strong></td>
                                            <td><strong><?php echo htmlspecialchars($patient['name']); ?></strong></td>
                                            <td>
                                                <i class="fa-solid fa-phone" style="font-size: 0.8rem; color: #64748b;"></i> <?php echo htmlspecialchars($patient['mobile']); ?><br>
                                                <?php if (!empty($patient['email'])): ?>
                                                    <i class="fa-solid fa-envelope" style="font-size: 0.8rem; color: #64748b;"></i> <?php echo htmlspecialchars($patient['email']); ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($patient['gender'] ?? 'N/A'); ?><br>
                                                <span style="font-size: 0.85rem; color: #64748b;"><?php echo htmlspecialchars($patient['age'] ? $patient['age'] . ' yrs' : 'N/A'); ?></span>
                                            </td>
                                            <td style="font-size: 0.8rem; color: #64748b;"><?php echo date('d M Y', strtotime($patient['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Register New Patient Card -->
            <div class="admin-panel-card">
                <div class="admin-card-header">
                    <h2>Register New Patient</h2>
                </div>
                
                <form action="patients.php" method="POST">
                    <?php echo csrfField(); ?>
                    <div class="form-group">
                        <label for="name" class="form-label">Full Name <span style="color: #ef4444;">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" placeholder="Enter patient name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="mobile" class="form-label">Mobile Number <span style="color: #ef4444;">*</span></label>
                        <input type="tel" id="mobile" name="mobile" class="form-control" placeholder="10-digit number" required>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="patient@example.com">
                    </div>

                    <div class="admin-form-row">
                        <div class="form-group">
                            <label for="gender" class="form-label">Gender</label>
                            <select id="gender" name="gender" class="form-control">
                                <option value="">Select</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="age" class="form-label">Age (Years)</label>
                            <input type="number" id="age" name="age" class="form-control" placeholder="e.g. 35" min="1" max="120">
                        </div>
                    </div>

                    <button type="submit" name="register_patient" class="btn btn-teal w-full" style="margin-top: 10px;">
                        <i class="fa-solid fa-user-plus"></i> Register Patient
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Client-side filter script -->
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const search = document.getElementById('patientSearch');
        const rows = document.querySelectorAll('.patient-row');
        
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
