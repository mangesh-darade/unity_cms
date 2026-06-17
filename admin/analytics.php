<?php
include '../includes/db.php';
include '../includes/auth.php';
require_once __DIR__ . '/../includes/ga4_analytics.php';
requireAdmin();

$admin_nav = 'analytics';
$msg = '';
$ga4Data = null;
$propertyId = trim($cms['ga4_property_id'] ?? '');
$lookerUrl = trim($cms['ga4_looker_embed_url'] ?? '');
$measurementId = trim($cms['google_analytics_id'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_ga4_config'])) {
    requireCsrf();
    try {
        $stmt = $db->prepare(
            dbDriver($db) === 'mysql'
                ? 'REPLACE INTO cms_settings (`key`, value) VALUES (:key, :value)'
                : 'INSERT OR REPLACE INTO cms_settings (key, value) VALUES (:key, :value)'
        );
        foreach (['ga4_property_id', 'ga4_looker_embed_url'] as $key) {
            if (isset($_POST[$key])) {
                $stmt->execute([':key' => $key, ':value' => trim($_POST[$key])]);
                $cms[$key] = trim($_POST[$key]);
            }
        }
        $propertyId = trim($cms['ga4_property_id'] ?? '');
        $lookerUrl = trim($cms['ga4_looker_embed_url'] ?? '');

        if (!empty($_FILES['ga4_service_account']['tmp_name']) && is_uploaded_file($_FILES['ga4_service_account']['tmp_name'])) {
            $json = file_get_contents($_FILES['ga4_service_account']['tmp_name']);
            $parsed = json_decode($json, true);
            if (!is_array($parsed) || empty($parsed['client_email']) || empty($parsed['private_key'])) {
                $msg = '<div class="alert alert-error">Invalid service account JSON file.</div>';
            } else {
                $dir = dirname(__DIR__) . '/storage/ga4';
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                file_put_contents(ga4ServiceAccountPath(), $json);
                @unlink(ga4DashboardCachePath());
                @unlink(dirname(__DIR__) . '/storage/ga4/token-cache.json');
                $msg = '<div class="alert alert-success">GA4 credentials saved successfully.</div>';
            }
        } else {
            $msg = '<div class="alert alert-success">GA4 settings saved.</div>';
        }
    } catch (PDOException $e) {
        $msg = '<div class="alert alert-error">Database error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

$refresh = isset($_GET['refresh']) && $_GET['refresh'] === '1';
$ga4Realtime = null;
if ($propertyId !== '' && ga4CredentialsConfigured()) {
    $ga4Realtime = ga4FetchRealtimeData($propertyId);
    $ga4Data = ga4FetchDashboardData($propertyId, $refresh);
}

$since7d = dbDriver($db) === 'mysql'
    ? "DATE_SUB(NOW(), INTERVAL 7 DAY)"
    : "datetime('now', '-7 days')";
$crmBookings7 = (int) $db->query("SELECT COUNT(*) FROM bookings WHERE created_at >= {$since7d}")->fetchColumn();
$crmInquiries7 = (int) $db->query("SELECT COUNT(*) FROM inquiries WHERE created_at >= {$since7d}")->fetchColumn();
try {
    $crmReviews7 = (int) $db->query("SELECT COUNT(*) FROM cms_testimonials WHERE source = 'website' AND created_at >= {$since7d}")->fetchColumn();
} catch (PDOException $e) {
    $crmReviews7 = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GA4 Analytics - Unity Lab Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .ga4-chart-bars { display: flex; align-items: flex-end; gap: 8px; height: 140px; margin-top: 16px; }
        .ga4-bar-col { flex: 1; text-align: center; min-width: 0; }
        .ga4-bar { background: linear-gradient(180deg, #0d9488, #14b8a6); border-radius: 4px 4px 0 0; min-height: 4px; }
        .ga4-bar-label { font-size: 0.65rem; color: #64748b; margin-top: 6px; overflow: hidden; text-overflow: ellipsis; }
        .ga4-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        .ga4-table th, .ga4-table td { padding: 10px 12px; border-bottom: 1px solid #e2e8f0; text-align: left; }
        .ga4-table th { color: #64748b; font-weight: 600; font-size: 0.8rem; text-transform: uppercase; }
        .ga4-embed { width: 100%; min-height: 520px; border: 0; border-radius: 8px; background: #f8fafc; }
        .ga4-setup-steps { margin: 0; padding-left: 20px; color: #475569; line-height: 1.7; }
        .ga4-setup-steps a { color: #0d9488; font-weight: 600; }
        .ga4-rt-pulse { width: 10px; height: 10px; border-radius: 50%; background: #22c55e; box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); animation: ga4Pulse 1.5s infinite; display: inline-block; }
        .ga4-rt-badge { background: #dcfce7; color: #15803d; font-size: 0.75rem; font-weight: 700; padding: 6px 12px; border-radius: 999px; letter-spacing: 0.05em; }
        .ga4-rt-badge.ga4-rt-status--error { background: #fee2e2; color: #b91c1c; }
        @keyframes ga4Pulse { 0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); } 70% { box-shadow: 0 0 0 10px rgba(34, 197, 94, 0); } 100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); } }
    </style>
</head>
<body class="admin-body">

<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div class="admin-main">
    <div class="admin-header">
        <div class="admin-title">
            <h1>GA4 Analytics Dashboard</h1>
            <p>Realtime visitor data from Google Analytics 4, plus historical reports and conversions.</p>
        </div>
        <div class="admin-user-info">
            <?php if ($propertyId !== '' && ga4CredentialsConfigured()): ?>
                <a href="analytics.php?refresh=1" class="btn btn-secondary" style="margin-right:8px;"><i class="fa-solid fa-rotate"></i> Refresh</a>
            <?php endif; ?>
            <a href="https://analytics.google.com/" target="_blank" rel="noopener" class="btn btn-teal"><i class="fa-solid fa-arrow-up-right-from-square"></i> Open GA4</a>
            <a href="index.php?logout=1" class="btn btn-secondary" style="padding: 6px 12px; margin-left: 8px;"><i class="fa-solid fa-power-off"></i></a>
        </div>
    </div>

    <?php echo $msg; ?>

    <div class="admin-panel-card" style="margin-bottom: 24px;">
        <div class="admin-card-header">
            <h2><i class="fa-solid fa-plug"></i> GA4 Connection Setup</h2>
            <p style="font-size:0.85rem; color:#64748b;">Tracking ID on site: <strong><?php echo $measurementId !== '' ? htmlspecialchars($measurementId) : 'Not set — add in CMS → Digital Marketing'; ?></strong></p>
        </div>
        <form action="analytics.php" method="POST" enctype="multipart/form-data">
            <?php echo csrfField(); ?>
            <div class="admin-form-row">
                <div class="form-group">
                    <label class="form-label">GA4 Property ID (numeric)</label>
                    <input type="text" name="ga4_property_id" class="form-control" value="<?php echo htmlspecialchars($propertyId); ?>" placeholder="e.g. 123456789">
                    <small style="color:#64748b;">GA4 → Admin → Property settings → Property ID (numbers only, not G-XXXXXXXX)</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Service Account JSON</label>
                    <input type="file" name="ga4_service_account" class="form-control" accept=".json,application/json" style="padding:8px;">
                    <small style="color:#64748b;">
                        <?php if (ga4CredentialsConfigured()): ?>
                            <i class="fa-solid fa-circle-check" style="color:#16a34a;"></i> JSON uploaded (<?php echo htmlspecialchars(ga4LoadServiceAccount()['client_email'] ?? ''); ?>)
                        <?php else: ?>
                            Upload GCP service account key file
                        <?php endif; ?>
                    </small>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Looker Studio Embed URL (optional)</label>
                <input type="url" name="ga4_looker_embed_url" class="form-control" value="<?php echo htmlspecialchars($lookerUrl); ?>" placeholder="https://lookerstudio.google.com/embed/reporting/...">
                <small style="color:#64748b;">Paste embed link for full Google dashboard inside admin (alternative to API cards below).</small>
            </div>
            <button type="submit" name="save_ga4_config" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save GA4 Configuration</button>
        </form>

        <details style="margin-top: 20px;">
            <summary style="cursor:pointer; font-weight:600; color:var(--primary);">Setup instructions (one-time)</summary>
            <ol class="ga4-setup-steps" style="margin-top:12px;">
                <li>Create a property at <a href="https://analytics.google.com/" target="_blank" rel="noopener">Google Analytics</a> and add Measurement ID <code>G-...</code> in <a href="cms.php#tab-marketing">CMS → Digital Marketing</a>.</li>
                <li>In <a href="https://console.cloud.google.com/" target="_blank" rel="noopener">Google Cloud Console</a>, create a project → enable <strong>Google Analytics Data API</strong>.</li>
                <li>Create a <strong>Service Account</strong> → Keys → Add key → JSON → download the file.</li>
                <li>In GA4 → Admin → Property access management → Add user → paste service account email → role <strong>Viewer</strong>.</li>
                <li>Upload JSON above and enter numeric <strong>Property ID</strong> from GA4 property settings.</li>
            </ol>
        </details>
    </div>

    <?php if ($lookerUrl !== ''): ?>
    <div class="admin-panel-card" style="margin-bottom: 24px;">
        <div class="admin-card-header">
            <h2><i class="fa-solid fa-chart-area"></i> Looker Studio Dashboard</h2>
        </div>
        <iframe class="ga4-embed" src="<?php echo htmlspecialchars($lookerUrl); ?>" allowfullscreen loading="lazy" title="Looker Studio Analytics"></iframe>
    </div>
    <?php endif; ?>

    <?php if ($propertyId !== '' && ga4CredentialsConfigured()): ?>
        <?php if ($ga4Realtime === null || empty($ga4Realtime['ok'])): ?>
            <div class="admin-panel-card" style="margin-bottom: 24px;">
                <div class="alert alert-error" style="margin:0;">
                    Realtime data unavailable: <?php echo htmlspecialchars($ga4Realtime['error'] ?? 'Unknown error'); ?>
                </div>
            </div>
        <?php else: ?>
            <?php include __DIR__ . '/includes/ga4_realtime_panel.php'; ?>
        <?php endif; ?>
    <?php endif; ?>

    <div class="stats-grid" style="margin-bottom: 24px;">
        <div class="stat-widget">
            <div class="stat-icon" style="background: rgba(13, 148, 136, 0.1); color: #0d9488;"><i class="fa-solid fa-calendar-check"></i></div>
            <div class="stat-info"><h3>CRM Bookings (7d)</h3><p><?php echo $crmBookings7; ?></p></div>
        </div>
        <div class="stat-widget">
            <div class="stat-icon" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;"><i class="fa-solid fa-envelope"></i></div>
            <div class="stat-info"><h3>CRM Inquiries (7d)</h3><p><?php echo $crmInquiries7; ?></p></div>
        </div>
        <div class="stat-widget">
            <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;"><i class="fa-solid fa-star"></i></div>
            <div class="stat-info"><h3>Website Reviews (7d)</h3><p><?php echo $crmReviews7; ?></p></div>
        </div>
    </div>

    <?php if ($propertyId === '' || !ga4CredentialsConfigured()): ?>
        <div class="admin-panel-card">
            <div class="alert alert-error" style="margin:0;">
                <i class="fa-solid fa-circle-info"></i>
                Connect GA4 Property ID + Service Account JSON above to load live visitor analytics.
                On <strong>localhost</strong>, GA4 will have little or no data until the site is live on your domain.
            </div>
        </div>
    <?php elseif ($ga4Data === null || empty($ga4Data['ok'])): ?>
        <div class="admin-panel-card">
            <div class="alert alert-error" style="margin:0;">
                <?php echo htmlspecialchars($ga4Data['error'] ?? 'Could not load GA4 data.'); ?>
                Verify Property ID, service account Viewer access, and that Analytics Data API is enabled.
            </div>
        </div>
    <?php else: ?>
        <?php
        $s7 = $ga4Data['summary7'] ?? [];
        $s30 = $ga4Data['summary30'] ?? [];
        $maxDaily = 1;
        foreach ($ga4Data['daily'] ?? [] as $d) {
            $maxDaily = max($maxDaily, (int) $d['sessions']);
        }
        ?>
        <h2 style="font-size:1.1rem; color:#475569; margin: 0 0 16px;"><i class="fa-solid fa-clock-rotate-left"></i> Historical Reports (cached 15 min)</h2>
        <div class="stats-grid" style="margin-bottom: 24px;">
            <div class="stat-widget">
                <div class="stat-icon" style="background: rgba(13, 148, 136, 0.1); color: #0d9488;"><i class="fa-solid fa-users"></i></div>
                <div class="stat-info"><h3>Active Users (7d)</h3><p><?php echo number_format(ga4MetricValue($s7, 'activeUsers')); ?></p></div>
            </div>
            <div class="stat-widget">
                <div class="stat-icon" style="background: rgba(99, 102, 241, 0.1); color: #6366f1;"><i class="fa-solid fa-door-open"></i></div>
                <div class="stat-info"><h3>Sessions (7d)</h3><p><?php echo number_format(ga4MetricValue($s7, 'sessions')); ?></p></div>
            </div>
            <div class="stat-widget">
                <div class="stat-icon" style="background: rgba(236, 72, 153, 0.1); color: #ec4899;"><i class="fa-solid fa-file-lines"></i></div>
                <div class="stat-info"><h3>Page Views (7d)</h3><p><?php echo number_format(ga4MetricValue($s7, 'screenPageViews')); ?></p></div>
            </div>
            <div class="stat-widget">
                <div class="stat-icon" style="background: rgba(34, 197, 94, 0.1); color: #22c55e;"><i class="fa-solid fa-user-plus"></i></div>
                <div class="stat-info"><h3>New Users (7d)</h3><p><?php echo number_format(ga4MetricValue($s7, 'newUsers')); ?></p></div>
            </div>
        </div>

        <div class="admin-form-row" style="margin-bottom: 24px;">
            <div class="admin-panel-card" style="flex:1;">
                <div class="admin-card-header"><h2>Last 14 Days — Sessions</h2></div>
                <div class="ga4-chart-bars">
                    <?php foreach ($ga4Data['daily'] ?? [] as $day): ?>
                        <?php $h = max(4, (int) round(((int) $day['sessions'] / $maxDaily) * 120)); ?>
                        <div class="ga4-bar-col" title="<?php echo htmlspecialchars($day['date'] . ': ' . $day['sessions'] . ' sessions'); ?>">
                            <div class="ga4-bar" style="height: <?php echo $h; ?>px;"></div>
                            <div class="ga4-bar-label"><?php echo htmlspecialchars(substr($day['date'], 5)); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="admin-panel-card" style="flex:1;">
                <div class="admin-card-header"><h2>30-Day Summary</h2></div>
                <table class="ga4-table">
                    <tr><th>Metric</th><th>Value</th></tr>
                    <tr><td>Active Users</td><td><?php echo number_format(ga4MetricValue($s30, 'activeUsers')); ?></td></tr>
                    <tr><td>Sessions</td><td><?php echo number_format(ga4MetricValue($s30, 'sessions')); ?></td></tr>
                    <tr><td>Page Views</td><td><?php echo number_format(ga4MetricValue($s30, 'screenPageViews')); ?></td></tr>
                </table>
                <p style="font-size:0.8rem; color:#94a3b8; margin-top:12px;">Cached <?php echo date('d M Y H:i', (int) ($ga4Data['cached_at'] ?? time())); ?> · refreshes every 15 min</p>
            </div>
        </div>

        <div class="admin-form-row" style="margin-bottom: 24px;">
            <div class="admin-panel-card" style="flex:1;">
                <div class="admin-card-header"><h2>Top Pages (7d)</h2></div>
                <table class="ga4-table">
                    <tr><th>Page</th><th>Views</th></tr>
                    <?php foreach ($ga4Data['top_pages'] ?? [] as $row): ?>
                        <tr><td><code><?php echo htmlspecialchars($row['label']); ?></code></td><td><?php echo number_format($row['value']); ?></td></tr>
                    <?php endforeach; ?>
                </table>
            </div>
            <div class="admin-panel-card" style="flex:1;">
                <div class="admin-card-header"><h2>Traffic Channels (7d)</h2></div>
                <table class="ga4-table">
                    <tr><th>Channel</th><th>Sessions</th></tr>
                    <?php foreach ($ga4Data['channels'] ?? [] as $row): ?>
                        <tr><td><?php echo htmlspecialchars($row['label']); ?></td><td><?php echo number_format($row['value']); ?></td></tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>

        <div class="admin-panel-card">
            <div class="admin-card-header"><h2>Conversion Events (30d)</h2><p style="font-size:0.85rem;color:#64748b;">Form submissions tracked on your website</p></div>
            <table class="ga4-table">
                <tr><th>Event</th><th>Count</th><th>Meaning</th></tr>
                <?php
                $eventLabels = [
                    'booking_submit' => 'Home collection booking',
                    'inquiry_submit' => 'Contact form',
                    'review_submit' => 'Patient review',
                    'page_view' => 'Page views (sample)',
                ];
                foreach ($ga4Data['events'] ?? [] as $row):
                ?>
                    <tr>
                        <td><code><?php echo htmlspecialchars($row['label']); ?></code></td>
                        <td><?php echo number_format($row['value']); ?></td>
                        <td><?php echo htmlspecialchars($eventLabels[$row['label']] ?? '—'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php if ($propertyId !== '' && ga4CredentialsConfigured()): ?>
<script src="js/ga4-realtime.js"></script>
<?php endif; ?>
</body>
</html>
