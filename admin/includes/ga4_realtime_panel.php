<?php
/** @var array|null $ga4Realtime */
$ga4Realtime = $ga4Realtime ?? null;
$rt = is_array($ga4Realtime) && !empty($ga4Realtime['ok']) ? $ga4Realtime : null;
?>
<div class="admin-panel-card ga4-realtime-card" id="ga4-realtime-panel" style="margin-bottom: 24px; border: 2px solid #14b8a6;">
    <div class="admin-card-header" style="align-items: center;">
        <div>
            <h2 style="display:flex; align-items:center; gap:10px; margin:0;">
                <span class="ga4-rt-pulse"></span>
                Realtime — Live from Google Analytics 4
            </h2>
            <p style="font-size:0.85rem; color:#64748b; margin:6px 0 0;">Users on your site in the last 30 minutes · auto-refreshes every 30 seconds</p>
        </div>
        <span class="ga4-rt-badge" id="ga4-rt-status">LIVE</span>
    </div>

    <p id="ga4-rt-error" class="alert alert-error" style="display:none; margin-bottom:16px;"></p>

    <div class="stats-grid" style="margin-bottom: 20px;">
        <div class="stat-widget" style="border:1px solid #99f6e4;">
            <div class="stat-icon" style="background: rgba(20, 184, 166, 0.15); color: #0d9488;"><i class="fa-solid fa-signal"></i></div>
            <div class="stat-info">
                <h3>Active Users Now</h3>
                <p id="ga4-rt-active-users" style="font-size:2rem;"><?php echo $rt ? number_format((int) $rt['active_users']) : '—'; ?></p>
            </div>
        </div>
        <div class="stat-widget" style="border:1px solid #e2e8f0;">
            <div class="stat-icon" style="background: rgba(99, 102, 241, 0.1); color: #6366f1;"><i class="fa-solid fa-bolt"></i></div>
            <div class="stat-info">
                <h3>Events (30 min)</h3>
                <p id="ga4-rt-event-count"><?php echo $rt ? number_format((int) $rt['event_count']) : '—'; ?></p>
            </div>
        </div>
    </div>

    <p id="ga4-rt-updated" style="font-size:0.8rem; color:#94a3b8; margin:0 0 16px;">
        <?php if ($rt): ?>Updated <?php echo date('H:i:s', (int) $rt['fetched_at']); ?><?php endif; ?>
    </p>

    <div class="admin-form-row">
        <div class="admin-panel-card" style="flex:1; box-shadow:none; margin:0;">
            <div class="admin-card-header"><h2>Active Pages</h2></div>
            <table class="ga4-table">
                <thead><tr><th>Page</th><th>Users</th></tr></thead>
                <tbody id="ga4-rt-pages">
                    <?php if ($rt && !empty($rt['page_paths'])): ?>
                        <?php foreach ($rt['page_paths'] as $row): ?>
                            <tr><td><code><?php echo htmlspecialchars($row['label']); ?></code></td><td><?php echo number_format($row['value']); ?></td></tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="2" style="color:#94a3b8;">No active pages right now</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="admin-panel-card" style="flex:1; box-shadow:none; margin:0;">
            <div class="admin-card-header"><h2>Traffic Sources</h2></div>
            <table class="ga4-table">
                <thead><tr><th>Source</th><th>Users</th></tr></thead>
                <tbody id="ga4-rt-sources">
                    <?php if ($rt && !empty($rt['sources'])): ?>
                        <?php foreach ($rt['sources'] as $row): ?>
                            <tr><td><?php echo htmlspecialchars($row['label']); ?></td><td><?php echo number_format($row['value']); ?></td></tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="2" style="color:#94a3b8;">No traffic sources yet</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="admin-form-row" style="margin-top: 16px;">
        <div class="admin-panel-card" style="flex:1; box-shadow:none; margin:0;">
            <div class="admin-card-header"><h2>Countries</h2></div>
            <table class="ga4-table">
                <thead><tr><th>Country</th><th>Users</th></tr></thead>
                <tbody id="ga4-rt-countries">
                    <?php if ($rt && !empty($rt['countries'])): ?>
                        <?php foreach ($rt['countries'] as $row): ?>
                            <tr><td><?php echo htmlspecialchars($row['label']); ?></td><td><?php echo number_format($row['value']); ?></td></tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="2" style="color:#94a3b8;">No country data yet</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="admin-panel-card" style="flex:1; box-shadow:none; margin:0;">
            <div class="admin-card-header"><h2>Devices</h2></div>
            <table class="ga4-table">
                <thead><tr><th>Device</th><th>Users</th></tr></thead>
                <tbody id="ga4-rt-devices">
                    <?php if ($rt && !empty($rt['devices'])): ?>
                        <?php foreach ($rt['devices'] as $row): ?>
                            <tr><td><?php echo htmlspecialchars($row['label']); ?></td><td><?php echo number_format($row['value']); ?></td></tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="2" style="color:#94a3b8;">No device data yet</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="admin-panel-card" style="margin-top: 16px; box-shadow:none;">
        <div class="admin-card-header"><h2>Recent Events (30 min)</h2></div>
        <table class="ga4-table">
            <thead><tr><th>Event</th><th>Count</th></tr></thead>
            <tbody id="ga4-rt-events">
                <?php if ($rt && !empty($rt['events'])): ?>
                    <?php foreach ($rt['events'] as $row): ?>
                        <tr><td><code><?php echo htmlspecialchars($row['label']); ?></code></td><td><?php echo number_format($row['value']); ?></td></tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="2" style="color:#94a3b8;">No events in the last 30 minutes</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
