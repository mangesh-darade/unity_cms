                <!-- Tab: Digital Marketing -->
                <div id="tab-marketing" class="cms-tab-content">
                    <div class="admin-panel-card">
                        <div class="admin-card-header">
                            <h2>Digital Marketing &amp; SEO</h2>
                            <p style="font-size:0.85rem; color:#64748b;">Manage search engine optimization, social profiles, analytics pixels, schema markup, and conversion elements.</p>
                        </div>
                        <form action="cms.php#tab-marketing" method="POST" enctype="multipart/form-data">
                            <?php echo csrfField(); ?>

                            <h3 style="font-size:1.05rem; color:var(--primary); margin:20px 0 12px;"><i class="fa-solid fa-magnifying-glass"></i> Default SEO</h3>
                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label class="form-label">Homepage SEO Title</label>
                                    <input type="text" name="seo_home_title" class="form-control" value="<?php echo htmlspecialchars($cms['seo_home_title'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Default Title Suffix</label>
                                    <input type="text" name="seo_default_title_suffix" class="form-control" value="<?php echo htmlspecialchars($cms['seo_default_title_suffix'] ?? ''); ?>" placeholder="Accurate Diagnostics & Blood Test Center">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Homepage Meta Description</label>
                                <textarea name="seo_home_description" class="form-control" rows="2"><?php echo htmlspecialchars($cms['seo_home_description'] ?? ''); ?></textarea>
                            </div>
                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label class="form-label">Default Meta Description</label>
                                    <textarea name="seo_default_description" class="form-control" rows="2"><?php echo htmlspecialchars($cms['seo_default_description'] ?? ''); ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Default Meta Keywords</label>
                                    <input type="text" name="seo_default_keywords" class="form-control" value="<?php echo htmlspecialchars($cms['seo_default_keywords'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label class="form-label">Page Title Format</label>
                                    <select name="seo_title_format" class="form-control">
                                        <option value="{page}" <?php echo ($cms['seo_title_format'] ?? '{page}') === '{page}' ? 'selected' : ''; ?>>Page title only</option>
                                        <option value="{page} - {site}" <?php echo ($cms['seo_title_format'] ?? '') === '{page} - {site}' ? 'selected' : ''; ?>>Page title - Site name</option>
                                    </select>
                                </div>
                                <div class="form-group" style="display:flex;align-items:center;gap:10px;padding-top:28px;">
                                    <input type="checkbox" name="seo_robots_index" value="1" id="seo_robots_index" <?php echo ($cms['seo_robots_index'] ?? '1') === '1' ? 'checked' : ''; ?> style="width:20px;height:20px;">
                                    <label for="seo_robots_index" style="margin:0;">Allow search engines to index site</label>
                                </div>
                            </div>

                            <h3 style="font-size:1.05rem; color:var(--primary); margin:24px 0 12px;"><i class="fa-solid fa-share-nodes"></i> Social &amp; Open Graph</h3>
                            <div class="admin-form-row align-center">
                                <div class="form-group">
                                    <label class="form-label">Default OG / Share Image</label>
                                    <input type="file" name="og_image_file" class="form-control" accept="image/*" style="padding:8px;">
                                    <small style="color:#64748b;">Recommended 1200×630px. Used for Facebook, WhatsApp, Twitter cards.</small>
                                </div>
                                <div>
                                    <?php if (!empty($cms['og_image'])): ?>
                                        <img src="../<?php echo htmlspecialchars($cms['og_image']); ?>" alt="OG preview" style="max-width:180px;border-radius:6px;border:1px solid #e2e8f0;">
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="admin-form-row">
                                <div class="form-group"><label class="form-label">OG Site Name</label><input type="text" name="og_site_name" class="form-control" value="<?php echo htmlspecialchars($cms['og_site_name'] ?? ''); ?>"></div>
                                <div class="form-group"><label class="form-label">Twitter Card Type</label><select name="twitter_card" class="form-control"><option value="summary_large_image" <?php echo ($cms['twitter_card'] ?? '') === 'summary_large_image' ? 'selected' : ''; ?>>Large image</option><option value="summary" <?php echo ($cms['twitter_card'] ?? '') === 'summary' ? 'selected' : ''; ?>>Summary</option></select></div>
                            </div>
                            <div class="form-group"><label class="form-label">Twitter @handle (optional)</label><input type="text" name="twitter_site" class="form-control" value="<?php echo htmlspecialchars($cms['twitter_site'] ?? ''); ?>" placeholder="@unityclinicallab"></div>
                            <div class="admin-form-row">
                                <div class="form-group"><label class="form-label">Facebook Page URL</label><input type="url" name="social_facebook" class="form-control" value="<?php echo htmlspecialchars($cms['social_facebook'] ?? ''); ?>"></div>
                                <div class="form-group"><label class="form-label">Instagram URL</label><input type="url" name="social_instagram" class="form-control" value="<?php echo htmlspecialchars($cms['social_instagram'] ?? ''); ?>"></div>
                            </div>
                            <div class="admin-form-row">
                                <div class="form-group"><label class="form-label">Twitter / X URL</label><input type="url" name="social_twitter" class="form-control" value="<?php echo htmlspecialchars($cms['social_twitter'] ?? ''); ?>"></div>
                                <div class="form-group"><label class="form-label">YouTube URL</label><input type="url" name="social_youtube" class="form-control" value="<?php echo htmlspecialchars($cms['social_youtube'] ?? ''); ?>"></div>
                            </div>
                            <div class="form-group"><label class="form-label">LinkedIn URL</label><input type="url" name="social_linkedin" class="form-control" value="<?php echo htmlspecialchars($cms['social_linkedin'] ?? ''); ?>"></div>

                            <h3 style="font-size:1.05rem; color:var(--primary); margin:24px 0 12px;"><i class="fa-solid fa-chart-line"></i> Analytics &amp; Pixels</h3>
                            <div class="admin-form-row">
                                <div class="form-group"><label class="form-label">Google Analytics 4 Measurement ID</label><input type="text" name="google_analytics_id" class="form-control" value="<?php echo htmlspecialchars($cms['google_analytics_id'] ?? ''); ?>" placeholder="G-XXXXXXXXXX"></div>
                                <div class="form-group"><label class="form-label">Google Tag Manager ID</label><input type="text" name="google_tag_manager_id" class="form-control" value="<?php echo htmlspecialchars($cms['google_tag_manager_id'] ?? ''); ?>" placeholder="GTM-XXXXXXX"></div>
                            </div>
                            <div class="admin-form-row">
                                <div class="form-group"><label class="form-label">Meta (Facebook) Pixel ID</label><input type="text" name="facebook_pixel_id" class="form-control" value="<?php echo htmlspecialchars($cms['facebook_pixel_id'] ?? ''); ?>"></div>
                                <div class="form-group"><label class="form-label">Google Search Console Verification</label><input type="text" name="google_site_verification" class="form-control" value="<?php echo htmlspecialchars($cms['google_site_verification'] ?? ''); ?>"></div>
                            </div>
                            <div class="form-group"><label class="form-label">Bing Webmaster Verification</label><input type="text" name="bing_site_verification" class="form-control" value="<?php echo htmlspecialchars($cms['bing_site_verification'] ?? ''); ?>"></div>
                            <p style="font-size:0.85rem; color:#64748b; margin-top:8px;">
                                <i class="fa-solid fa-chart-pie"></i>
                                View <strong>realtime</strong> GA4 visitor data in admin: <a href="analytics.php"><strong>Admin → GA4 Analytics</strong></a>
                                (auto-refreshes every 30 seconds; requires Property ID + service account JSON on that page).
                            </p>

                            <h3 style="font-size:1.05rem; color:var(--primary); margin:24px 0 12px;"><i class="fa-solid fa-map-location-dot"></i> Local Business Schema (JSON-LD)</h3>
                            <div class="admin-form-row">
                                <div class="form-group"><label class="form-label">Alternate Business Name</label><input type="text" name="schema_alternate_name" class="form-control" value="<?php echo htmlspecialchars($cms['schema_alternate_name'] ?? ''); ?>"></div>
                                <div class="form-group"><label class="form-label">Price Range</label><input type="text" name="schema_price_range" class="form-control" value="<?php echo htmlspecialchars($cms['schema_price_range'] ?? '$$'); ?>"></div>
                            </div>
                            <div class="form-group"><label class="form-label">Street Address</label><input type="text" name="schema_street" class="form-control" value="<?php echo htmlspecialchars($cms['schema_street'] ?? ''); ?>"></div>
                            <div class="admin-form-row">
                                <div class="form-group"><label class="form-label">City</label><input type="text" name="schema_city" class="form-control" value="<?php echo htmlspecialchars($cms['schema_city'] ?? ''); ?>"></div>
                                <div class="form-group"><label class="form-label">State</label><input type="text" name="schema_state" class="form-control" value="<?php echo htmlspecialchars($cms['schema_state'] ?? ''); ?>"></div>
                            </div>
                            <div class="admin-form-row">
                                <div class="form-group"><label class="form-label">Postal Code</label><input type="text" name="schema_postal" class="form-control" value="<?php echo htmlspecialchars($cms['schema_postal'] ?? ''); ?>"></div>
                                <div class="form-group"><label class="form-label">Country Code</label><input type="text" name="schema_country" class="form-control" value="<?php echo htmlspecialchars($cms['schema_country'] ?? 'IN'); ?>"></div>
                            </div>
                            <div class="admin-form-row">
                                <div class="form-group"><label class="form-label">Latitude</label><input type="text" name="schema_lat" class="form-control" value="<?php echo htmlspecialchars($cms['schema_lat'] ?? ''); ?>"></div>
                                <div class="form-group"><label class="form-label">Longitude</label><input type="text" name="schema_lng" class="form-control" value="<?php echo htmlspecialchars($cms['schema_lng'] ?? ''); ?>"></div>
                            </div>
                            <div class="admin-form-row">
                                <div class="form-group"><label class="form-label">Weekday Opens (24h)</label><input type="text" name="schema_opens_weekday" class="form-control" value="<?php echo htmlspecialchars($cms['schema_opens_weekday'] ?? '07:00'); ?>"></div>
                                <div class="form-group"><label class="form-label">Weekday Closes</label><input type="text" name="schema_closes_weekday" class="form-control" value="<?php echo htmlspecialchars($cms['schema_closes_weekday'] ?? '21:00'); ?>"></div>
                            </div>
                            <div class="admin-form-row">
                                <div class="form-group"><label class="form-label">Sunday Opens</label><input type="text" name="schema_opens_sunday" class="form-control" value="<?php echo htmlspecialchars($cms['schema_opens_sunday'] ?? '07:00'); ?>"></div>
                                <div class="form-group"><label class="form-label">Sunday Closes</label><input type="text" name="schema_closes_sunday" class="form-control" value="<?php echo htmlspecialchars($cms['schema_closes_sunday'] ?? '14:00'); ?>"></div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Google Maps Embed URL</label>
                                <input type="url" name="maps_embed_url" class="form-control" value="<?php echo htmlspecialchars($cms['maps_embed_url'] ?? ''); ?>" placeholder="https://www.google.com/maps/embed?pb=...">
                                <small style="color:#64748b;">Also editable in <a href="cms.php#tab-general">General Settings → Google Maps</a> with directions link &amp; lat/lng.</small>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Geo Region Code</label>
                                <input type="text" name="geo_region_code" class="form-control" value="<?php echo htmlspecialchars($cms['geo_region_code'] ?? 'IN-MH'); ?>" placeholder="IN-MH">
                            </div>

                            <h3 style="font-size:1.05rem; color:var(--primary); margin:24px 0 12px;"><i class="fa-solid fa-rocket"></i> Conversion &amp; CTA</h3>
                            <div class="admin-form-row">
                                <div class="form-group"><label class="form-label">Offer Banner Link URL</label><input type="text" name="top_offer_link" class="form-control" value="<?php echo htmlspecialchars($cms['top_offer_link'] ?? 'packages.php'); ?>"></div>
                                <div class="form-group"><label class="form-label">Offer Banner Button Text</label><input type="text" name="top_offer_link_text" class="form-control" value="<?php echo htmlspecialchars($cms['top_offer_link_text'] ?? 'View Offers'); ?>"></div>
                            </div>
                            <div class="form-group"><label class="form-label">Top Bar Location Text</label><input type="text" name="top_bar_location" class="form-control" value="<?php echo htmlspecialchars($cms['top_bar_location'] ?? ''); ?>"></div>
                            <div class="admin-form-row">
                                <div class="form-group"><label class="form-label">Hero: Book Button Text</label><input type="text" name="hero_btn_book_text" class="form-control" value="<?php echo htmlspecialchars($cms['hero_btn_book_text'] ?? ''); ?>"></div>
                                <div class="form-group"><label class="form-label">Hero: Book Button URL</label><input type="text" name="hero_btn_book_url" class="form-control" value="<?php echo htmlspecialchars($cms['hero_btn_book_url'] ?? ''); ?>"></div>
                            </div>
                            <div class="admin-form-row">
                                <div class="form-group"><label class="form-label">Hero: Download Button Text</label><input type="text" name="hero_btn_download_text" class="form-control" value="<?php echo htmlspecialchars($cms['hero_btn_download_text'] ?? ''); ?>"></div>
                                <div class="form-group"><label class="form-label">Hero: Download Button URL</label><input type="text" name="hero_btn_download_url" class="form-control" value="<?php echo htmlspecialchars($cms['hero_btn_download_url'] ?? ''); ?>"></div>
                            </div>
                            <div class="admin-form-row">
                                <div class="form-group"><label class="form-label">Hero: Call Button Text</label><input type="text" name="hero_btn_call_text" class="form-control" value="<?php echo htmlspecialchars($cms['hero_btn_call_text'] ?? ''); ?>"></div>
                                <div class="form-group"><label class="form-label">WhatsApp Pre-filled Message</label><input type="text" name="hero_whatsapp_message" class="form-control" value="<?php echo htmlspecialchars($cms['hero_whatsapp_message'] ?? ''); ?>"></div>
                            </div>
                            <div class="form-group" style="display:flex;align-items:center;gap:10px;">
                                <input type="checkbox" name="floating_whatsapp_enabled" value="1" id="floating_whatsapp_enabled" <?php echo ($cms['floating_whatsapp_enabled'] ?? '0') === '1' ? 'checked' : ''; ?> style="width:20px;height:20px;">
                                <label for="floating_whatsapp_enabled" style="margin:0;">Show floating WhatsApp button on all pages</label>
                            </div>
                            <div class="admin-form-row">
                                <div class="form-group"><label class="form-label">Footer Badge 1</label><input type="text" name="footer_badge_1" class="form-control" value="<?php echo htmlspecialchars($cms['footer_badge_1'] ?? ''); ?>"></div>
                                <div class="form-group"><label class="form-label">Footer Badge 2</label><input type="text" name="footer_badge_2" class="form-control" value="<?php echo htmlspecialchars($cms['footer_badge_2'] ?? ''); ?>"></div>
                            </div>
                            <div class="form-group"><label class="form-label">Footer Home Collection Note</label><input type="text" name="footer_home_collection_note" class="form-control" value="<?php echo htmlspecialchars($cms['footer_home_collection_note'] ?? ''); ?>"></div>

                            <h3 style="font-size:1.05rem; color:var(--primary); margin:24px 0 12px;"><i class="fa-solid fa-code"></i> Custom Tracking Scripts</h3>
                            <div class="form-group">
                                <label class="form-label">Head Scripts (before &lt;/head&gt;)</label>
                                <textarea name="marketing_head_scripts" class="form-control" rows="4" placeholder="Paste custom tracking snippets, chat widgets, etc."><?php echo htmlspecialchars($cms['marketing_head_scripts'] ?? ''); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Body Scripts (after &lt;body&gt;)</label>
                                <textarea name="marketing_body_scripts" class="form-control" rows="4"><?php echo htmlspecialchars($cms['marketing_body_scripts'] ?? ''); ?></textarea>
                            </div>

                            <p style="font-size:0.85rem; color:#64748b; margin-top:12px;">Sitemap: <a href="../sitemap.php" target="_blank"><?php echo htmlspecialchars(rtrim(BASE_URL, '/') . '/sitemap.php'); ?></a> · RSS: <a href="../feed.php" target="_blank">feed.php</a> · Locations: <a href="../locations.php" target="_blank">locations.php</a> · Per-page SEO under <a href="cms.php#tab-pages">Site Pages</a>.</p>
                            <p style="font-size:0.85rem; color:#64748b;">Tip: Use <strong>GTM OR direct GA4</strong> — if both are set, only GTM loads GA to avoid duplicate tracking. Add conversion tags in GTM for booking_submit, inquiry_submit, review_submit events.</p>

                            <button type="submit" name="update_marketing" class="btn btn-teal w-full" style="margin-top: 20px;">
                                <i class="fa-solid fa-bullhorn"></i> Save Digital Marketing Settings
                            </button>
                        </form>
                    </div>
                </div>
