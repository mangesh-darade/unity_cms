                <!-- Tab: Website Footer -->
                <div id="tab-footer" class="cms-tab-content">
                    <div class="admin-panel-card">
                        <div class="admin-card-header">
                            <h2>Website Footer</h2>
                            <p style="font-size:0.85rem; color:#64748b;">Footer columns, contact details, working hours, badges, and legal links. Quick links pull from the active navigation menu.</p>
                        </div>
                        <form action="cms.php#tab-footer" method="POST">
                            <?php echo csrfField(); ?>

                            <h3 style="font-size:1.05rem; color:var(--primary); margin-bottom:12px;">Brand Column</h3>
                            <div class="form-group">
                                <label class="form-label">About Text</label>
                                <textarea name="footer_about" class="form-control" rows="3"><?php echo htmlspecialchars($cms['footer_about'] ?? ''); ?></textarea>
                            </div>
                            <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
                                <input type="checkbox" name="footer_show_badges" value="1" <?php echo ($cms['footer_show_badges'] ?? '1') === '1' ? 'checked' : ''; ?> style="width:20px;height:20px;">
                                <label style="margin:0;">Show accreditation badges</label>
                            </div>
                            <div class="admin-form-row">
                                <div class="form-group"><label class="form-label">Badge 1</label><input type="text" name="footer_badge_1" class="form-control" value="<?php echo htmlspecialchars($cms['footer_badge_1'] ?? ''); ?>"></div>
                                <div class="form-group"><label class="form-label">Badge 2</label><input type="text" name="footer_badge_2" class="form-control" value="<?php echo htmlspecialchars($cms['footer_badge_2'] ?? ''); ?>"></div>
                            </div>
                            <div class="form-group"><label class="form-label">Badge 3 (optional)</label><input type="text" name="footer_badge_3" class="form-control" value="<?php echo htmlspecialchars($cms['footer_badge_3'] ?? ''); ?>"></div>

                            <h3 style="font-size:1.05rem; color:var(--primary); margin:24px 0 12px;">Column Titles</h3>
                            <div class="admin-form-row">
                                <div class="form-group"><label class="form-label">Quick Links Title</label><input type="text" name="footer_col_links_title" class="form-control" value="<?php echo htmlspecialchars($cms['footer_col_links_title'] ?? 'Quick Links'); ?>"></div>
                                <div class="form-group"><label class="form-label">Contact Title</label><input type="text" name="footer_col_contact_title" class="form-control" value="<?php echo htmlspecialchars($cms['footer_col_contact_title'] ?? 'Contact Us'); ?>"></div>
                            </div>
                            <div class="form-group"><label class="form-label">Working Hours Title</label><input type="text" name="footer_col_hours_title" class="form-control" value="<?php echo htmlspecialchars($cms['footer_col_hours_title'] ?? 'Working Hours'); ?>"></div>

                            <h3 style="font-size:1.05rem; color:var(--primary); margin:24px 0 12px;">Contact &amp; Hours</h3>
                            <div class="form-group"><label class="form-label">Address</label><textarea name="support_address" class="form-control" rows="2"><?php echo htmlspecialchars($cms['support_address'] ?? ''); ?></textarea></div>
                            <div class="admin-form-row">
                                <div class="form-group"><label class="form-label">Weekday Hours Label</label><input type="text" name="footer_weekday_label" class="form-control" value="<?php echo htmlspecialchars($cms['footer_weekday_label'] ?? 'Mon - Sat:'); ?>"></div>
                                <div class="form-group"><label class="form-label">Weekday Hours Value</label><input type="text" name="working_hours_weekday" class="form-control" value="<?php echo htmlspecialchars($cms['working_hours_weekday'] ?? ''); ?>"></div>
                            </div>
                            <div class="admin-form-row">
                                <div class="form-group"><label class="form-label">Sunday Label</label><input type="text" name="footer_sunday_label" class="form-control" value="<?php echo htmlspecialchars($cms['footer_sunday_label'] ?? 'Sunday:'); ?>"></div>
                                <div class="form-group"><label class="form-label">Sunday Hours Value</label><input type="text" name="working_hours_sunday" class="form-control" value="<?php echo htmlspecialchars($cms['working_hours_sunday'] ?? ''); ?>"></div>
                            </div>
                            <div class="form-group"><label class="form-label">Home Collection Note</label><input type="text" name="footer_home_collection_note" class="form-control" value="<?php echo htmlspecialchars($cms['footer_home_collection_note'] ?? ''); ?>"></div>
                            <div class="admin-form-row">
                                <div class="form-group"><label class="form-label">WhatsApp Link Label</label><input type="text" name="footer_whatsapp_label" class="form-control" value="<?php echo htmlspecialchars($cms['footer_whatsapp_label'] ?? 'Chat with Us'); ?>"></div>
                                <div class="form-group" style="display:flex;align-items:center;gap:10px;padding-top:28px;">
                                    <input type="checkbox" name="footer_show_whatsapp" value="1" <?php echo ($cms['footer_show_whatsapp'] ?? '1') === '1' ? 'checked' : ''; ?> style="width:20px;height:20px;">
                                    <label style="margin:0;">Show WhatsApp in footer</label>
                                </div>
                            </div>

                            <h3 style="font-size:1.05rem; color:var(--primary); margin:24px 0 12px;">Bottom Bar</h3>
                            <div class="form-group"><label class="form-label">Copyright Text</label><input type="text" name="footer_copyright" class="form-control" value="<?php echo htmlspecialchars($cms['footer_copyright'] ?? ''); ?>"></div>
                            <div class="admin-form-row">
                                <div class="form-group"><label class="form-label">Privacy Link Label</label><input type="text" name="footer_privacy_label" class="form-control" value="<?php echo htmlspecialchars($cms['footer_privacy_label'] ?? 'Privacy Policy'); ?>"></div>
                                <div class="form-group"><label class="form-label">Terms Link Label</label><input type="text" name="footer_terms_label" class="form-control" value="<?php echo htmlspecialchars($cms['footer_terms_label'] ?? 'Terms of Service'); ?>"></div>
                            </div>
                            <p style="font-size:0.85rem;color:#64748b;">Privacy/Terms URLs come from <a href="cms.php#tab-pages">Site Pages</a> (privacy / terms filenames).</p>

                            <button type="submit" name="update_footer" class="btn btn-teal w-full" style="margin-top:20px;"><i class="fa-solid fa-shoe-prints"></i> Save Footer Settings</button>
                        </form>
                    </div>
                </div>
