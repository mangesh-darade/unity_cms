<?php
/**
 * CMS admin handlers for pages, sections, blocks, and section items.
 * Included from admin/cms.php after requireCsrf block starts - handlers run inside POST with CSRF already validated.
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_sections'])) {
    try {
        $db->beginTransaction();
        $stmt = $db->prepare('UPDATE cms_sections SET sequence = :seq, is_active = :active, section_title = :stitle, section_tag = :tag, section_heading = :heading, section_description = :desc WHERE id = :id');

        foreach ($_POST['sequence'] as $id => $seq) {
            $active = isset($_POST['is_active'][$id]) ? 1 : 0;
            $stmt->execute([
                ':seq' => (int) $seq,
                ':active' => $active,
                ':stitle' => trim($_POST['section_title'][$id] ?? ''),
                ':tag' => trim($_POST['section_tag'][$id] ?? ''),
                ':heading' => trim($_POST['section_heading'][$id] ?? ''),
                ':desc' => trim($_POST['section_description'][$id] ?? ''),
                ':id' => (int) $id,
            ]);
        }
        $db->commit();
        $msg = '<div class="alert alert-success">Homepage sections updated successfully!</div>';
    } catch (PDOException $e) {
        $db->rollBack();
        $msg = '<div class="alert alert-error">Database error: ' . $e->getMessage() . '</div>';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_section'])) {
    $code = preg_replace('/[^a-z0-9_]/', '', strtolower(str_replace('-', '_', trim($_POST['section_code']))));
    $title = trim($_POST['section_title']);
    $type = trim($_POST['section_type'] ?? 'builtin');
    $seq = (int) ($_POST['sequence'] ?? 99);
    $body = trim($_POST['section_body'] ?? '');

    if ($code === '' || $title === '') {
        $msg = '<div class="alert alert-error">Section code and title are required.</div>';
    } elseif (!in_array($type, ['builtin', 'custom_html', 'items_grid'], true)) {
        $msg = '<div class="alert alert-error">Invalid section type.</div>';
    } elseif ($type === 'builtin' && !in_array($code, cmsAvailableSectionTemplates(), true)) {
        $msg = '<div class="alert alert-error">No template file found for this section code. Create includes/sections/' . htmlspecialchars($code) . '.php first or use Custom HTML / Icon Cards type.</div>';
    } else {
        try {
            $stmt = $db->prepare('INSERT INTO cms_sections (section_code, section_title, section_type, section_body, sequence, is_active) VALUES (?, ?, ?, ?, ?, 1)');
            $stmt->execute([$code, $title, $type, $body, $seq]);
            $msg = '<div class="alert alert-success">Homepage section added successfully!</div>';
        } catch (PDOException $e) {
            $msg = '<div class="alert alert-error">Could not add section (code may already exist): ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_section'])) {
    $id = (int) $_POST['section_id'];
    try {
        $stmt = $db->prepare('UPDATE cms_sections SET section_title = ?, section_tag = ?, section_heading = ?, section_description = ?, section_body = ?, section_type = ?, sequence = ?, is_active = ? WHERE id = ?');
        $stmt->execute([
            trim($_POST['section_title']),
            trim($_POST['section_tag'] ?? ''),
            trim($_POST['section_heading'] ?? ''),
            trim($_POST['section_description'] ?? ''),
            trim($_POST['section_body'] ?? ''),
            trim($_POST['section_type'] ?? 'builtin'),
            (int) $_POST['sequence'],
            isset($_POST['is_active']) ? 1 : 0,
            $id,
        ]);
        $msg = '<div class="alert alert-success">Section updated successfully!</div>';
    } catch (PDOException $e) {
        $msg = '<div class="alert alert-error">Database error: ' . $e->getMessage() . '</div>';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_header'])) {
    try {
        $stmt = $db->prepare('UPDATE cms_settings SET value = :value WHERE key = :key');
        $keys = [
            'logo_text', 'logo_type', 'logo_icon', 'support_phone', 'support_email', 'whatsapp_number',
            'top_offer_text', 'top_offer_link', 'top_offer_link_text', 'top_bar_location',
            'header_logo_url', 'header_menu_toggle_label', 'breadcrumb_home_label',
            'header_logo_width', 'header_logo_height',
        ];
        foreach ($keys as $k) {
            if (isset($_POST[$k])) {
                $stmt->execute([':key' => $k, ':value' => trim($_POST[$k])]);
            }
        }
        $checkboxes = [
            'top_offer_active', 'header_show_top_bar', 'header_show_phone', 'header_show_email',
            'header_show_location', 'header_show_social', 'header_show_whatsapp_icon', 'floating_whatsapp_enabled',
        ];
        foreach ($checkboxes as $k) {
            $stmt->execute([':key' => $k, ':value' => isset($_POST[$k]) ? '1' : '0']);
        }
        $logo = handleCMSImageUpload('logo_image_file');
        if ($logo !== '') {
            $stmt->execute([':key' => 'logo_image', ':value' => $logo]);
        }
        $cms_reload = $db->query('SELECT * FROM cms_settings')->fetchAll();
        $cms = [];
        foreach ($cms_reload as $r) {
            $cms[$r['key']] = $r['value'];
        }
        $msg = '<div class="alert alert-success">Header settings saved successfully!</div>';
    } catch (PDOException $e) {
        $msg = '<div class="alert alert-error">Database error: ' . $e->getMessage() . '</div>';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_footer'])) {
    try {
        $stmt = $db->prepare('UPDATE cms_settings SET value = :value WHERE key = :key');
        $keys = [
            'footer_about', 'footer_copyright', 'support_address', 'working_hours_weekday', 'working_hours_sunday',
            'footer_badge_1', 'footer_badge_2', 'footer_badge_3', 'footer_home_collection_note',
            'footer_col_links_title', 'footer_col_contact_title', 'footer_col_hours_title',
            'footer_weekday_label', 'footer_sunday_label', 'footer_whatsapp_label',
            'footer_privacy_label', 'footer_terms_label',
        ];
        foreach ($keys as $k) {
            if (isset($_POST[$k])) {
                $stmt->execute([':key' => $k, ':value' => trim($_POST[$k])]);
            }
        }
        foreach (['footer_show_whatsapp', 'footer_show_badges'] as $k) {
            $stmt->execute([':key' => $k, ':value' => isset($_POST[$k]) ? '1' : '0']);
        }
        $cms_reload = $db->query('SELECT * FROM cms_settings')->fetchAll();
        $cms = [];
        foreach ($cms_reload as $r) {
            $cms[$r['key']] = $r['value'];
        }
        $msg = '<div class="alert alert-success">Footer settings saved successfully!</div>';
    } catch (PDOException $e) {
        $msg = '<div class="alert alert-error">Database error: ' . $e->getMessage() . '</div>';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_page'])) {
    $page_id = (int) $_POST['page_id'];
    try {
        $og_image = handleCMSImageUpload('page_og_image_file');
        $sql = 'UPDATE cms_pages SET page_heading = :heading, breadcrumb_label = :breadcrumb, meta_title = :meta_title, meta_description = :meta_desc, meta_keywords = :keywords, content_tag = :tag, content_title = :title, content_description = :desc, page_body = :body, robots_noindex = :noindex, sitemap_changefreq = :freq, sitemap_priority = :priority, include_in_sitemap = :sitemap, is_active = :active';
        if ($og_image !== '') {
            $sql .= ', og_image = :og_image';
        }
        $sql .= ' WHERE id = :id';
        $stmt = $db->prepare($sql);
        $params = [
            ':heading' => trim($_POST['page_heading']),
            ':breadcrumb' => trim($_POST['breadcrumb_label']),
            ':meta_title' => trim($_POST['meta_title']),
            ':meta_desc' => trim($_POST['meta_description']),
            ':keywords' => trim($_POST['meta_keywords'] ?? ''),
            ':tag' => trim($_POST['content_tag']),
            ':title' => trim($_POST['content_title']),
            ':desc' => trim($_POST['content_description']),
            ':body' => trim($_POST['page_body'] ?? ''),
            ':noindex' => isset($_POST['robots_noindex']) ? 1 : 0,
            ':freq' => trim($_POST['sitemap_changefreq'] ?? 'monthly'),
            ':priority' => trim($_POST['sitemap_priority'] ?? '0.5'),
            ':sitemap' => isset($_POST['include_in_sitemap']) ? 1 : 0,
            ':active' => isset($_POST['is_active']) ? 1 : 0,
            ':id' => $page_id,
        ];
        if ($og_image !== '') {
            $params[':og_image'] = $og_image;
        }
        $stmt->execute($params);
        $msg = '<div class="alert alert-success">Page content updated successfully!</div>';
    } catch (PDOException $e) {
        $msg = '<div class="alert alert-error">Database error: ' . $e->getMessage() . '</div>';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_marketing'])) {
    try {
        $stmt = $db->prepare('UPDATE cms_settings SET value = :value WHERE key = :key');
        $keys = [
            'seo_default_title_suffix', 'seo_default_description', 'seo_default_keywords', 'seo_title_format',
            'seo_home_title', 'seo_home_description',
            'og_site_name', 'twitter_card', 'twitter_site',
            'google_analytics_id', 'google_tag_manager_id', 'facebook_pixel_id',
            'google_site_verification', 'bing_site_verification',
            'schema_alternate_name', 'schema_street', 'schema_city', 'schema_state', 'schema_postal', 'schema_country',
            'schema_lat', 'schema_lng', 'schema_price_range',
            'schema_opens_weekday', 'schema_closes_weekday', 'schema_opens_sunday', 'schema_closes_sunday',
            'social_facebook', 'social_instagram', 'social_twitter', 'social_youtube', 'social_linkedin',
            'top_offer_link', 'top_offer_link_text', 'top_bar_location',
            'footer_badge_1', 'footer_badge_2', 'footer_home_collection_note',
            'hero_btn_book_text', 'hero_btn_book_url', 'hero_btn_download_text', 'hero_btn_download_url',
            'hero_btn_call_text', 'hero_whatsapp_message',
            'marketing_head_scripts', 'marketing_body_scripts',
        ];
        foreach ($keys as $k) {
            if (isset($_POST[$k])) {
                $stmt->execute([':key' => $k, ':value' => trim($_POST[$k])]);
            }
        }
        $checkboxes = ['seo_robots_index', 'floating_whatsapp_enabled'];
        foreach ($checkboxes as $k) {
            $stmt->execute([':key' => $k, ':value' => isset($_POST[$k]) ? '1' : '0']);
        }
        $og = handleCMSImageUpload('og_image_file');
        if ($og !== '') {
            $stmt->execute([':key' => 'og_image', ':value' => $og]);
        }
        $cms_reload = $db->query('SELECT * FROM cms_settings')->fetchAll();
        $cms = [];
        foreach ($cms_reload as $r) {
            $cms[$r['key']] = $r['value'];
        }
        $msg = '<div class="alert alert-success">Digital marketing settings saved successfully!</div>';
    } catch (PDOException $e) {
        $msg = '<div class="alert alert-error">Database error: ' . $e->getMessage() . '</div>';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_section_item'])) {
    $stmt = $db->prepare('INSERT INTO cms_section_items (section_code, title, subtitle, description, icon, sequence) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        trim($_POST['section_code']),
        trim($_POST['title']),
        trim($_POST['subtitle'] ?? ''),
        trim($_POST['description']),
        trim($_POST['icon'] ?? 'fa-solid fa-circle-check'),
        (int) $_POST['sequence'],
    ]);
    $msg = '<div class="alert alert-success">Section item added.</div>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_section_item'])) {
    $stmt = $db->prepare('UPDATE cms_section_items SET title = ?, subtitle = ?, description = ?, icon = ?, sequence = ?, is_active = ? WHERE id = ?');
    $stmt->execute([
        trim($_POST['title']),
        trim($_POST['subtitle'] ?? ''),
        trim($_POST['description']),
        trim($_POST['icon'] ?? 'fa-solid fa-circle-check'),
        (int) $_POST['sequence'],
        isset($_POST['is_active']) ? 1 : 0,
        (int) $_POST['item_id'],
    ]);
    $msg = '<div class="alert alert-success">Section item updated.</div>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_page_block'])) {
    $img = handleCMSImageUpload('block_image_file');
    $stmt = $db->prepare('INSERT INTO cms_page_blocks (page_slug, block_type, title, subtitle, content, image_path, icon, sequence, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)');
    $stmt->execute([
        trim($_POST['page_slug']),
        trim($_POST['block_type']),
        trim($_POST['title']),
        trim($_POST['subtitle'] ?? ''),
        trim($_POST['content'] ?? ''),
        $img,
        trim($_POST['icon'] ?? ''),
        (int) $_POST['sequence'],
    ]);
    $msg = '<div class="alert alert-success">Page block added.</div>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_page_block'])) {
    $block_id = (int) $_POST['block_id'];
    $img = handleCMSImageUpload('block_image_file');
    if ($img !== '') {
        $stmt = $db->prepare('UPDATE cms_page_blocks SET block_type = ?, title = ?, subtitle = ?, content = ?, image_path = ?, icon = ?, sequence = ?, is_active = ? WHERE id = ?');
        $stmt->execute([
            trim($_POST['block_type']), trim($_POST['title']), trim($_POST['subtitle'] ?? ''), trim($_POST['content'] ?? ''),
            $img, trim($_POST['icon'] ?? ''), (int) $_POST['sequence'], isset($_POST['is_active']) ? 1 : 0, $block_id,
        ]);
    } else {
        $stmt = $db->prepare('UPDATE cms_page_blocks SET block_type = ?, title = ?, subtitle = ?, content = ?, icon = ?, sequence = ?, is_active = ? WHERE id = ?');
        $stmt->execute([
            trim($_POST['block_type']), trim($_POST['title']), trim($_POST['subtitle'] ?? ''), trim($_POST['content'] ?? ''),
            trim($_POST['icon'] ?? ''), (int) $_POST['sequence'], isset($_POST['is_active']) ? 1 : 0, $block_id,
        ]);
    }
    $msg = '<div class="alert alert-success">Page block updated.</div>';
}
