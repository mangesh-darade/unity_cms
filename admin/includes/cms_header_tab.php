                <!-- Tab: Website Header -->
                <div id="tab-header" class="cms-tab-content">
                    <div class="admin-panel-card">
                        <div class="admin-card-header">
                            <h2>Website Header</h2>
                            <p style="font-size:0.85rem; color:#64748b;">Logo, top info bar, offer banner, and header visibility. Navigation links are managed under <a href="cms.php#tab-menu">Navigation Menu</a>.</p>
                        </div>
                        <form action="cms.php#tab-header" method="POST" enctype="multipart/form-data">
                            <?php echo csrfField(); ?>

                            <h3 style="font-size:1.05rem; color:var(--primary); margin-bottom:12px;">Logo</h3>
                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label class="form-label">Logo Brand Text</label>
                                    <input type="text" name="logo_text" class="form-control" value="<?php echo htmlspecialchars($cms['logo_text'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Logo Link URL</label>
                                    <input type="text" name="header_logo_url" class="form-control" value="<?php echo htmlspecialchars($cms['header_logo_url'] ?? 'index.php'); ?>">
                                </div>
                            </div>
                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label class="form-label">Logo Display Type</label>
                                    <select name="logo_type" class="form-control">
                                        <option value="text" <?php echo ($cms['logo_type'] ?? 'text') === 'text' ? 'selected' : ''; ?>>Icon + Text</option>
                                        <option value="image" <?php echo ($cms['logo_type'] ?? '') === 'image' ? 'selected' : ''; ?>>Image Logo</option>
                                        <option value="icon" <?php echo ($cms['logo_type'] ?? '') === 'icon' ? 'selected' : ''; ?>>Medical Icon + Text</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Logo Icon Class</label>
                                    <input type="text" name="logo_icon" class="form-control" value="<?php echo htmlspecialchars($cms['logo_icon'] ?? 'fa-solid fa-flask'); ?>">
                                </div>
                            </div>
                            <div class="admin-form-row align-center">
                                <div class="form-group">
                                    <label class="form-label">Upload Logo Image</label>
                                    <input type="file" name="logo_image_file" class="form-control" accept="image/*" style="padding:8px;">
                                    <small style="color:#64748b;">Upload 400×200 px PNG/SVG for best quality. Header display is capped for a clean nav bar (max ~280×80 px).</small>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Logo Display Width (px)</label>
                                    <input type="number" name="header_logo_width" class="form-control" min="40" max="400" value="<?php echo (int) ($cms['header_logo_width'] ?? 240); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Logo Display Height (px)</label>
                                    <input type="number" name="header_logo_height" class="form-control" min="30" max="200" value="<?php echo (int) ($cms['header_logo_height'] ?? 72); ?>">
                                </div>
                            </div>
                            <div class="admin-form-row align-center">
                                <div>
                                    <?php if (!empty($cms['logo_image'])): ?>
                                        <label class="form-label">Current Logo Preview</label>
                                        <img src="../<?php echo htmlspecialchars($cms['logo_image']); ?>" style="width:<?php echo (int) ($cms['header_logo_width'] ?? 400); ?>px;height:<?php echo (int) ($cms['header_logo_height'] ?? 200); ?>px;object-fit:contain;border:1px solid #e2e8f0;border-radius:8px;background:#fff;">
                                    <?php endif; ?>
                                </div>
                            </div>

                            <h3 style="font-size:1.05rem; color:var(--primary); margin:24px 0 12px;">Top Info Bar</h3>
                            <div class="admin-form-row">
                                <div class="form-group"><label class="form-label">Phone</label><input type="text" name="support_phone" class="form-control" value="<?php echo htmlspecialchars($cms['support_phone'] ?? ''); ?>"></div>
                                <div class="form-group"><label class="form-label">Email</label><input type="email" name="support_email" class="form-control" value="<?php echo htmlspecialchars($cms['support_email'] ?? ''); ?>"></div>
                            </div>
                            <div class="admin-form-row">
                                <div class="form-group"><label class="form-label">Location Text</label><input type="text" name="top_bar_location" class="form-control" value="<?php echo htmlspecialchars($cms['top_bar_location'] ?? ''); ?>"></div>
                                <div class="form-group"><label class="form-label">WhatsApp Number</label><input type="text" name="whatsapp_number" class="form-control" value="<?php echo htmlspecialchars($cms['whatsapp_number'] ?? ''); ?>"></div>
                            </div>
                            <div style="display:flex; flex-wrap:wrap; gap:16px; margin-bottom:16px;">
                                <label><input type="checkbox" name="header_show_top_bar" value="1" <?php echo ($cms['header_show_top_bar'] ?? '1') === '1' ? 'checked' : ''; ?>> Show top bar</label>
                                <label><input type="checkbox" name="header_show_phone" value="1" <?php echo ($cms['header_show_phone'] ?? '1') === '1' ? 'checked' : ''; ?>> Show phone</label>
                                <label><input type="checkbox" name="header_show_email" value="1" <?php echo ($cms['header_show_email'] ?? '1') === '1' ? 'checked' : ''; ?>> Show email</label>
                                <label><input type="checkbox" name="header_show_location" value="1" <?php echo ($cms['header_show_location'] ?? '1') === '1' ? 'checked' : ''; ?>> Show location</label>
                                <label><input type="checkbox" name="header_show_social" value="1" <?php echo ($cms['header_show_social'] ?? '1') === '1' ? 'checked' : ''; ?>> Show social icons</label>
                                <label><input type="checkbox" name="header_show_whatsapp_icon" value="1" <?php echo ($cms['header_show_whatsapp_icon'] ?? '1') === '1' ? 'checked' : ''; ?>> Show WhatsApp icon</label>
                            </div>

                            <h3 style="font-size:1.05rem; color:var(--primary); margin:24px 0 12px;">Offer Announcement Banner</h3>
                            <div class="form-group" style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
                                <input type="checkbox" name="top_offer_active" value="1" id="hdr_offer_active" <?php echo ($cms['top_offer_active'] ?? '0') === '1' ? 'checked' : ''; ?> style="width:20px;height:20px;">
                                <label for="hdr_offer_active" style="margin:0;">Enable offer banner</label>
                            </div>
                            <div class="form-group"><label class="form-label">Banner Text</label><input type="text" name="top_offer_text" class="form-control" value="<?php echo htmlspecialchars($cms['top_offer_text'] ?? ''); ?>"></div>
                            <div class="admin-form-row">
                                <div class="form-group"><label class="form-label">Banner Button URL</label><input type="text" name="top_offer_link" class="form-control" value="<?php echo htmlspecialchars($cms['top_offer_link'] ?? ''); ?>"></div>
                                <div class="form-group"><label class="form-label">Banner Button Text</label><input type="text" name="top_offer_link_text" class="form-control" value="<?php echo htmlspecialchars($cms['top_offer_link_text'] ?? ''); ?>"></div>
                            </div>

                            <h3 style="font-size:1.05rem; color:var(--primary); margin:24px 0 12px;">Other</h3>
                            <div class="admin-form-row">
                                <div class="form-group"><label class="form-label">Mobile Menu Toggle Label</label><input type="text" name="header_menu_toggle_label" class="form-control" value="<?php echo htmlspecialchars($cms['header_menu_toggle_label'] ?? 'Toggle menu'); ?>"></div>
                                <div class="form-group"><label class="form-label">Breadcrumb "Home" Label</label><input type="text" name="breadcrumb_home_label" class="form-control" value="<?php echo htmlspecialchars($cms['breadcrumb_home_label'] ?? 'Home'); ?>"></div>
                            </div>
                            <div class="form-group" style="display:flex;align-items:center;gap:10px;">
                                <input type="checkbox" name="floating_whatsapp_enabled" value="1" <?php echo ($cms['floating_whatsapp_enabled'] ?? '0') === '1' ? 'checked' : ''; ?> style="width:20px;height:20px;">
                                <label style="margin:0;">Floating WhatsApp button</label>
                            </div>

                            <button type="submit" name="update_header" class="btn btn-teal w-full" style="margin-top:20px;"><i class="fa-solid fa-bars"></i> Save Header Settings</button>
                        </form>
                    </div>
                </div>
