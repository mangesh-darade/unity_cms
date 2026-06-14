<?php
if (basename($_SERVER['PHP_SELF']) == 'footer.php') {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied');
}

$site_name = cmsSetting($cms, 'site_name');
$logo_text = cmsSetting($cms, 'logo_text');
$support_phone = cmsSetting($cms, 'support_phone');
$support_email = cmsSetting($cms, 'support_email');
$support_address = cmsSetting($cms, 'support_address');
$whatsapp_num = cmsSetting($cms, 'whatsapp_number');
[$logo_main, $logo_span] = cmsLogoParts($logo_text);

$privacy_url = cmsPageFilename($cms_pages ?? [], 'privacy', 'privacy.php');
$terms_url = cmsPageFilename($cms_pages ?? [], 'terms', 'terms.php');
?>
    </main>
    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <h2><?php echo htmlspecialchars($logo_main); ?><span><?php echo htmlspecialchars($logo_span); ?></span></h2>
                    <?php if (cmsSetting($cms, 'footer_about') !== ''): ?>
                    <p><?php echo htmlspecialchars($cms['footer_about']); ?></p>
                    <?php endif; ?>
                    <?php if (($cms['footer_show_badges'] ?? '1') === '1'): ?>
                    <div class="accreditations" style="margin-top: 15px; display: flex; gap: 10px; flex-wrap: wrap;">
                        <?php foreach (['footer_badge_1', 'footer_badge_2', 'footer_badge_3'] as $badgeKey): ?>
                            <?php if ($badge = cmsSetting($cms, $badgeKey)): ?>
                            <span style="font-size: 0.8rem; background-color: rgba(255,255,255,0.1); padding: 4px 10px; border-radius: 4px; border: 1px solid rgba(255,255,255,0.2);"><?php echo htmlspecialchars($badge); ?></span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="footer-col">
                    <h3><?php echo htmlspecialchars(cmsSetting($cms, 'footer_col_links_title', 'Quick Links')); ?></h3>
                    <ul class="footer-links">
                        <?php
                        try {
                            $footer_links = $db->query('SELECT title, url FROM cms_menu WHERE is_active = 1 AND is_cta = 0 ORDER BY sequence ASC')->fetchAll();
                        } catch (PDOException $e) {
                            $footer_links = [];
                        }
                        foreach ($footer_links as $fl): ?>
                            <li><a href="<?php echo htmlspecialchars($fl['url']); ?>"><?php echo htmlspecialchars($fl['title']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="footer-col">
                    <h3><?php echo htmlspecialchars(cmsSetting($cms, 'footer_col_contact_title', 'Contact Us')); ?></h3>
                    <?php if ($support_address !== ''): ?>
                    <div class="footer-contact-item">
                        <span><i class="fa-solid fa-location-dot"></i></span>
                        <p><?php echo nl2br(htmlspecialchars($support_address)); ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if ($support_phone !== ''): ?>
                    <div class="footer-contact-item">
                        <span><i class="fa-solid fa-phone"></i></span>
                        <p><?php echo htmlspecialchars($support_phone); ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if ($support_email !== ''): ?>
                    <div class="footer-contact-item">
                        <span><i class="fa-solid fa-envelope"></i></span>
                        <p><?php echo htmlspecialchars($support_email); ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if (($cms['footer_show_whatsapp'] ?? '1') === '1' && $whatsapp_num !== ''): ?>
                    <div class="footer-contact-item">
                        <span><i class="fa-brands fa-whatsapp"></i></span>
                        <p><a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $whatsapp_num); ?>" target="_blank" rel="noopener noreferrer" style="color: #25d366; font-weight: bold;"><?php echo htmlspecialchars(cmsSetting($cms, 'footer_whatsapp_label', 'Chat with Us')); ?></a></p>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="footer-col">
                    <h3><?php echo htmlspecialchars(cmsSetting($cms, 'footer_col_hours_title', 'Working Hours')); ?></h3>
                    <ul class="footer-links" style="color: #cbd5e1;">
                        <?php if (cmsSetting($cms, 'working_hours_weekday') !== ''): ?>
                        <li style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span><?php echo htmlspecialchars(cmsSetting($cms, 'footer_weekday_label', 'Mon - Sat:')); ?></span>
                            <span><?php echo htmlspecialchars($cms['working_hours_weekday']); ?></span>
                        </li>
                        <?php endif; ?>
                        <?php if (cmsSetting($cms, 'working_hours_sunday') !== ''): ?>
                        <li style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span><?php echo htmlspecialchars(cmsSetting($cms, 'footer_sunday_label', 'Sunday:')); ?></span>
                            <span><?php echo htmlspecialchars($cms['working_hours_sunday']); ?></span>
                        </li>
                        <?php endif; ?>
                        <?php if (cmsSetting($cms, 'footer_home_collection_note') !== ''): ?>
                        <li style="margin-top: 15px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 10px; font-size: 0.85rem; color: #94a3b8;">
                            <i class="fa-solid fa-truck-medical"></i> <?php echo htmlspecialchars($cms['footer_home_collection_note']); ?>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars(cmsSetting($cms, 'footer_copyright', $site_name)); ?></p>
                <div class="footer-bottom-links">
                    <a href="<?php echo htmlspecialchars($privacy_url); ?>"><?php echo htmlspecialchars(cmsSetting($cms, 'footer_privacy_label', 'Privacy Policy')); ?></a>
                    <a href="<?php echo htmlspecialchars($terms_url); ?>"><?php echo htmlspecialchars(cmsSetting($cms, 'footer_terms_label', 'Terms of Service')); ?></a>
                </div>
            </div>
        </div>
    </footer>

    <script src="js/main.js"></script>
</body>
</html>
