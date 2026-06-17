<?php
/** @var string $admin_nav */
$admin_nav = $admin_nav ?? '';
?>
<div class="admin-sidebar">
    <div class="admin-sidebar-header">
        <div class="logo-icon" style="width: 32px; height: 32px; font-size: 1.1rem;"><i class="fa-solid fa-flask"></i></div>
        <span style="font-family: 'Outfit', sans-serif; font-size: 1.25rem; font-weight: 700; color: #ffffff;">Unity Lab Admin</span>
    </div>

    <ul class="admin-menu">
        <li class="admin-menu-item<?php echo $admin_nav === 'dashboard' ? ' active' : ''; ?>"><a href="index.php"><i class="fa-solid fa-chart-line"></i> <span>Dashboard</span></a></li>
        <li class="admin-menu-item<?php echo $admin_nav === 'analytics' ? ' active' : ''; ?>"><a href="analytics.php"><i class="fa-solid fa-chart-pie"></i> <span>GA4 Analytics</span></a></li>
        <li class="admin-menu-item<?php echo $admin_nav === 'bookings' ? ' active' : ''; ?>"><a href="bookings.php"><i class="fa-solid fa-calendar-check"></i> <span>Bookings</span></a></li>
        <li class="admin-menu-item<?php echo $admin_nav === 'patients' ? ' active' : ''; ?>"><a href="patients.php"><i class="fa-solid fa-users"></i> <span>Patients</span></a></li>
        <li class="admin-menu-item<?php echo $admin_nav === 'reports' ? ' active' : ''; ?>"><a href="reports.php"><i class="fa-solid fa-file-pdf"></i> <span>Upload Reports</span></a></li>
        <li class="admin-menu-item<?php echo $admin_nav === 'inquiries' ? ' active' : ''; ?>"><a href="inquiries.php"><i class="fa-solid fa-envelope-open-text"></i> <span>Inquiries</span></a></li>
        <li class="admin-menu-item<?php echo $admin_nav === 'cms' ? ' active' : ''; ?>"><a href="cms.php"><i class="fa-solid fa-file-pen"></i> <span>CMS Settings</span></a></li>
        <li class="admin-menu-item<?php echo $admin_nav === 'settings' ? ' active' : ''; ?>"><a href="settings.php"><i class="fa-solid fa-sliders"></i> <span>Settings</span></a></li>
    </ul>

    <div class="admin-sidebar-footer">
        Logged in as:<br>
        <strong style="color: #ffffff;"><?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?></strong>
    </div>
</div>
