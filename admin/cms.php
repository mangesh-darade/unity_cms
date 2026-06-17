<?php
include '../includes/db.php';
include '../includes/auth.php';
require_once __DIR__ . '/../includes/locations_data.php';
requireAdmin();

$msg = '';

// Helper to handle image uploads to images/ directory
function handleCMSImageUpload($file_field) {
    if (isset($_FILES[$file_field]) && $_FILES[$file_field]['error'] === 0) {
        $file = $_FILES[$file_field];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($file_ext, $allowed)) {
            $new_filename = 'cms_' . time() . '_' . rand(1000, 9999) . '.' . $file_ext;
            $dest = __DIR__ . '/../images/' . $new_filename;
            if (move_uploaded_file($file['tmp_name'], $dest)) {
                return 'images/' . $new_filename;
            }
        }
    }
    return '';
}

// ==========================================
// FORM SUBMISSION HANDLERS
// ==========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    
    // 1. General Content Settings
    if (isset($_POST['update_general'])) {
        try {
            $stmt = $db->prepare("UPDATE cms_settings SET value = :value WHERE key = :key");
            
            $keys = [
                'site_name', 'support_address', 'maps_embed_url', 'maps_directions_url',
                'schema_lat', 'schema_lng',
                'hero_tagline', 'hero_headline', 'hero_subheadline',
                'hero_panel_label', 'hero_panel_live', 'hero_panel_help',
                'hero_stat_1_value', 'hero_stat_1_label',
                'hero_stat_2_value', 'hero_stat_2_label',
                'hero_stat_3_value', 'hero_stat_3_label',
                'hero_stat_4_value', 'hero_stat_4_label',
                'hero_float_1_icon', 'hero_float_1_title', 'hero_float_1_desc',
                'hero_float_2_icon', 'hero_float_2_title', 'hero_float_2_desc',
                'hero_trust_3_text', 'hero_trust_3_icon',
                'hero_trust_4_text', 'hero_trust_4_icon',
                'trust_strip_1_icon', 'trust_strip_1_text',
                'trust_strip_2_icon', 'trust_strip_2_text',
                'trust_strip_3_icon', 'trust_strip_3_text',
                'trust_strip_4_icon', 'trust_strip_4_text',
                'rate_card_image', 'rate_card_cta_text',
            ];
            
            // Check if user uploaded a new hero background image
            $new_hero_bg = handleCMSImageUpload('hero_bg_image_file');
            if (!empty($new_hero_bg)) {
                $stmt->execute([':key' => 'hero_bg_image', ':value' => $new_hero_bg]);
            }

            $new_favicon = handleCMSImageUpload('favicon_file');
            if ($new_favicon !== '') {
                $stmt->execute([':key' => 'favicon_path', ':value' => $new_favicon]);
            }

            $new_apple = handleCMSImageUpload('apple_touch_icon_file');
            if ($new_apple !== '') {
                $stmt->execute([':key' => 'apple_touch_icon', ':value' => $new_apple]);
            }

            $new_rate_card = handleCMSImageUpload('rate_card_image_file');
            if ($new_rate_card !== '') {
                $stmt->execute([':key' => 'rate_card_image', ':value' => $new_rate_card]);
            }

            foreach (['trust_strip_enabled', 'rate_card_enabled', 'mobile_sticky_enabled'] as $flag) {
                $stmt->execute([':key' => $flag, ':value' => isset($_POST[$flag]) ? '1' : '0']);
            }

            foreach ($keys as $k) {
                if (isset($_POST[$k])) {
                    $stmt->execute([':key' => $k, ':value' => trim($_POST[$k])]);
                }
            }

            // Reload global config
            $cms_reload = $db->query("SELECT * FROM cms_settings")->fetchAll();
            $cms = [];
            foreach ($cms_reload as $r) {
                $cms[$r['key']] = $r['value'];
            }
            
            $msg = '<div class="alert alert-success">General settings updated successfully!</div>';
        } catch (PDOException $e) {
            $msg = '<div class="alert alert-error">Database error: ' . $e->getMessage() . '</div>';
        }
    }
    
    // 2. Sections, pages, blocks, and section items (dynamic CMS)
    require __DIR__ . '/includes/cms_dynamic_handlers.php';
    // 3. Add Pathology Service
    if (isset($_POST['add_service'])) {
        $title = trim($_POST['title']);
        $category = trim($_POST['category']);
        $price = (float)$_POST['price'];
        $description = trim($_POST['description']);
        $sample = trim($_POST['sample_type']);
        $prep = trim($_POST['prep_instructions']);
        $seq = (int)$_POST['sequence'];
        
        if (!empty($title) && !empty($category)) {
            try {
                $stmt = $db->prepare("INSERT INTO cms_services (title, category, price, description, sample_type, prep_instructions, sequence) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $category, $price, $description, $sample, $prep, $seq]);
                $msg = '<div class="alert alert-success">New pathology test added successfully!</div>';
            } catch (PDOException $e) {
                $msg = '<div class="alert alert-error">Database error: ' . $e->getMessage() . '</div>';
            }
        } else {
            $msg = '<div class="alert alert-error">Please fill in both Test Title and Category.</div>';
        }
    }
    
    // 4. Add Health Package
    if (isset($_POST['add_package'])) {
        $name = trim($_POST['name']);
        $price = (float)$_POST['price'];
        $desc = trim($_POST['description']);
        $features = trim($_POST['features']);
        $featured = isset($_POST['is_featured']) ? 1 : 0;
        $seq = (int)$_POST['sequence'];
        
        if (!empty($name)) {
            try {
                $stmt = $db->prepare("INSERT INTO cms_packages (name, price, description, features, is_featured, sequence) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $price, $desc, $features, $featured, $seq]);
                $msg = '<div class="alert alert-success">New health package added successfully!</div>';
            } catch (PDOException $e) {
                $msg = '<div class="alert alert-error">Database error: ' . $e->getMessage() . '</div>';
            }
        }
    }
    
    // 5. Add FAQ
    if (isset($_POST['add_faq'])) {
        $q = trim($_POST['question']);
        $a = trim($_POST['answer']);
        $seq = (int)$_POST['sequence'];
        if (!empty($q) && !empty($a)) {
            $stmt = $db->prepare("INSERT INTO cms_faqs (question, answer, sequence) VALUES (?, ?, ?)");
            $stmt->execute([$q, $a, $seq]);
            $msg = '<div class="alert alert-success">FAQ question added.</div>';
        }
    }

    // 6. Add Testimonial
    if (isset($_POST['add_testimonial'])) {
        $text = trim($_POST['text']);
        $author = trim($_POST['author']);
        $desig = trim($_POST['designation']);
        $seq = (int)$_POST['sequence'];
        if (!empty($text) && !empty($author)) {
            $stmt = $db->prepare("INSERT INTO cms_testimonials (text, author, designation, sequence, status, source) VALUES (?, ?, ?, ?, 'approved', 'admin')");
            $stmt->execute([$text, $author, $desig, $seq]);
            $msg = '<div class="alert alert-success">Testimonial card added.</div>';
        }
    }

    if (isset($_POST['approve_testimonial'])) {
        require_once __DIR__ . '/../includes/notifications.php';
        $id = (int) $_POST['testimonial_id'];
        $stmt = $db->prepare("UPDATE cms_testimonials SET status = 'approved' WHERE id = ?");
        $stmt->execute([$id]);
        $row = $db->prepare('SELECT * FROM cms_testimonials WHERE id = ?');
        $row->execute([$id]);
        if ($review = $row->fetch()) {
            notifyPatientReviewApproved($cms, $review);
        }
        $msg = '<div class="alert alert-success">Review approved and published on the website.</div>';
    }

    if (isset($_POST['reject_testimonial'])) {
        $id = (int) $_POST['testimonial_id'];
        $stmt = $db->prepare("UPDATE cms_testimonials SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$id]);
        $msg = '<div class="alert alert-success">Review rejected and hidden from the website.</div>';
    }

    if (isset($_POST['add_location'])) {
        $name = trim($_POST['name'] ?? '');
        $slug = cmsSanitizeLocationSlug(trim($_POST['slug'] ?? ''), $name);
        $headline = trim($_POST['headline'] ?? '');
        $description = trim($_POST['description'] ?? '');
        if ($name !== '' && $headline !== '' && $description !== '') {
            try {
                $stmt = $db->prepare('INSERT INTO cms_locations (slug, name, state, headline, description, keywords, services_text, sequence, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([
                    $slug,
                    $name,
                    trim($_POST['state'] ?? 'Maharashtra'),
                    $headline,
                    $description,
                    trim($_POST['keywords'] ?? ''),
                    trim($_POST['services_text'] ?? ''),
                    (int) ($_POST['sequence'] ?? 0),
                    isset($_POST['is_active']) ? 1 : 0,
                ]);
                $msg = '<div class="alert alert-success">Service location added successfully!</div>';
            } catch (PDOException $e) {
                $msg = '<div class="alert alert-error">Could not add location. Slug may already exist.</div>';
            }
        } else {
            $msg = '<div class="alert alert-error">City name, headline, and description are required.</div>';
        }
    }

    if (isset($_POST['update_location'])) {
        $id = (int) ($_POST['location_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $slug = cmsSanitizeLocationSlug(trim($_POST['slug'] ?? ''), $name);
        if ($id > 0 && $name !== '') {
            try {
                $stmt = $db->prepare('UPDATE cms_locations SET slug=?, name=?, state=?, headline=?, description=?, keywords=?, services_text=?, sequence=?, is_active=? WHERE id=?');
                $stmt->execute([
                    $slug,
                    $name,
                    trim($_POST['state'] ?? 'Maharashtra'),
                    trim($_POST['headline'] ?? ''),
                    trim($_POST['description'] ?? ''),
                    trim($_POST['keywords'] ?? ''),
                    trim($_POST['services_text'] ?? ''),
                    (int) ($_POST['sequence'] ?? 0),
                    isset($_POST['is_active']) ? 1 : 0,
                    $id,
                ]);
                $msg = '<div class="alert alert-success">Service location updated successfully!</div>';
            } catch (PDOException $e) {
                $msg = '<div class="alert alert-error">Could not update location. Slug may already be in use.</div>';
            }
        }
    }

    // 7. Add Gallery Item
    if (isset($_POST['add_gallery'])) {
        $title = trim($_POST['title']);
        $desc = trim($_POST['description']);
        $seq = (int)$_POST['sequence'];
        $img = handleCMSImageUpload('gallery_file');
        
        if (!empty($title) && !empty($img)) {
            $stmt = $db->prepare("INSERT INTO cms_gallery (title, image_path, description, sequence) VALUES (?, ?, ?, ?)");
            $stmt->execute([$title, $img, $desc, $seq]);
            $msg = '<div class="alert alert-success">Gallery slide added.</div>';
        } else {
            $msg = '<div class="alert alert-error">Title and Image file are required.</div>';
        }
    }

    // 8. Add Equipment Item
    if (isset($_POST['add_equipment'])) {
        $title = trim($_POST['title']);
        $desc = trim($_POST['description']);
        $seq = (int)$_POST['sequence'];
        $img = handleCMSImageUpload('equipment_file');
        
        if (!empty($title) && !empty($img)) {
            $stmt = $db->prepare("INSERT INTO cms_equipment (title, image_path, description, sequence) VALUES (?, ?, ?, ?)");
            $stmt->execute([$title, $img, $desc, $seq]);
            $msg = '<div class="alert alert-success">Equipment profile added.</div>';
        } else {
            $msg = '<div class="alert alert-error">Title and Image file are required.</div>';
        }
    }

    // 9. Add Blog Post
    if (isset($_POST['add_blog'])) {
        $title = trim($_POST['title']);
        $author = trim($_POST['author']);
        $summary = trim($_POST['summary']);
        $content = trim($_POST['content']);
        $img = handleCMSImageUpload('blog_file');
        
        if (!empty($title) && !empty($img)) {
            $stmt = $db->prepare("INSERT INTO cms_blogs (title, image_path, author, summary, content) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $img, $author, $summary, $content]);
            $msg = '<div class="alert alert-success">Blog article published successfully!</div>';
        } else {
            $msg = '<div class="alert alert-error">Title and Image are required for articles.</div>';
        }
    }

    // 10. Add Navigation Menu Link
    if (isset($_POST['add_menu_item'])) {
        $title = trim($_POST['menu_title']);
        $url = trim($_POST['menu_url']);
        $seq = (int)$_POST['menu_sequence'];
        $is_cta = isset($_POST['menu_is_cta']) ? 1 : 0;
        
        if (!empty($title) && !empty($url)) {
            try {
                $stmt = $db->prepare("INSERT INTO cms_menu (title, url, sequence, is_active, is_cta) VALUES (?, ?, ?, 1, ?)");
                $stmt->execute([$title, $url, $seq, $is_cta]);
                $msg = '<div class="alert alert-success">Navigation link added successfully!</div>';
            } catch (PDOException $e) {
                $msg = '<div class="alert alert-error">Database error: ' . $e->getMessage() . '</div>';
            }
        }
    }

    // 11. Update Navigation Menu Items (Bulk)
    if (isset($_POST['update_menu_items'])) {
        try {
            $db->beginTransaction();
            $stmt = $db->prepare("UPDATE cms_menu SET title = :title, url = :url, sequence = :seq, is_active = :active, is_cta = :cta WHERE id = :id");
            
            foreach ($_POST['menu_title'] as $id => $title) {
                $url = $_POST['menu_url'][$id];
                $seq = (int)$_POST['menu_sequence'][$id];
                $active = isset($_POST['menu_active'][$id]) ? 1 : 0;
                $cta = isset($_POST['menu_cta'][$id]) ? 1 : 0;
                
                $stmt->execute([
                    ':title' => trim($title),
                    ':url' => trim($url),
                    ':seq' => $seq,
                    ':active' => $active,
                    ':cta' => $cta,
                    ':id' => (int)$id
                ]);
            }
            $db->commit();
            $msg = '<div class="alert alert-success">Navigation layout updated successfully!</div>';
        } catch (PDOException $e) {
            $db->rollBack();
            $msg = '<div class="alert alert-error">Database error: ' . $e->getMessage() . '</div>';
        }
    }

    if (isset($_POST['update_service'])) {
        $id = (int) $_POST['service_id'];
        $stmt = $db->prepare('UPDATE cms_services SET title=?, category=?, price=?, description=?, sample_type=?, prep_instructions=?, sequence=? WHERE id=?');
        $stmt->execute([
            trim($_POST['title']), trim($_POST['category']), (float) $_POST['price'],
            trim($_POST['description']), trim($_POST['sample_type']), trim($_POST['prep_instructions']),
            (int) $_POST['sequence'], $id
        ]);
        $msg = '<div class="alert alert-success">Pathology test updated successfully!</div>';
    }

    if (isset($_POST['update_package'])) {
        $id = (int) $_POST['package_id'];
        $featured = isset($_POST['is_featured']) ? 1 : 0;
        $stmt = $db->prepare('UPDATE cms_packages SET name=?, price=?, description=?, features=?, is_featured=?, sequence=? WHERE id=?');
        $stmt->execute([
            trim($_POST['name']), (float) $_POST['price'], trim($_POST['description']),
            trim($_POST['features']), $featured, (int) $_POST['sequence'], $id
        ]);
        $msg = '<div class="alert alert-success">Health package updated successfully!</div>';
    }

    if (isset($_POST['update_faq'])) {
        $id = (int) $_POST['faq_id'];
        $stmt = $db->prepare('UPDATE cms_faqs SET question=?, answer=?, sequence=? WHERE id=?');
        $stmt->execute([trim($_POST['question']), trim($_POST['answer']), (int) $_POST['sequence'], $id]);
        $msg = '<div class="alert alert-success">FAQ updated successfully!</div>';
    }

    if (isset($_POST['update_testimonial'])) {
        $id = (int) $_POST['testimonial_id'];
        $stmt = $db->prepare('UPDATE cms_testimonials SET text=?, author=?, designation=?, sequence=? WHERE id=?');
        $stmt->execute([trim($_POST['text']), trim($_POST['author']), trim($_POST['designation']), (int) $_POST['sequence'], $id]);
        $msg = '<div class="alert alert-success">Testimonial updated successfully!</div>';
    }

    if (isset($_POST['update_blog'])) {
        $id = (int) $_POST['blog_id'];
        $img = handleCMSImageUpload('blog_file');
        if (!empty($img)) {
            $stmt = $db->prepare('UPDATE cms_blogs SET title=?, image_path=?, author=?, summary=?, content=? WHERE id=?');
            $stmt->execute([trim($_POST['title']), $img, trim($_POST['author']), trim($_POST['summary']), trim($_POST['content']), $id]);
        } else {
            $stmt = $db->prepare('UPDATE cms_blogs SET title=?, author=?, summary=?, content=? WHERE id=?');
            $stmt->execute([trim($_POST['title']), trim($_POST['author']), trim($_POST['summary']), trim($_POST['content']), $id]);
        }
        $msg = '<div class="alert alert-success">Blog article updated successfully!</div>';
    }
}

// ==========================================
// DELETE HANDLERS
// ==========================================
if (isset($_GET['delete_type']) && isset($_GET['id'])) {
    $del_type = $_GET['delete_type'];
    $del_id = (int)$_GET['id'];
    
    try {
        if ($del_type === 'service') {
            $stmt = $db->prepare("DELETE FROM cms_services WHERE id = ?");
            $stmt->execute([$del_id]);
            $msg = '<div class="alert alert-success">Pathology service removed.</div>';
        } elseif ($del_type === 'package') {
            $stmt = $db->prepare("DELETE FROM cms_packages WHERE id = ?");
            $stmt->execute([$del_id]);
            $msg = '<div class="alert alert-success">Health package removed.</div>';
        } elseif ($del_type === 'faq') {
            $stmt = $db->prepare("DELETE FROM cms_faqs WHERE id = ?");
            $stmt->execute([$del_id]);
            $msg = '<div class="alert alert-success">FAQ removed.</div>';
        } elseif ($del_type === 'testimonial') {
            $stmt = $db->prepare("DELETE FROM cms_testimonials WHERE id = ?");
            $stmt->execute([$del_id]);
            $msg = '<div class="alert alert-success">Testimonial removed.</div>';
        } elseif ($del_type === 'location') {
            $stmt = $db->prepare('DELETE FROM cms_locations WHERE id = ?');
            $stmt->execute([$del_id]);
            $msg = '<div class="alert alert-success">Service location removed.</div>';
        } elseif ($del_type === 'gallery') {
            // Unlink file
            $f_path = $db->query("SELECT image_path FROM cms_gallery WHERE id = $del_id")->fetchColumn();
            if ($f_path && file_exists(__DIR__ . '/../' . $f_path)) {
                unlink(__DIR__ . '/../' . $f_path);
            }
            $db->exec("DELETE FROM cms_gallery WHERE id = $del_id");
            $msg = '<div class="alert alert-success">Gallery slide deleted.</div>';
        } elseif ($del_type === 'equipment') {
            $f_path = $db->query("SELECT image_path FROM cms_equipment WHERE id = $del_id")->fetchColumn();
            if ($f_path && file_exists(__DIR__ . '/../' . $f_path)) {
                unlink(__DIR__ . '/../' . $f_path);
            }
            $db->exec("DELETE FROM cms_equipment WHERE id = $del_id");
            $msg = '<div class="alert alert-success">Equipment card deleted.</div>';
        } elseif ($del_type === 'blog') {
            $f_path = $db->query("SELECT image_path FROM cms_blogs WHERE id = $del_id")->fetchColumn();
            if ($f_path && file_exists(__DIR__ . '/../' . $f_path)) {
                unlink(__DIR__ . '/../' . $f_path);
            }
            $db->exec("DELETE FROM cms_blogs WHERE id = $del_id");
            $msg = '<div class="alert alert-success">Blog article deleted.</div>';
        } elseif ($del_type === 'menu') {
            $stmt = $db->prepare("DELETE FROM cms_menu WHERE id = ?");
            $stmt->execute([$del_id]);
            $msg = '<div class="alert alert-success">Navigation link removed.</div>';
        } elseif ($del_type === 'section') {
            $row = $db->prepare('SELECT section_code, section_type FROM cms_sections WHERE id = ?');
            $row->execute([$del_id]);
            $secRow = $row->fetch();
            if ($secRow && in_array($secRow['section_type'], ['custom_html', 'items_grid'], true)) {
                $db->prepare('DELETE FROM cms_section_items WHERE section_code = ?')->execute([$secRow['section_code']]);
                $db->prepare('DELETE FROM cms_sections WHERE id = ?')->execute([$del_id]);
                $msg = '<div class="alert alert-success">Custom homepage section removed.</div>';
            } else {
                $msg = '<div class="alert alert-error">Built-in sections cannot be deleted. Disable them instead.</div>';
            }
        } elseif ($del_type === 'section_item') {
            $stmt = $db->prepare("DELETE FROM cms_section_items WHERE id = ?");
            $stmt->execute([$del_id]);
            $msg = '<div class="alert alert-success">Section item deleted.</div>';
        } elseif ($del_type === 'page_block') {
            $stmt = $db->prepare("DELETE FROM cms_page_blocks WHERE id = ?");
            $stmt->execute([$del_id]);
            $msg = '<div class="alert alert-success">Page block deleted.</div>';
        }
    } catch (PDOException $e) {
        $msg = '<div class="alert alert-error">Database deletion error: ' . $e->getMessage() . '</div>';
    }
}

// ==========================================
// QUERY CURRENT RECORDS
// ==========================================
function cmsFetchEdit(PDO $db, string $table, string $param): ?array {
    if (!isset($_GET[$param])) {
        return null;
    }
    $id = (int) $_GET[$param];
    if ($id <= 0) {
        return null;
    }
    $stmt = $db->prepare("SELECT * FROM {$table} WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

$edit_service = cmsFetchEdit($db, 'cms_services', 'edit_service');
$edit_package = cmsFetchEdit($db, 'cms_packages', 'edit_package');
$edit_faq = cmsFetchEdit($db, 'cms_faqs', 'edit_faq');
$edit_testimonial = cmsFetchEdit($db, 'cms_testimonials', 'edit_testimonial');
$edit_location = cmsFetchEdit($db, 'cms_locations', 'edit_location');
$edit_blog = cmsFetchEdit($db, 'cms_blogs', 'edit_blog');
$edit_page = cmsFetchEdit($db, 'cms_pages', 'edit_page');
$edit_section_item = cmsFetchEdit($db, 'cms_section_items', 'edit_section_item');
$edit_page_block = cmsFetchEdit($db, 'cms_page_blocks', 'edit_page_block');
$edit_section = cmsFetchEdit($db, 'cms_sections', 'edit_section');
$section_templates = cmsAvailableSectionTemplates();

$sections = $db->query("SELECT * FROM cms_sections ORDER BY sequence ASC")->fetchAll();
$site_pages = $db->query("SELECT * FROM cms_pages ORDER BY sequence ASC, id ASC")->fetchAll();
$section_items = $db->query("SELECT * FROM cms_section_items ORDER BY section_code ASC, sequence ASC")->fetchAll();
$block_page_filter = isset($_GET['block_page']) ? trim($_GET['block_page']) : 'about';
$page_blocks = $db->prepare("SELECT * FROM cms_page_blocks WHERE page_slug = ? ORDER BY sequence ASC, id ASC");
$page_blocks->execute([$block_page_filter]);
$page_blocks = $page_blocks->fetchAll();
$services = $db->query("SELECT * FROM cms_services ORDER BY sequence ASC")->fetchAll();
$packages = $db->query("SELECT * FROM cms_packages ORDER BY sequence ASC")->fetchAll();
$faqs = $db->query("SELECT * FROM cms_faqs ORDER BY sequence ASC")->fetchAll();
$testimonials = $db->query("SELECT * FROM cms_testimonials ORDER BY CASE status WHEN 'pending' THEN 0 WHEN 'approved' THEN 1 ELSE 2 END, sequence ASC")->fetchAll();
$pending_testimonials = array_values(array_filter($testimonials, fn($t) => ($t['status'] ?? 'approved') === 'pending'));
$published_testimonials = array_values(array_filter($testimonials, fn($t) => in_array($t['status'] ?? 'approved', ['approved', ''], true) || ($t['status'] ?? '') === ''));
$locations = cmsLocationAreasAll($db);
$gallery = $db->query("SELECT * FROM cms_gallery ORDER BY sequence ASC")->fetchAll();
$equipment = $db->query("SELECT * FROM cms_equipment ORDER BY sequence ASC")->fetchAll();
$blogs = $db->query("SELECT * FROM cms_blogs ORDER BY id DESC")->fetchAll();
$menu_items_admin = $db->query("SELECT * FROM cms_menu ORDER BY sequence ASC")->fetchAll();
$admin_nav = 'cms';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Website CMS Panel - Unity Lab Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/cms.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-body">

    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="admin-main">
        <div class="admin-header">
            <div class="admin-title">
                <h1>Website CMS Dashboard</h1>
                <p>Modify texts, upload banners, reorder pages, and manage service listings dynamically.</p>
            </div>
            <div class="admin-user-info">
                <i class="fa-solid fa-circle-user" style="font-size: 1.5rem; color: #0d9488;"></i>
                <span>Admin</span>
                <a href="index.php?logout=1" class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.85rem; border-radius: 4px;"><i class="fa-solid fa-power-off"></i> Logout</a>
            </div>
        </div>

        <?php echo $msg; ?>

        <!-- CMS Tab Layout -->
        <div class="cms-container">
            <!-- Left Tabs Sidebar -->
            <ul class="cms-tabs-list">
                <li><button class="cms-tab-btn active" data-tab="tab-general"><i class="fa-solid fa-gear"></i> General Settings</button></li>
                <li><button class="cms-tab-btn" data-tab="tab-header"><i class="fa-solid fa-bars"></i> Website Header</button></li>
                <li><button class="cms-tab-btn" data-tab="tab-footer"><i class="fa-solid fa-shoe-prints"></i> Website Footer</button></li>
                <li><button class="cms-tab-btn" data-tab="tab-pages"><i class="fa-solid fa-file-lines"></i> Site Pages</button></li>
                <li><button class="cms-tab-btn" data-tab="tab-sections"><i class="fa-solid fa-list-ol"></i> Home Sections</button></li>
                <li><button class="cms-tab-btn" data-tab="tab-section-items"><i class="fa-solid fa-layer-group"></i> Section Items</button></li>
                <li><button class="cms-tab-btn" data-tab="tab-page-blocks"><i class="fa-solid fa-cubes"></i> Page Blocks</button></li>
                <li><button class="cms-tab-btn" data-tab="tab-marketing"><i class="fa-solid fa-bullhorn"></i> Digital Marketing</button></li>
                <li><button class="cms-tab-btn" data-tab="tab-services"><i class="fa-solid fa-microscope"></i> Pathology Tests</button></li>
                <li><button class="cms-tab-btn" data-tab="tab-packages"><i class="fa-solid fa-box-tissue"></i> Health Packages</button></li>
                <li><button class="cms-tab-btn" data-tab="tab-faqs"><i class="fa-solid fa-circle-question"></i> FAQ Accordion</button></li>
                <li><button class="cms-tab-btn" data-tab="tab-testimonials"><i class="fa-solid fa-star"></i> Testimonials</button></li>
                <li><button class="cms-tab-btn" data-tab="tab-locations"><i class="fa-solid fa-map-location-dot"></i> Service Locations</button></li>
                <li><button class="cms-tab-btn" data-tab="tab-media"><i class="fa-solid fa-images"></i> Gallery & Equipment</button></li>
                <li><button class="cms-tab-btn" data-tab="tab-blog"><i class="fa-solid fa-pen-nib"></i> health Blog</button></li>
                <li><button class="cms-tab-btn" data-tab="tab-menu"><i class="fa-solid fa-compass"></i> Navigation Menu</button></li>
            </ul>

            <!-- Right Tab Contents -->
            <div style="flex-grow: 1; min-width: 0;">
                
                <!-- Tab 1: General Info -->
                <div id="tab-general" class="cms-tab-content active">
                    <div class="admin-panel-card">
                        <div class="admin-card-header">
                            <h2>General Branding & Contacts Settings</h2>
                        </div>
                        <form action="cms.php" method="POST" enctype="multipart/form-data">
                            <?php echo csrfField(); ?>
                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label class="form-label">Business Name</label>
                                    <input type="text" name="site_name" class="form-control" value="<?php echo htmlspecialchars($cms['site_name']); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Favicon (browser tab icon)</label>
                                    <input type="file" name="favicon_file" class="form-control" accept="image/*" style="padding:8px;">
                                    <?php if ($fav = cmsSetting($cms, 'favicon_path')): ?>
                                    <small style="color:#64748b;">Current: <a href="../<?php echo htmlspecialchars($fav); ?>" target="_blank" rel="noopener"><?php echo htmlspecialchars($fav); ?></a></small>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Physical Address</label>
                                <textarea name="support_address" class="form-control" rows="3" placeholder="Lab name, street, landmark, city, pincode"><?php echo htmlspecialchars($cms['support_address']); ?></textarea>
                            </div>

                            <h3 style="font-size:1.05rem;color:var(--primary);margin:20px 0 12px;"><i class="fa-solid fa-map-location-dot"></i> Google Maps (exact lab location)</h3>
                            <p style="font-size:0.85rem;color:#64748b;margin-bottom:12px;">Open Google Maps → find your lab → Share → <strong>Embed a map</strong> (paste iframe <code>src</code> URL below). For directions link, use Share → <strong>Send a link</strong>.</p>
                            <div class="form-group">
                                <label class="form-label">Maps Embed URL</label>
                                <input type="url" name="maps_embed_url" class="form-control" value="<?php echo htmlspecialchars($cms['maps_embed_url'] ?? ''); ?>" placeholder="https://www.google.com/maps/embed?pb=...">
                                <small style="color:#64748b;">Used on Contact page and homepage contact section.</small>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Google Maps Directions URL</label>
                                <input type="url" name="maps_directions_url" class="form-control" value="<?php echo htmlspecialchars($cms['maps_directions_url'] ?? ''); ?>" placeholder="https://www.google.com/maps/dir/?api=1&destination=...">
                                <small style="color:#64748b;">Optional “Get Directions” button on Contact page.</small>
                            </div>
                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label class="form-label">Latitude (schema / geo)</label>
                                    <input type="text" name="schema_lat" class="form-control" value="<?php echo htmlspecialchars($cms['schema_lat'] ?? ''); ?>" placeholder="18.5204">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Longitude (schema / geo)</label>
                                    <input type="text" name="schema_lng" class="form-control" value="<?php echo htmlspecialchars($cms['schema_lng'] ?? ''); ?>" placeholder="73.8567">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Apple Touch Icon (optional)</label>
                                <input type="file" name="apple_touch_icon_file" class="form-control" accept="image/*" style="padding:8px;">
                                <?php if ($apple = cmsSetting($cms, 'apple_touch_icon')): ?>
                                <small style="color:#64748b;">Current: <?php echo htmlspecialchars($apple); ?></small>
                                <?php endif; ?>
                            </div>

                            <div class="form-group" style="display:flex;align-items:center;gap:10px;margin-top:8px;">
                                <input type="checkbox" name="mobile_sticky_enabled" value="1" id="mobile_sticky_enabled" <?php echo ($cms['mobile_sticky_enabled'] ?? '1') === '1' ? 'checked' : ''; ?> style="width:20px;height:20px;">
                                <label for="mobile_sticky_enabled" style="margin:0;">Show mobile sticky bar (Call / WhatsApp / Book)</label>
                            </div>

                            <p style="font-size:0.85rem;color:#64748b;margin:12px 0 20px;padding:12px;background:#f8fafc;border-radius:6px;border:1px solid #e2e8f0;">
                                Logo, phone, email, offer banner → <a href="cms.php#tab-header">Website Header</a>.
                                Footer columns &amp; hours → <a href="cms.php#tab-footer">Website Footer</a>.
                            </p>

                            <div style="border-top: 1px solid var(--border); margin-top: 20px; padding-top: 20px;">
                                <h3 style="font-size: 1.1rem; margin-bottom: 15px; color: var(--primary);">Homepage Hero Banner Settings</h3>
                                
                                <div class="form-group">
                                    <label class="form-label">Hero Tagline</label>
                                    <input type="text" name="hero_tagline" class="form-control" value="<?php echo htmlspecialchars($cms['hero_tagline']); ?>">
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Hero Headline</label>
                                    <input type="text" name="hero_headline" class="form-control" value="<?php echo htmlspecialchars($cms['hero_headline']); ?>">
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Hero Sub-headline Description</label>
                                    <textarea name="hero_subheadline" class="form-control" rows="2"><?php echo htmlspecialchars($cms['hero_subheadline']); ?></textarea>
                                </div>

                                <div class="admin-form-row align-center">
                                    <div class="form-group">
                                        <label class="form-label">Change Hero Banner Background Image</label>
                                        <input type="file" name="hero_bg_image_file" class="form-control" accept="image/*" style="padding:8px;">
                                    </div>
                                    <div>
                                        <label class="form-label">Current Background Preview</label>
                                        <img src="../<?php echo htmlspecialchars($cms['hero_bg_image']); ?>" class="img-preview-badge" style="width:140px; height: 70px;">
                                    </div>
                                </div>

                                <h4 style="font-size:1rem;margin:24px 0 12px;color:var(--primary);">Hero Stats Panel</h4>
                                <div class="admin-form-row">
                                    <div class="form-group"><label class="form-label">Panel Title</label><input type="text" name="hero_panel_label" class="form-control" value="<?php echo htmlspecialchars($cms['hero_panel_label'] ?? 'Lab Excellence'); ?>"></div>
                                    <div class="form-group"><label class="form-label">Live Badge Text</label><input type="text" name="hero_panel_live" class="form-control" value="<?php echo htmlspecialchars($cms['hero_panel_live'] ?? 'Open Now'); ?>"></div>
                                </div>
                                <div class="form-group"><label class="form-label">Panel Help Text</label><input type="text" name="hero_panel_help" class="form-control" value="<?php echo htmlspecialchars($cms['hero_panel_help'] ?? 'Need help booking?'); ?>"></div>
                                <?php for ($i = 1; $i <= 4; $i++): ?>
                                <div class="admin-form-row">
                                    <div class="form-group"><label class="form-label">Stat <?php echo $i; ?> Value</label><input type="text" name="hero_stat_<?php echo $i; ?>_value" class="form-control" value="<?php echo htmlspecialchars($cms['hero_stat_' . $i . '_value'] ?? ''); ?>"></div>
                                    <div class="form-group"><label class="form-label">Stat <?php echo $i; ?> Label</label><input type="text" name="hero_stat_<?php echo $i; ?>_label" class="form-control" value="<?php echo htmlspecialchars($cms['hero_stat_' . $i . '_label'] ?? ''); ?>"></div>
                                </div>
                                <?php endfor; ?>

                                <h4 style="font-size:1rem;margin:24px 0 12px;color:var(--primary);">Floating Cards &amp; Trust Badges</h4>
                                <?php for ($f = 1; $f <= 2; $f++): ?>
                                <div class="admin-form-row">
                                    <div class="form-group"><label class="form-label">Float Card <?php echo $f; ?> Icon (FA class)</label><input type="text" name="hero_float_<?php echo $f; ?>_icon" class="form-control" value="<?php echo htmlspecialchars($cms['hero_float_' . $f . '_icon'] ?? ''); ?>" placeholder="fa-solid fa-microscope"></div>
                                    <div class="form-group"><label class="form-label">Float Card <?php echo $f; ?> Title</label><input type="text" name="hero_float_<?php echo $f; ?>_title" class="form-control" value="<?php echo htmlspecialchars($cms['hero_float_' . $f . '_title'] ?? ''); ?>"></div>
                                </div>
                                <div class="form-group"><label class="form-label">Float Card <?php echo $f; ?> Description</label><input type="text" name="hero_float_<?php echo $f; ?>_desc" class="form-control" value="<?php echo htmlspecialchars($cms['hero_float_' . $f . '_desc'] ?? ''); ?>"></div>
                                <?php endfor; ?>
                                <div class="admin-form-row">
                                    <div class="form-group"><label class="form-label">Trust Badge 3 Icon</label><input type="text" name="hero_trust_3_icon" class="form-control" value="<?php echo htmlspecialchars($cms['hero_trust_3_icon'] ?? 'fa-solid fa-bolt'); ?>"></div>
                                    <div class="form-group"><label class="form-label">Trust Badge 3 Text</label><input type="text" name="hero_trust_3_text" class="form-control" value="<?php echo htmlspecialchars($cms['hero_trust_3_text'] ?? 'Reports in 6–12 hrs'); ?>"></div>
                                </div>
                                <div class="admin-form-row">
                                    <div class="form-group"><label class="form-label">Trust Badge 4 Icon</label><input type="text" name="hero_trust_4_icon" class="form-control" value="<?php echo htmlspecialchars($cms['hero_trust_4_icon'] ?? 'fa-solid fa-house-medical'); ?>"></div>
                                    <div class="form-group"><label class="form-label">Trust Badge 4 Text</label><input type="text" name="hero_trust_4_text" class="form-control" value="<?php echo htmlspecialchars($cms['hero_trust_4_text'] ?? 'Home Collection'); ?>"></div>
                                </div>

                                <h4 style="font-size:1rem;margin:24px 0 12px;color:var(--primary);">Homepage Trust Strip &amp; Rate Card</h4>
                                <div class="form-group" style="display:flex;align-items:center;gap:10px;">
                                    <input type="checkbox" name="trust_strip_enabled" value="1" id="trust_strip_enabled" <?php echo ($cms['trust_strip_enabled'] ?? '1') === '1' ? 'checked' : ''; ?> style="width:20px;height:20px;">
                                    <label for="trust_strip_enabled" style="margin:0;">Show trust strip below hero (90+ tests, home collection, etc.)</label>
                                </div>
                                <?php for ($t = 1; $t <= 4; $t++): ?>
                                <div class="admin-form-row">
                                    <div class="form-group"><label class="form-label">Strip Item <?php echo $t; ?> Icon</label><input type="text" name="trust_strip_<?php echo $t; ?>_icon" class="form-control" value="<?php echo htmlspecialchars($cms['trust_strip_' . $t . '_icon'] ?? ''); ?>" placeholder="fa-solid fa-vials"></div>
                                    <div class="form-group"><label class="form-label">Strip Item <?php echo $t; ?> Text</label><input type="text" name="trust_strip_<?php echo $t; ?>_text" class="form-control" value="<?php echo htmlspecialchars($cms['trust_strip_' . $t . '_text'] ?? ''); ?>"></div>
                                </div>
                                <?php endfor; ?>
                                <div class="form-group" style="display:flex;align-items:center;gap:10px;">
                                    <input type="checkbox" name="rate_card_enabled" value="1" id="rate_card_enabled" <?php echo ($cms['rate_card_enabled'] ?? '1') === '1' ? 'checked' : ''; ?> style="width:20px;height:20px;">
                                    <label for="rate_card_enabled" style="margin:0;">Show “View Rate Card” button on trust strip</label>
                                </div>
                                <div class="admin-form-row">
                                    <div class="form-group">
                                        <label class="form-label">Rate Card Image</label>
                                        <input type="file" name="rate_card_image_file" class="form-control" accept="image/*" style="padding:8px;">
                                        <input type="text" name="rate_card_image" class="form-control" style="margin-top:8px;" value="<?php echo htmlspecialchars($cms['rate_card_image'] ?? 'images/gallery/web/rate-card.jpg'); ?>" placeholder="images/gallery/web/rate-card.jpg">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Rate Card Button Text</label>
                                        <input type="text" name="rate_card_cta_text" class="form-control" value="<?php echo htmlspecialchars($cms['rate_card_cta_text'] ?? 'View Rate Card'); ?>">
                                    </div>
                                </div>
                            </div>

                            <button type="submit" name="update_general" class="btn btn-teal w-full" style="margin-top: 20px;">
                                <i class="fa-solid fa-circle-check"></i> Save General Configurations
                            </button>
                        </form>
                    </div>
                </div>

                <?php include __DIR__ . '/includes/cms_header_tab.php'; ?>
                <?php include __DIR__ . '/includes/cms_footer_tab.php'; ?>

                <!-- Tab: Site Pages -->
                <div id="tab-pages" class="cms-tab-content">
                    <?php if ($edit_page): ?>
                    <div class="cms-form-box" style="border:2px solid var(--brand-teal); margin-bottom: 24px;">
                        <h3 style="font-size: 1.2rem; margin-bottom: 15px; color: var(--primary);">Edit Page: <?php echo htmlspecialchars($edit_page['slug']); ?></h3>
                        <form action="cms.php#tab-pages" method="POST" enctype="multipart/form-data">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="page_id" value="<?php echo (int) $edit_page['id']; ?>">
                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label class="form-label">Page Heading (H1)</label>
                                    <input type="text" name="page_heading" class="form-control" value="<?php echo htmlspecialchars($edit_page['page_heading']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Breadcrumb Label</label>
                                    <input type="text" name="breadcrumb_label" class="form-control" value="<?php echo htmlspecialchars($edit_page['breadcrumb_label']); ?>" required>
                                </div>
                            </div>
                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label class="form-label">Meta Title (SEO)</label>
                                    <input type="text" name="meta_title" class="form-control" value="<?php echo htmlspecialchars($edit_page['meta_title']); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Meta Keywords</label>
                                    <input type="text" name="meta_keywords" class="form-control" value="<?php echo htmlspecialchars($edit_page['meta_keywords'] ?? ''); ?>" placeholder="comma, separated, keywords">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Meta Description (SEO)</label>
                                <textarea name="meta_description" class="form-control" rows="2"><?php echo htmlspecialchars($edit_page['meta_description']); ?></textarea>
                            </div>
                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label class="form-label">OG Image (optional override)</label>
                                    <input type="file" name="page_og_image_file" class="form-control" accept="image/*" style="padding:8px;">
                                    <?php if (!empty($edit_page['og_image'])): ?><small>Current: <?php echo htmlspecialchars($edit_page['og_image']); ?></small><?php endif; ?>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">File</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($edit_page['filename']); ?>" disabled>
                                </div>
                            </div>
                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label class="form-label">Sitemap Change Frequency</label>
                                    <select name="sitemap_changefreq" class="form-control">
                                        <?php foreach (['always','hourly','daily','weekly','monthly','yearly','never'] as $freq): ?>
                                            <option value="<?php echo $freq; ?>" <?php echo (($edit_page['sitemap_changefreq'] ?? 'monthly') === $freq) ? 'selected' : ''; ?>><?php echo ucfirst($freq); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Sitemap Priority (0.0 - 1.0)</label>
                                    <input type="text" name="sitemap_priority" class="form-control" value="<?php echo htmlspecialchars($edit_page['sitemap_priority'] ?? '0.5'); ?>">
                                </div>
                            </div>
                            <div style="display:flex; gap:20px; flex-wrap:wrap; margin-bottom:12px;">
                                <label style="display:flex;align-items:center;gap:8px;"><input type="checkbox" name="include_in_sitemap" value="1" <?php echo ((int)($edit_page['include_in_sitemap'] ?? 1) === 1) ? 'checked' : ''; ?>> Include in sitemap</label>
                                <label style="display:flex;align-items:center;gap:8px;"><input type="checkbox" name="robots_noindex" value="1" <?php echo ((int)($edit_page['robots_noindex'] ?? 0) === 1) ? 'checked' : ''; ?>> Noindex (hide from search engines)</label>
                                <label style="display:flex;align-items:center;gap:8px;"><input type="checkbox" name="is_active" value="1" id="page_active_<?php echo (int) $edit_page['id']; ?>" <?php echo ((int) $edit_page['is_active'] === 1) ? 'checked' : ''; ?>> Page active</label>
                            </div>
                            <?php if (in_array($edit_page['slug'], ['privacy', 'terms'], true)): ?>
                            <div class="form-group">
                                <label class="form-label">Page Body (HTML allowed)</label>
                                <textarea name="page_body" class="form-control" rows="14" style="font-family:monospace; font-size:0.85rem;"><?php echo htmlspecialchars($edit_page['page_body'] ?? ''); ?></textarea>
                                <small style="color:#64748b;">Edit Privacy Policy / Terms content. Use basic HTML tags.</small>
                            </div>
                            <?php endif; ?>
                            <div style="border-top:1px solid var(--border); margin:16px 0; padding-top:16px;">
                                <h4 style="font-size:1rem; margin-bottom:12px; color:var(--primary);">In-page Section Header (tag / title / description)</h4>
                                <div class="admin-form-row">
                                    <div class="form-group">
                                        <label class="form-label">Content Tag</label>
                                        <input type="text" name="content_tag" class="form-control" value="<?php echo htmlspecialchars($edit_page['content_tag'] ?? ''); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Content Title</label>
                                        <input type="text" name="content_title" class="form-control" value="<?php echo htmlspecialchars($edit_page['content_title'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Content Description</label>
                                    <textarea name="content_description" class="form-control" rows="2"><?php echo htmlspecialchars($edit_page['content_description'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            <div style="display:flex; gap:10px; margin-top:16px;">
                                <button type="submit" name="update_page" class="btn btn-teal"><i class="fa-solid fa-circle-check"></i> Save Page</button>
                                <a href="cms.php#tab-pages" class="btn btn-secondary">Cancel</a>
                                <?php if ($edit_page['slug'] === 'about'): ?>
                                    <a href="cms.php?block_page=about#tab-page-blocks" class="btn btn-secondary"><i class="fa-solid fa-cubes"></i> Edit About Blocks</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>

                    <div class="admin-panel-card">
                        <div class="admin-card-header">
                            <h2>Site Pages</h2>
                            <p style="font-size:0.85rem; color:#64748b;">Edit page titles, breadcrumbs, SEO meta, and listing-page section headers.</p>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Page</th>
                                        <th>Heading</th>
                                        <th>File</th>
                                        <th>Active</th>
                                        <th style="width:120px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($site_pages as $pg): ?>
                                    <tr>
                                        <td><code><?php echo htmlspecialchars($pg['slug']); ?></code></td>
                                        <td><?php echo htmlspecialchars($pg['page_heading']); ?></td>
                                        <td style="font-size:0.85rem;"><?php echo htmlspecialchars($pg['filename']); ?></td>
                                        <td><?php echo ((int) $pg['is_active'] === 1) ? '<span style="color:var(--brand-teal);">Yes</span>' : 'No'; ?></td>
                                        <td>
                                            <a href="cms.php?edit_page=<?php echo (int) $pg['id']; ?>#tab-pages" class="action-btn"><i class="fa-regular fa-pen-to-square"></i> Edit</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Tab: Homepage Sections -->
                <div id="tab-sections" class="cms-tab-content">
                    <?php if ($edit_section): ?>
                    <div class="cms-form-box" style="border:2px solid var(--brand-teal); margin-bottom:24px;">
                        <h3 style="font-size:1.2rem;margin-bottom:15px;color:var(--primary);">Edit Section: <?php echo htmlspecialchars($edit_section['section_code']); ?></h3>
                        <form action="cms.php#tab-sections" method="POST">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="section_id" value="<?php echo (int) $edit_section['id']; ?>">
                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label class="form-label">Section Code</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($edit_section['section_code']); ?>" disabled>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Display Title (admin label)</label>
                                    <input type="text" name="section_title" class="form-control" value="<?php echo htmlspecialchars($edit_section['section_title']); ?>" required>
                                </div>
                            </div>
                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label class="form-label">Section Type</label>
                                    <select name="section_type" class="form-control">
                                        <option value="builtin" <?php echo ($edit_section['section_type'] ?? 'builtin') === 'builtin' ? 'selected' : ''; ?>>Built-in template (PHP file)</option>
                                        <option value="custom_html" <?php echo ($edit_section['section_type'] ?? '') === 'custom_html' ? 'selected' : ''; ?>>Custom HTML block</option>
                                        <option value="items_grid" <?php echo ($edit_section['section_type'] ?? '') === 'items_grid' ? 'selected' : ''; ?>>Icon cards grid (section items)</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Sequence</label>
                                    <input type="number" name="sequence" class="form-control" value="<?php echo (int) $edit_section['sequence']; ?>" min="1" required>
                                </div>
                            </div>
                            <div class="admin-form-row">
                                <div class="form-group"><label class="form-label">Tag</label><input type="text" name="section_tag" class="form-control" value="<?php echo htmlspecialchars($edit_section['section_tag'] ?? ''); ?>"></div>
                                <div class="form-group"><label class="form-label">Heading</label><input type="text" name="section_heading" class="form-control" value="<?php echo htmlspecialchars($edit_section['section_heading'] ?? ''); ?>"></div>
                            </div>
                            <div class="form-group"><label class="form-label">Description</label><input type="text" name="section_description" class="form-control" value="<?php echo htmlspecialchars($edit_section['section_description'] ?? ''); ?>"></div>
                            <?php if (in_array($edit_section['section_type'] ?? 'builtin', ['custom_html'], true) || ($edit_section['section_type'] ?? '') === 'custom_html'): ?>
                            <div class="form-group"><label class="form-label">Custom HTML Body</label><textarea name="section_body" class="form-control" rows="8" style="font-family:monospace;font-size:0.85rem;"><?php echo htmlspecialchars($edit_section['section_body'] ?? ''); ?></textarea></div>
                            <?php else: ?>
                            <div class="form-group"><label class="form-label">Custom HTML Body (for Custom HTML type)</label><textarea name="section_body" class="form-control" rows="4" style="font-family:monospace;font-size:0.85rem;"><?php echo htmlspecialchars($edit_section['section_body'] ?? ''); ?></textarea></div>
                            <?php endif; ?>
                            <div class="form-group" style="display:flex;align-items:center;gap:10px;">
                                <input type="checkbox" name="is_active" value="1" <?php echo ((int)$edit_section['is_active'] === 1) ? 'checked' : ''; ?> style="width:20px;height:20px;">
                                <label style="margin:0;">Active on homepage</label>
                            </div>
                            <div style="display:flex;gap:10px;margin-top:16px;">
                                <button type="submit" name="update_section" class="btn btn-teal">Save Section</button>
                                <a href="cms.php#tab-sections" class="btn btn-secondary">Cancel</a>
                                <?php if (($edit_section['section_type'] ?? '') === 'items_grid'): ?>
                                <a href="cms.php#tab-section-items" class="btn btn-secondary">Manage Items</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>

                    <div class="cms-form-box" style="margin-bottom:24px;">
                        <h3 style="font-size:1.1rem;margin-bottom:15px;color:var(--primary);"><i class="fa-solid fa-plus"></i> Add Homepage Section</h3>
                        <form action="cms.php#tab-sections" method="POST">
                            <?php echo csrfField(); ?>
                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label class="form-label">Section Code (lowercase, underscores)</label>
                                    <input type="text" name="section_code" class="form-control" placeholder="e.g. promo_banner" required pattern="[a-z0-9_]+">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Display Title</label>
                                    <input type="text" name="section_title" class="form-control" required>
                                </div>
                            </div>
                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label class="form-label">Section Type</label>
                                    <select name="section_type" class="form-control" id="new_section_type">
                                        <option value="items_grid">Icon cards grid (add items after)</option>
                                        <option value="custom_html">Custom HTML content block</option>
                                        <option value="builtin">Built-in PHP template</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Sequence</label>
                                    <input type="number" name="sequence" class="form-control" value="<?php echo count($sections) + 1; ?>" min="1">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Available built-in templates</label>
                                <p style="font-size:0.85rem;color:#64748b;margin-bottom:8px;">For <strong>Built-in</strong> type, section code must match a file: <?php echo htmlspecialchars(implode(', ', $section_templates)); ?></p>
                            </div>
                            <div class="form-group">
                                <label class="form-label">HTML Body (Custom HTML type)</label>
                                <textarea name="section_body" class="form-control" rows="4" placeholder="<p>Your custom section HTML...</p>"></textarea>
                            </div>
                            <button type="submit" name="add_section" class="btn btn-teal"><i class="fa-solid fa-plus"></i> Add Section</button>
                        </form>
                    </div>

                    <div class="admin-panel-card">
                        <div class="admin-card-header">
                            <h2>Home Sections — Order &amp; Content</h2>
                            <p style="font-size:0.85rem; color:#64748b;">Reorder, enable/disable, and edit section headers. Use <strong>Edit</strong> for full section settings.</p>
                        </div>
                        
                        <form action="cms.php#tab-sections" method="POST">
                            <?php echo csrfField(); ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th style="width:50px;">On</th>
                                            <th>Section</th>
                                            <th style="width:60px;">Seq</th>
                                            <th>Type</th>
                                            <th>Tag</th>
                                            <th>Heading</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($sections as $sec): ?>
                                            <tr>
                                                <td class="text-center">
                                                    <input type="checkbox" name="is_active[<?php echo $sec['id']; ?>]" value="1" <?php echo ((int)$sec['is_active'] === 1) ? 'checked' : ''; ?> style="width: 20px; height: 20px;">
                                                </td>
                                                <td>
                                                    <input type="text" name="section_title[<?php echo $sec['id']; ?>]" class="form-control" value="<?php echo htmlspecialchars($sec['section_title']); ?>" style="padding:4px;font-size:0.85rem;font-weight:600;">
                                                    <code style="font-size:0.75rem;"><?php echo htmlspecialchars($sec['section_code']); ?></code>
                                                </td>
                                                <td>
                                                    <input type="number" name="sequence[<?php echo $sec['id']; ?>]" class="form-control" value="<?php echo $sec['sequence']; ?>" min="1" max="99" required style="padding: 4px; text-align: center;">
                                                </td>
                                                <td style="font-size:0.8rem;"><code><?php echo htmlspecialchars($sec['section_type'] ?? 'builtin'); ?></code></td>
                                                <td>
                                                    <input type="text" name="section_tag[<?php echo $sec['id']; ?>]" class="form-control" value="<?php echo htmlspecialchars($sec['section_tag'] ?? ''); ?>" style="padding:4px;font-size:0.85rem;">
                                                </td>
                                                <td>
                                                    <input type="text" name="section_heading[<?php echo $sec['id']; ?>]" class="form-control" value="<?php echo htmlspecialchars($sec['section_heading'] ?? ''); ?>" style="padding:4px;font-size:0.85rem;">
                                                    <input type="hidden" name="section_description[<?php echo $sec['id']; ?>]" value="<?php echo htmlspecialchars($sec['section_description'] ?? ''); ?>">
                                                </td>
                                                <td style="white-space:nowrap;">
                                                    <a href="cms.php?edit_section=<?php echo (int)$sec['id']; ?>#tab-sections" class="action-btn"><i class="fa-regular fa-pen-to-square"></i></a>
                                                    <?php if (in_array($sec['section_type'] ?? '', ['custom_html', 'items_grid'], true)): ?>
                                                    <a href="cms.php?delete_type=section&id=<?php echo (int)$sec['id']; ?>#tab-sections" class="action-btn" onclick="return confirm('Delete this section?');"><i class="fa-solid fa-trash"></i></a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <button type="submit" name="update_sections" class="btn btn-teal w-full" style="margin-top: 20px;">
                                <i class="fa-solid fa-list-ol"></i> Save Quick Layout Changes
                            </button>
                        </form>
                        <p style="margin-top:16px; font-size:0.85rem; color:#64748b;">
                            Icon card sections → manage cards under <a href="cms.php#tab-section-items">Section Items</a>.
                        </p>
                    </div>
                </div>

                <!-- Tab: Section Items -->
                <div id="tab-section-items" class="cms-tab-content">
                    <?php if ($edit_section_item): ?>
                    <div class="cms-form-box" style="border:2px solid var(--brand-teal); margin-bottom: 24px;">
                        <h3 style="font-size: 1.2rem; margin-bottom: 15px; color: var(--primary);">Edit Section Item</h3>
                        <form action="cms.php#tab-section-items" method="POST">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="item_id" value="<?php echo (int) $edit_section_item['id']; ?>">
                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label class="form-label">Section Code</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($edit_section_item['section_code']); ?>" disabled>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Sequence</label>
                                    <input type="number" name="sequence" class="form-control" value="<?php echo (int) $edit_section_item['sequence']; ?>" min="1" required>
                                </div>
                            </div>
                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label class="form-label">Title</label>
                                    <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($edit_section_item['title']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Subtitle (optional)</label>
                                    <input type="text" name="subtitle" class="form-control" value="<?php echo htmlspecialchars($edit_section_item['subtitle'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3" required><?php echo htmlspecialchars($edit_section_item['description']); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Icon class (Font Awesome)</label>
                                <input type="text" name="icon" class="form-control" value="<?php echo htmlspecialchars($edit_section_item['icon'] ?? 'fa-solid fa-circle-check'); ?>" placeholder="fa-solid fa-microscope">
                            </div>
                            <div class="form-group" style="display:flex; align-items:center; gap:10px;">
                                <input type="checkbox" name="is_active" value="1" <?php echo ((int) $edit_section_item['is_active'] === 1) ? 'checked' : ''; ?> style="width:20px;height:20px;">
                                <label style="margin:0;">Active</label>
                            </div>
                            <div style="display:flex; gap:10px; margin-top:16px;">
                                <button type="submit" name="update_section_item" class="btn btn-teal">Save Item</button>
                                <a href="cms.php#tab-section-items" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>

                    <div class="cms-form-box">
                        <h3 style="font-size: 1.1rem; margin-bottom: 15px; color: var(--primary);">Add Section Item</h3>
                        <form action="cms.php#tab-section-items" method="POST">
                            <?php echo csrfField(); ?>
                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label class="form-label">Section Code</label>
                                    <select name="section_code" class="form-control" required>
                                        <?php foreach ($sections as $sec): ?>
                                            <option value="<?php echo htmlspecialchars($sec['section_code']); ?>"><?php echo htmlspecialchars($sec['section_title']); ?> (<?php echo htmlspecialchars($sec['section_code']); ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Sequence</label>
                                    <input type="number" name="sequence" class="form-control" value="1" min="1" required>
                                </div>
                            </div>
                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label class="form-label">Title</label>
                                    <input type="text" name="title" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Icon class</label>
                                    <input type="text" name="icon" class="form-control" value="fa-solid fa-circle-check">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="2" required></textarea>
                            </div>
                            <button type="submit" name="add_section_item" class="btn btn-teal"><i class="fa-solid fa-plus"></i> Add Item</button>
                        </form>
                    </div>

                    <div class="admin-panel-card" style="margin-top: 24px;">
                        <div class="admin-card-header"><h2>Section Items</h2></div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Section</th>
                                        <th>Title</th>
                                        <th>Seq</th>
                                        <th>Active</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($section_items as $item): ?>
                                    <tr>
                                        <td><code><?php echo htmlspecialchars($item['section_code']); ?></code></td>
                                        <td>
                                            <i class="<?php echo htmlspecialchars($item['icon']); ?>" style="color:var(--brand-teal); margin-right:6px;"></i>
                                            <?php echo htmlspecialchars($item['title']); ?>
                                        </td>
                                        <td><?php echo (int) $item['sequence']; ?></td>
                                        <td><?php echo ((int) $item['is_active'] === 1) ? 'Yes' : 'No'; ?></td>
                                        <td>
                                            <a href="cms.php?edit_section_item=<?php echo (int) $item['id']; ?>#tab-section-items" class="action-btn" style="margin-right:8px;"><i class="fa-regular fa-pen-to-square"></i> Edit</a>
                                            <a href="cms.php?delete_type=section_item&id=<?php echo (int) $item['id']; ?>#tab-section-items" class="action-btn delete-link" onclick="return confirm('Delete this item?');"><i class="fa-solid fa-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Tab: Page Blocks (About page content, etc.) -->
                <div id="tab-page-blocks" class="cms-tab-content">
                    <?php if ($edit_page_block): ?>
                    <div class="cms-form-box" style="border:2px solid var(--brand-teal); margin-bottom: 24px;">
                        <h3 style="font-size: 1.2rem; margin-bottom: 15px; color: var(--primary);">Edit Page Block</h3>
                        <form action="cms.php?block_page=<?php echo urlencode($edit_page_block['page_slug']); ?>#tab-page-blocks" method="POST" enctype="multipart/form-data">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="block_id" value="<?php echo (int) $edit_page_block['id']; ?>">
                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label class="form-label">Block Type</label>
                                    <select name="block_type" class="form-control" required>
                                        <?php foreach (['intro','badge','header','feature','team'] as $bt): ?>
                                            <option value="<?php echo $bt; ?>" <?php echo ($edit_page_block['block_type'] === $bt) ? 'selected' : ''; ?>><?php echo ucfirst($bt); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Sequence</label>
                                    <input type="number" name="sequence" class="form-control" value="<?php echo (int) $edit_page_block['sequence']; ?>" min="1" required>
                                </div>
                            </div>
                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label class="form-label">Title</label>
                                    <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($edit_page_block['title']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Subtitle</label>
                                    <input type="text" name="subtitle" class="form-control" value="<?php echo htmlspecialchars($edit_page_block['subtitle'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Content</label>
                                <textarea name="content" class="form-control" rows="4"><?php echo htmlspecialchars($edit_page_block['content'] ?? ''); ?></textarea>
                            </div>
                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label class="form-label">Icon (feature blocks) or initials (team)</label>
                                    <input type="text" name="icon" class="form-control" value="<?php echo htmlspecialchars($edit_page_block['icon'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Replace Image (intro blocks)</label>
                                    <input type="file" name="block_image_file" class="form-control" accept="image/*" style="padding:8px;">
                                    <?php if (!empty($edit_page_block['image_path'])): ?>
                                        <small>Current: <?php echo htmlspecialchars($edit_page_block['image_path']); ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="form-group" style="display:flex; align-items:center; gap:10px;">
                                <input type="checkbox" name="is_active" value="1" <?php echo ((int) $edit_page_block['is_active'] === 1) ? 'checked' : ''; ?> style="width:20px;height:20px;">
                                <label style="margin:0;">Active</label>
                            </div>
                            <div style="display:flex; gap:10px; margin-top:16px;">
                                <button type="submit" name="update_page_block" class="btn btn-teal">Save Block</button>
                                <a href="cms.php?block_page=<?php echo urlencode($block_page_filter); ?>#tab-page-blocks" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>

                    <div class="cms-form-box">
                        <h3 style="font-size: 1.1rem; margin-bottom: 15px; color: var(--primary);">Add Page Block</h3>
                        <form action="cms.php?block_page=<?php echo urlencode($block_page_filter); ?>#tab-page-blocks" method="POST" enctype="multipart/form-data">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="page_slug" value="<?php echo htmlspecialchars($block_page_filter); ?>">
                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label class="form-label">Page</label>
                                    <select name="page_slug_select" class="form-control" disabled>
                                        <?php foreach ($site_pages as $pg): ?>
                                            <option <?php echo ($pg['slug'] === $block_page_filter) ? 'selected' : ''; ?>><?php echo htmlspecialchars($pg['slug']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Block Type</label>
                                    <select name="block_type" class="form-control" required>
                                        <option value="intro">Intro (text + image)</option>
                                        <option value="badge">Badge</option>
                                        <option value="header">Section Header</option>
                                        <option value="feature">Feature Card</option>
                                        <option value="team">Team Member</option>
                                    </select>
                                </div>
                            </div>
                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label class="form-label">Title</label>
                                    <input type="text" name="title" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Subtitle</label>
                                    <input type="text" name="subtitle" class="form-control">
                                </div>
                            </div>
                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label class="form-label">Sequence</label>
                                    <input type="number" name="sequence" class="form-control" value="99" min="1" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Icon / Initials</label>
                                    <input type="text" name="icon" class="form-control">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Content</label>
                                <textarea name="content" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Image (intro blocks)</label>
                                <input type="file" name="block_image_file" class="form-control" accept="image/*" style="padding:8px;">
                            </div>
                            <button type="submit" name="add_page_block" class="btn btn-teal"><i class="fa-solid fa-plus"></i> Add Block</button>
                        </form>
                    </div>

                    <div class="admin-panel-card" style="margin-top: 24px;">
                        <div class="admin-card-header">
                            <h2>Page Blocks</h2>
                            <form method="get" action="cms.php" style="margin-top:10px;">
                                <label class="form-label">Filter by page:</label>
                                <select name="block_page" class="form-control" style="max-width:240px; display:inline-block;" onchange="this.form.submit()">
                                    <?php foreach ($site_pages as $pg): ?>
                                        <option value="<?php echo htmlspecialchars($pg['slug']); ?>" <?php echo ($pg['slug'] === $block_page_filter) ? 'selected' : ''; ?>><?php echo htmlspecialchars($pg['page_heading']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Title</th>
                                        <th>Seq</th>
                                        <th>Active</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($page_blocks)): ?>
                                        <tr><td colspan="5" class="text-center" style="padding:24px; color:#64748b;">No blocks for this page yet.</td></tr>
                                    <?php else: ?>
                                    <?php foreach ($page_blocks as $blk): ?>
                                    <tr>
                                        <td><code><?php echo htmlspecialchars($blk['block_type']); ?></code></td>
                                        <td><?php echo htmlspecialchars($blk['title']); ?></td>
                                        <td><?php echo (int) $blk['sequence']; ?></td>
                                        <td><?php echo ((int) $blk['is_active'] === 1) ? 'Yes' : 'No'; ?></td>
                                        <td>
                                            <a href="cms.php?edit_page_block=<?php echo (int) $blk['id']; ?>&block_page=<?php echo urlencode($block_page_filter); ?>#tab-page-blocks" class="action-btn" style="margin-right:8px;"><i class="fa-regular fa-pen-to-square"></i> Edit</a>
                                            <a href="cms.php?delete_type=page_block&id=<?php echo (int) $blk['id']; ?>&block_page=<?php echo urlencode($block_page_filter); ?>#tab-page-blocks" class="action-btn" onclick="return confirm('Delete this block?');"><i class="fa-solid fa-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <?php include __DIR__ . '/includes/cms_marketing_tab.php'; ?>

                <!-- Tab 3: Pathology Tests catalog -->
                <div id="tab-services" class="cms-tab-content">
                    <?php if ($edit_service): ?>
                    <div class="cms-form-box" style="border:2px solid var(--brand-teal);">
                        <h3 style="font-size: 1.2rem; margin-bottom: 15px; color: var(--primary);">Edit Pathology Test</h3>
                        <form action="cms.php" method="POST">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="service_id" value="<?php echo (int) $edit_service['id']; ?>">
                            <div class="admin-form-row">
                                <div class="form-group"><label class="form-label">Test Title</label><input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($edit_service['title']); ?>" required></div>
                                <div class="form-group"><label class="form-label">Category</label><input type="text" name="category" class="form-control" value="<?php echo htmlspecialchars($edit_service['category']); ?>" required></div>
                            </div>
                            <div class="admin-form-row">
                                <div class="form-group"><label class="form-label">Price (INR)</label><input type="number" name="price" class="form-control" value="<?php echo htmlspecialchars($edit_service['price']); ?>" step="0.01" required></div>
                                <div class="form-group"><label class="form-label">Sequence</label><input type="number" name="sequence" class="form-control" value="<?php echo (int) $edit_service['sequence']; ?>"></div>
                            </div>
                            <div class="admin-form-row">
                                <div class="form-group"><label class="form-label">Sample Type</label><input type="text" name="sample_type" class="form-control" value="<?php echo htmlspecialchars($edit_service['sample_type']); ?>"></div>
                                <div class="form-group"><label class="form-label">Prep Instructions</label><input type="text" name="prep_instructions" class="form-control" value="<?php echo htmlspecialchars($edit_service['prep_instructions']); ?>"></div>
                            </div>
                            <div class="form-group"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"><?php echo htmlspecialchars($edit_service['description']); ?></textarea></div>
                            <button type="submit" name="update_service" class="btn btn-teal"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
                            <a href="cms.php#tab-services" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                    <?php endif; ?>
                    <!-- Add form box -->
                    <div class="cms-form-box">
                        <h3 style="font-size: 1.2rem; margin-bottom: 15px; color: var(--primary);">Add Pathology Test / Service</h3>
                        <form action="cms.php" method="POST">
                            <?php echo csrfField(); ?>
                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label class="form-label">Test Title</label>
                                    <input type="text" name="title" class="form-control" placeholder="e.g. Vitamin B12 Test" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Category</label>
                                    <input type="text" name="category" class="form-control" placeholder="e.g. HORMONE ASSAY, VITAMIN SCAN" required>
                                </div>
                            </div>
                            
                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label class="form-label">Price (INR)</label>
                                    <input type="number" name="price" class="form-control" placeholder="e.g. 599" step="0.01" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Display Sequence</label>
                                    <input type="number" name="sequence" class="form-control" value="0" min="0">
                                </div>
                            </div>

                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label class="form-label">Sample Tube Type</label>
                                    <input type="text" name="sample_type" class="form-control" placeholder="e.g. Blood (Serum), Mid-stream urine">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Pre-test Guidelines</label>
                                    <input type="text" name="prep_instructions" class="form-control" placeholder="e.g. 10-12 hours fasting required">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Test Description Details</label>
                                <textarea name="description" class="form-control" rows="2" placeholder="Explain parameters evaluated in the test..."></textarea>
                            </div>

                            <button type="submit" name="add_service" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add Test to Catalog</button>
                        </form>
                    </div>

                    <!-- Current list -->
                    <div class="admin-panel-card">
                        <div class="admin-card-header">
                            <h2>Current Pathology Services Directory</h2>
                        </div>
                        <?php foreach ($services as $srv): ?>
                            <div class="cms-item-card">
                                <div class="cms-item-details">
                                    <h4><?php echo htmlspecialchars($srv['title']); ?></h4>
                                    <span class="badge badge-new"><?php echo htmlspecialchars($srv['category']); ?></span> &nbsp;&nbsp;
                                    <strong style="color: var(--brand-blue);">₹<?php echo number_format($srv['price']); ?></strong> &nbsp;&nbsp;
                                    <span style="font-size:0.8rem; color:#64748b;">Sequence: <?php echo $srv['sequence']; ?></span>
                                    <p style="font-size:0.85rem; color:#64748b; margin-top:5px;"><?php echo htmlspecialchars($srv['description']); ?></p>
                                </div>
                                <div class="cms-item-actions">
                                    <a href="cms.php?edit_service=<?php echo $srv['id']; ?>#tab-services" class="action-btn" style="margin-right:8px;"><i class="fa-regular fa-pen-to-square"></i> Edit</a>
                                    <a href="cms.php?delete_type=service&id=<?php echo $srv['id']; ?>" onclick="return confirm('Delete this test?')" class="action-btn btn-action-delete">
                                        <i class="fa-regular fa-trash-can"></i> Delete
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Tab 4: Health Packages -->
                <div id="tab-packages" class="cms-tab-content">
                    <?php if ($edit_package): ?>
                    <div class="cms-form-box" style="border:2px solid var(--brand-teal);">
                        <h3 style="font-size: 1.2rem; margin-bottom: 15px; color: var(--primary);">Edit Health Package</h3>
                        <form action="cms.php" method="POST">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="package_id" value="<?php echo (int) $edit_package['id']; ?>">
                            <div class="admin-form-row">
                                <div class="form-group"><label class="form-label">Package Name</label><input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($edit_package['name']); ?>" required></div>
                                <div class="form-group"><label class="form-label">Price (INR)</label><input type="number" name="price" class="form-control" value="<?php echo htmlspecialchars($edit_package['price']); ?>" required></div>
                            </div>
                            <div class="admin-form-row">
                                <div class="form-group"><label class="form-label">Sequence</label><input type="number" name="sequence" class="form-control" value="<?php echo (int) $edit_package['sequence']; ?>"></div>
                                <div class="form-group" style="display:flex; align-items:center; gap:10px; padding-top:30px;">
                                    <input type="checkbox" name="is_featured" value="1" <?php echo ((int)$edit_package['is_featured'] === 1) ? 'checked' : ''; ?> style="width:20px;height:20px;">
                                    <label style="font-weight:600;">Featured Package</label>
                                </div>
                            </div>
                            <div class="form-group"><label class="form-label">Description</label><input type="text" name="description" class="form-control" value="<?php echo htmlspecialchars($edit_package['description']); ?>"></div>
                            <div class="form-group"><label class="form-label">Features</label><textarea name="features" class="form-control" rows="4"><?php echo htmlspecialchars($edit_package['features']); ?></textarea></div>
                            <button type="submit" name="update_package" class="btn btn-teal"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
                            <a href="cms.php#tab-packages" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                    <?php endif; ?>
                    <!-- Add package box -->
                    <div class="cms-form-box">
                        <h3 style="font-size: 1.2rem; margin-bottom: 15px; color: var(--primary);">Add Value Health Package</h3>
                        <form action="cms.php" method="POST">
                            <?php echo csrfField(); ?>
                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label class="form-label">Package Name</label>
                                    <input type="text" name="name" class="form-control" placeholder="e.g. Senior Citizen Package" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Price (INR)</label>
                                    <input type="number" name="price" class="form-control" placeholder="e.g. 1499" required>
                                </div>
                            </div>

                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label class="form-label">Display Sequence</label>
                                    <input type="number" name="sequence" class="form-control" value="0">
                                </div>
                                <div class="form-group" style="display:flex; align-items:center; gap: 10px; padding-top: 30px;">
                                    <input type="checkbox" name="is_featured" value="1" id="is_feat_check" style="width:20px; height:20px;">
                                    <label for="is_feat_check" style="font-weight:600; cursor:pointer;">Mark as Recommended (Featured Card)</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Description Summary</label>
                                <input type="text" name="description" class="form-control" placeholder="Brief outline of who this package is for...">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Parameters / Tests Included (Enter one parameter per line)</label>
                                <textarea name="features" class="form-control" rows="4" placeholder="e.g. Complete Blood Count (CBC)&#10;Lipid Profile&#10;Thyroid Profile (T3, T4, TSH)"></textarea>
                            </div>

                            <button type="submit" name="add_package" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Publish Package</button>
                        </form>
                    </div>

                    <!-- Current list -->
                    <div class="admin-panel-card">
                        <div class="admin-card-header">
                            <h2>Current Health Packages</h2>
                        </div>
                        <?php foreach ($packages as $pkg): ?>
                            <div class="cms-item-card">
                                <div class="cms-item-details">
                                    <h4><?php echo htmlspecialchars($pkg['name']); ?> <?php echo ((int)$pkg['is_featured'] === 1) ? '<span class="badge badge-success">Recommended</span>' : ''; ?></h4>
                                    <strong style="color: var(--brand-blue);">₹<?php echo number_format($pkg['price']); ?></strong> &nbsp;&nbsp;
                                    <span style="font-size:0.8rem; color:#64748b;">Sequence: <?php echo $pkg['sequence']; ?></span>
                                    <p style="font-size:0.85rem; color:#64748b; margin-top:5px;"><?php echo htmlspecialchars($pkg['description']); ?></p>
                                </div>
                                <div class="cms-item-actions">
                                    <a href="cms.php?edit_package=<?php echo $pkg['id']; ?>#tab-packages" class="action-btn" style="margin-right:8px;"><i class="fa-regular fa-pen-to-square"></i> Edit</a>
                                    <a href="cms.php?delete_type=package&id=<?php echo $pkg['id']; ?>" onclick="return confirm('Delete this package?')" class="action-btn btn-action-delete">
                                        <i class="fa-regular fa-trash-can"></i> Delete
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Tab 5: FAQ Accordion -->
                <div id="tab-faqs" class="cms-tab-content">
                    <?php if ($edit_faq): ?>
                    <div class="cms-form-box" style="border:2px solid var(--brand-teal);">
                        <h3 style="font-size: 1.2rem; margin-bottom: 15px; color: var(--primary);">Edit FAQ</h3>
                        <form action="cms.php" method="POST">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="faq_id" value="<?php echo (int) $edit_faq['id']; ?>">
                            <div class="form-group"><label class="form-label">Question</label><input type="text" name="question" class="form-control" value="<?php echo htmlspecialchars($edit_faq['question']); ?>" required></div>
                            <div class="form-group"><label class="form-label">Answer</label><textarea name="answer" class="form-control" rows="3" required><?php echo htmlspecialchars($edit_faq['answer']); ?></textarea></div>
                            <div class="form-group"><label class="form-label">Sequence</label><input type="number" name="sequence" class="form-control" value="<?php echo (int) $edit_faq['sequence']; ?>"></div>
                            <button type="submit" name="update_faq" class="btn btn-teal"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
                            <a href="cms.php#tab-faqs" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                    <?php endif; ?>
                    <div class="cms-form-box">
                        <h3 style="font-size: 1.2rem; margin-bottom: 15px; color: var(--primary);">Add FAQ Item</h3>
                        <form action="cms.php" method="POST">
                            <?php echo csrfField(); ?>
                            <div class="form-group">
                                <label class="form-label">Question</label>
                                <input type="text" name="question" class="form-control" required placeholder="State common patient question...">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Answer</label>
                                <textarea name="answer" class="form-control" rows="3" required placeholder="Write helpful resolution details..."></textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Display Sequence</label>
                                <input type="number" name="sequence" class="form-control" value="0">
                            </div>
                            <button type="submit" name="add_faq" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add FAQ</button>
                        </form>
                    </div>

                    <div class="admin-panel-card">
                        <div class="admin-card-header">
                            <h2>Current FAQ List</h2>
                        </div>
                        <?php foreach ($faqs as $faq): ?>
                            <div class="cms-item-card">
                                <div class="cms-item-details">
                                    <h4>Q: <?php echo htmlspecialchars($faq['question']); ?></h4>
                                    <p style="font-size:0.85rem; color:#64748b; margin-top:5px;">A: <?php echo htmlspecialchars($faq['answer']); ?></p>
                                </div>
                                <div class="cms-item-actions">
                                    <a href="cms.php?edit_faq=<?php echo $faq['id']; ?>#tab-faqs" class="action-btn" style="margin-right:8px;"><i class="fa-regular fa-pen-to-square"></i> Edit</a>
                                    <a href="cms.php?delete_type=faq&id=<?php echo $faq['id']; ?>" onclick="return confirm('Delete FAQ?')" class="action-btn btn-action-delete">
                                        <i class="fa-regular fa-trash-can"></i> Delete
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Tab 6: Testimonials -->
                <div id="tab-testimonials" class="cms-tab-content">
                    <?php if (!empty($pending_testimonials)): ?>
                    <div class="admin-panel-card" style="border: 2px solid #f59e0b; margin-bottom: 24px;">
                        <div class="admin-card-header">
                            <h2>Pending Patient Reviews (<?php echo count($pending_testimonials); ?>)</h2>
                            <p style="font-size:0.85rem; color:#64748b;">Submitted from the website — approve to publish on the homepage.</p>
                        </div>
                        <?php foreach ($pending_testimonials as $tst): ?>
                            <div class="cms-item-card" style="background:#fffbeb;">
                                <div class="cms-item-details">
                                    <p style="font-style:italic; margin-bottom:10px;">"<?php echo htmlspecialchars($tst['text']); ?>"</p>
                                    <h4>- <?php echo htmlspecialchars($tst['author']); ?> <span style="font-size:0.85rem; color:#64748b; font-weight:400;">(<?php echo htmlspecialchars($tst['designation'] ?? 'Patient'); ?>)</span></h4>
                                    <p style="font-size:0.85rem; color:#64748b; margin-top:8px;">
                                        <?php if (!empty($tst['email'])): ?>Email: <?php echo htmlspecialchars($tst['email']); ?> · <?php endif; ?>
                                        <?php if (!empty($tst['mobile'])): ?>Mobile: <?php echo htmlspecialchars($tst['mobile']); ?> · <?php endif; ?>
                                        Source: Website
                                    </p>
                                </div>
                                <div class="cms-item-actions" style="display:flex; gap:8px; flex-wrap:wrap;">
                                    <form action="cms.php#tab-testimonials" method="POST" style="display:inline;">
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="testimonial_id" value="<?php echo (int) $tst['id']; ?>">
                                        <button type="submit" name="approve_testimonial" class="btn btn-teal btn-sm"><i class="fa-solid fa-check"></i> Approve</button>
                                    </form>
                                    <form action="cms.php#tab-testimonials" method="POST" style="display:inline;">
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="testimonial_id" value="<?php echo (int) $tst['id']; ?>">
                                        <button type="submit" name="reject_testimonial" class="btn btn-secondary btn-sm"><i class="fa-solid fa-ban"></i> Reject</button>
                                    </form>
                                    <a href="cms.php?edit_testimonial=<?php echo $tst['id']; ?>#tab-testimonials" class="action-btn"><i class="fa-regular fa-pen-to-square"></i> Edit</a>
                                    <a href="cms.php?delete_type=testimonial&id=<?php echo $tst['id']; ?>" onclick="return confirm('Delete this review?')" class="action-btn btn-action-delete">
                                        <i class="fa-regular fa-trash-can"></i> Delete
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <?php if ($edit_testimonial): ?>
                    <div class="cms-form-box" style="border:2px solid var(--brand-teal);">
                        <h3 style="font-size: 1.2rem; margin-bottom: 15px; color: var(--primary);">Edit Testimonial</h3>
                        <form action="cms.php" method="POST">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="testimonial_id" value="<?php echo (int) $edit_testimonial['id']; ?>">
                            <div class="form-group"><label class="form-label">Review</label><textarea name="text" class="form-control" rows="3" required><?php echo htmlspecialchars($edit_testimonial['text']); ?></textarea></div>
                            <div class="admin-form-row">
                                <div class="form-group"><label class="form-label">Author</label><input type="text" name="author" class="form-control" value="<?php echo htmlspecialchars($edit_testimonial['author']); ?>" required></div>
                                <div class="form-group"><label class="form-label">Designation</label><input type="text" name="designation" class="form-control" value="<?php echo htmlspecialchars($edit_testimonial['designation']); ?>"></div>
                            </div>
                            <div class="form-group"><label class="form-label">Sequence</label><input type="number" name="sequence" class="form-control" value="<?php echo (int) $edit_testimonial['sequence']; ?>"></div>
                            <button type="submit" name="update_testimonial" class="btn btn-teal"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
                            <a href="cms.php#tab-testimonials" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                    <?php endif; ?>
                    <div class="cms-form-box">
                        <h3 style="font-size: 1.2rem; margin-bottom: 15px; color: var(--primary);">Add Patient Testimonial Review</h3>
                        <form action="cms.php" method="POST">
                            <?php echo csrfField(); ?>
                            <div class="form-group">
                                <label class="form-label">Review Comment</label>
                                <textarea name="text" class="form-control" rows="3" required placeholder="Insert patient review quotation..."></textarea>
                            </div>
                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label class="form-label">Patient Name</label>
                                    <input type="text" name="author" class="form-control" required placeholder="e.g. John Doe">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Designation / Tag</label>
                                    <input type="text" name="designation" class="form-control" placeholder="e.g. Patient, Maharashtra">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Display Sequence</label>
                                <input type="number" name="sequence" class="form-control" value="0">
                            </div>
                            <button type="submit" name="add_testimonial" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add Testimonial</button>
                        </form>
                    </div>

                    <div class="admin-panel-card">
                        <div class="admin-card-header">
                            <h2>Published Testimonials</h2>
                        </div>
                        <?php if (empty($published_testimonials)): ?>
                            <p style="color:#64748b; padding: 12px 0;">No published reviews yet.</p>
                        <?php endif; ?>
                        <?php foreach ($published_testimonials as $tst): ?>
                            <div class="cms-item-card">
                                <div class="cms-item-details">
                                    <p style="font-style:italic; margin-bottom:10px;">"<?php echo htmlspecialchars($tst['text']); ?>"</p>
                                    <h4>- <?php echo htmlspecialchars($tst['author']); ?> <span style="font-size:0.85rem; color:#64748b; font-weight:400;">(<?php echo htmlspecialchars($tst['designation']); ?>)</span></h4>
                                </div>
                                <div class="cms-item-actions">
                                    <a href="cms.php?edit_testimonial=<?php echo $tst['id']; ?>#tab-testimonials" class="action-btn" style="margin-right:8px;"><i class="fa-regular fa-pen-to-square"></i> Edit</a>
                                    <a href="cms.php?delete_type=testimonial&id=<?php echo $tst['id']; ?>" onclick="return confirm('Delete testimonial?')" class="action-btn btn-action-delete">
                                        <i class="fa-regular fa-trash-can"></i> Delete
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Tab: Service Locations (Local SEO) -->
                <div id="tab-locations" class="cms-tab-content">
                    <div class="admin-panel-card" style="margin-bottom: 20px;">
                        <div class="admin-card-header">
                            <h2>Service Locations</h2>
                            <p style="font-size:0.85rem; color:#64748b;">Manage city/area pages shown on <a href="../locations.php" target="_blank">locations.php</a> for local SEO and home collection.</p>
                        </div>
                    </div>

                    <?php if ($edit_location): ?>
                    <div class="cms-form-box" style="border:2px solid var(--brand-teal); margin-bottom: 24px;">
                        <h3 style="font-size: 1.2rem; margin-bottom: 15px; color: var(--primary);">Edit Location — <?php echo htmlspecialchars($edit_location['name']); ?></h3>
                        <form action="cms.php#tab-locations" method="POST">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="location_id" value="<?php echo (int) $edit_location['id']; ?>">
                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label class="form-label">City / Area Name</label>
                                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($edit_location['name']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">URL Slug</label>
                                    <input type="text" name="slug" class="form-control" value="<?php echo htmlspecialchars($edit_location['slug']); ?>" required placeholder="e.g. pune">
                                    <small style="color:#64748b;">Used in: location.php?city=<strong>slug</strong></small>
                                </div>
                            </div>
                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label class="form-label">State</label>
                                    <input type="text" name="state" class="form-control" value="<?php echo htmlspecialchars($edit_location['state'] ?? 'Maharashtra'); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Display Sequence</label>
                                    <input type="number" name="sequence" class="form-control" value="<?php echo (int) $edit_location['sequence']; ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">SEO Headline</label>
                                <input type="text" name="headline" class="form-control" value="<?php echo htmlspecialchars($edit_location['headline']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3" required><?php echo htmlspecialchars($edit_location['description']); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label">SEO Keywords</label>
                                <input type="text" name="keywords" class="form-control" value="<?php echo htmlspecialchars($edit_location['keywords'] ?? ''); ?>" placeholder="pathology lab Pune, blood test Pune">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Popular Services (one per line)</label>
                                <textarea name="services_text" class="form-control" rows="5" placeholder="CBC & hematology&#10;Thyroid profile"><?php echo htmlspecialchars($edit_location['services_text'] ?? ''); ?></textarea>
                            </div>
                            <label style="display:flex; gap:8px; align-items:center; margin-bottom:16px;">
                                <input type="checkbox" name="is_active" value="1" <?php echo (int) ($edit_location['is_active'] ?? 1) === 1 ? 'checked' : ''; ?> style="width:18px;height:18px;">
                                Show on website
                            </label>
                            <button type="submit" name="update_location" class="btn btn-teal"><i class="fa-solid fa-floppy-disk"></i> Save Location</button>
                            <a href="cms.php#tab-locations" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                    <?php endif; ?>

                    <div class="cms-form-box">
                        <h3 style="font-size: 1.2rem; margin-bottom: 15px; color: var(--primary);">Add Service Location</h3>
                        <form action="cms.php#tab-locations" method="POST">
                            <?php echo csrfField(); ?>
                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label class="form-label">City / Area Name</label>
                                    <input type="text" name="name" class="form-control" required placeholder="e.g. Pune">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">URL Slug (optional)</label>
                                    <input type="text" name="slug" class="form-control" placeholder="Auto from name if empty, e.g. pune">
                                </div>
                            </div>
                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label class="form-label">State</label>
                                    <input type="text" name="state" class="form-control" value="Maharashtra">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Display Sequence</label>
                                    <input type="number" name="sequence" class="form-control" value="0">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">SEO Headline</label>
                                <input type="text" name="headline" class="form-control" required placeholder="Pathology Lab & Home Collection in Pune">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3" required placeholder="Short description for the location landing page..."></textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label">SEO Keywords</label>
                                <input type="text" name="keywords" class="form-control" placeholder="pathology lab Pune, blood test Pune">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Popular Services (one per line)</label>
                                <textarea name="services_text" class="form-control" rows="5" placeholder="CBC & hematology&#10;Home sample collection"></textarea>
                            </div>
                            <label style="display:flex; gap:8px; align-items:center; margin-bottom:16px;">
                                <input type="checkbox" name="is_active" value="1" checked style="width:18px;height:18px;">
                                Show on website
                            </label>
                            <button type="submit" name="add_location" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add Location</button>
                        </form>
                    </div>

                    <div class="admin-panel-card" style="margin-top: 24px;">
                        <div class="admin-card-header">
                            <h2>Current Locations (<?php echo count($locations); ?>)</h2>
                        </div>
                        <?php if (empty($locations)): ?>
                            <p style="color:#64748b; padding:12px 0;">No locations yet. Add your first city above, or reload the page to import defaults.</p>
                        <?php endif; ?>
                        <?php foreach ($locations as $loc): ?>
                            <div class="cms-item-card">
                                <div class="cms-item-details">
                                    <h4><?php echo htmlspecialchars($loc['name']); ?>
                                        <?php if ((int) ($loc['is_active'] ?? 1) !== 1): ?>
                                            <span class="badge badge-pending" style="font-size:0.75rem; margin-left:8px;">Hidden</span>
                                        <?php endif; ?>
                                    </h4>
                                    <p style="font-size:0.85rem; color:#64748b; margin:6px 0;">Slug: <code><?php echo htmlspecialchars($loc['slug']); ?></code> · Seq: <?php echo (int) $loc['sequence']; ?></p>
                                    <p style="font-style:italic; margin-bottom:6px;"><?php echo htmlspecialchars($loc['headline']); ?></p>
                                    <p style="font-size:0.9rem; color:#475569;"><?php echo htmlspecialchars(strlen($loc['description']) > 120 ? substr($loc['description'], 0, 120) . '…' : $loc['description']); ?></p>
                                </div>
                                <div class="cms-item-actions">
                                    <a href="../location.php?city=<?php echo urlencode($loc['slug']); ?>" target="_blank" class="action-btn" style="margin-right:8px;"><i class="fa-solid fa-arrow-up-right-from-square"></i> View</a>
                                    <a href="cms.php?edit_location=<?php echo (int) $loc['id']; ?>#tab-locations" class="action-btn" style="margin-right:8px;"><i class="fa-regular fa-pen-to-square"></i> Edit</a>
                                    <a href="cms.php?delete_type=location&id=<?php echo (int) $loc['id']; ?>" onclick="return confirm('Delete this location?')" class="action-btn btn-action-delete">
                                        <i class="fa-regular fa-trash-can"></i> Delete
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Tab 7: Gallery & Equipment -->
                <div id="tab-media" class="cms-tab-content">
                    <!-- 7.1 Gallery Upload -->
                    <div class="cms-form-box">
                        <h3 style="font-size: 1.2rem; margin-bottom: 15px; color: var(--primary);">Add Laboratory Gallery Photo</h3>
                        <form action="cms.php" method="POST" enctype="multipart/form-data">
                            <?php echo csrfField(); ?>
                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label class="form-label">Slide Title</label>
                                    <input type="text" name="title" class="form-control" required placeholder="e.g. Blood Collection Station">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Select Photo File</label>
                                    <input type="file" name="gallery_file" class="form-control" required accept="image/*" style="padding:8px;">
                                </div>
                            </div>
                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label class="form-label">Slide Short Description</label>
                                    <input type="text" name="description" class="form-control" placeholder="Brief overlay text...">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Display Sequence</label>
                                    <input type="number" name="sequence" class="form-control" value="0">
                                </div>
                            </div>
                            <button type="submit" name="add_gallery" class="btn btn-primary"><i class="fa-solid fa-cloud-arrow-up"></i> Upload to Gallery</button>
                        </form>
                    </div>

                    <!-- 7.2 Equipment Upload -->
                    <div class="cms-form-box">
                        <h3 style="font-size: 1.2rem; margin-bottom: 15px; color: var(--primary);">Add Advanced Laboratory Equipment</h3>
                        <form action="cms.php" method="POST" enctype="multipart/form-data">
                            <?php echo csrfField(); ?>
                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label class="form-label">Machine Name</label>
                                    <input type="text" name="title" class="form-control" required placeholder="e.g. Chemistry Analyzer">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Select Machine Photo</label>
                                    <input type="file" name="equipment_file" class="form-control" required accept="image/*" style="padding:8px;">
                                </div>
                            </div>
                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label class="form-label">Machine Description</label>
                                    <input type="text" name="description" class="form-control" placeholder="State analyzer technology parameters...">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Display Sequence</label>
                                    <input type="number" name="sequence" class="form-control" value="0">
                                </div>
                            </div>
                            <button type="submit" name="add_equipment" class="btn btn-primary"><i class="fa-solid fa-cloud-arrow-up"></i> Add Equipment profile</button>
                        </form>
                    </div>

                    <!-- Display listings side by side or sequentially -->
                    <div class="grid-2">
                        <div class="admin-panel-card" style="padding: 15px;">
                            <h3 style="font-size: 1.1rem; margin-bottom: 15px; border-bottom: 1px solid var(--border); padding-bottom: 8px;">Active Gallery Items</h3>
                            <?php foreach ($gallery as $g_item): ?>
                                <div class="cms-item-card" style="padding:10px;">
                                    <img src="../<?php echo htmlspecialchars($g_item['image_path']); ?>" class="img-preview-badge" style="width:50px; height:50px;">
                                    <div class="cms-item-details">
                                        <h5 style="margin:0; font-size:0.95rem;"><?php echo htmlspecialchars($g_item['title']); ?></h5>
                                    </div>
                                    <a href="cms.php?delete_type=gallery&id=<?php echo $g_item['id']; ?>" onclick="return confirm('Delete gallery image?')" class="action-btn btn-action-delete" style="padding: 4px 8px;">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="admin-panel-card" style="padding: 15px;">
                            <h3 style="font-size: 1.1rem; margin-bottom: 15px; border-bottom: 1px solid var(--border); padding-bottom: 8px;">Equipment Profiles</h3>
                            <?php foreach ($equipment as $e_item): ?>
                                <div class="cms-item-card" style="padding:10px;">
                                    <img src="../<?php echo htmlspecialchars($e_item['image_path']); ?>" class="img-preview-badge" style="width:50px; height:50px;">
                                    <div class="cms-item-details">
                                        <h5 style="margin:0; font-size:0.95rem;"><?php echo htmlspecialchars($e_item['title']); ?></h5>
                                    </div>
                                    <a href="cms.php?delete_type=equipment&id=<?php echo $e_item['id']; ?>" onclick="return confirm('Delete equipment profile?')" class="action-btn btn-action-delete" style="padding: 4px 8px;">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Tab 8: Blog articles -->
                <div id="tab-blog" class="cms-tab-content">
                    <?php if ($edit_blog): ?>
                    <div class="cms-form-box" style="border:2px solid var(--brand-teal);">
                        <h3 style="font-size: 1.2rem; margin-bottom: 15px; color: var(--primary);">Edit Blog Article</h3>
                        <form action="cms.php" method="POST" enctype="multipart/form-data">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="blog_id" value="<?php echo (int) $edit_blog['id']; ?>">
                            <div class="admin-form-row">
                                <div class="form-group"><label class="form-label">Title</label><input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($edit_blog['title']); ?>" required></div>
                                <div class="form-group"><label class="form-label">Author</label><input type="text" name="author" class="form-control" value="<?php echo htmlspecialchars($edit_blog['author']); ?>" required></div>
                            </div>
                            <div class="form-group"><label class="form-label">Replace Banner Image (optional)</label><input type="file" name="blog_file" class="form-control" accept="image/*" style="padding:8px;"></div>
                            <div class="form-group"><label class="form-label">Summary</label><input type="text" name="summary" class="form-control" value="<?php echo htmlspecialchars($edit_blog['summary']); ?>" required></div>
                            <div class="form-group"><label class="form-label">Content</label><textarea name="content" class="form-control" rows="6" required><?php echo htmlspecialchars($edit_blog['content']); ?></textarea></div>
                            <button type="submit" name="update_blog" class="btn btn-teal"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
                            <a href="cms.php#tab-blog" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                    <?php endif; ?>
                    <div class="cms-form-box">
                        <h3 style="font-size: 1.2rem; margin-bottom: 15px; color: var(--primary);">Publish Health Blog Article</h3>
                        <form action="cms.php" method="POST" enctype="multipart/form-data">
                            <?php echo csrfField(); ?>
                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label class="form-label">Article Title</label>
                                    <input type="text" name="title" class="form-control" required placeholder="e.g. Deciphering thyroid profile readings">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Author Name</label>
                                    <input type="text" name="author" class="form-control" value="Dr. Sunita Verma" required>
                                </div>
                            </div>
                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label class="form-label">Select Banner Image</label>
                                    <input type="file" name="blog_file" class="form-control" required accept="image/*" style="padding:8px;">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Brief Summary (For index listings)</label>
                                    <input type="text" name="summary" class="form-control" placeholder="A one-sentence summary hook..." required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Full Article Content</label>
                                <textarea name="content" class="form-control" rows="6" placeholder="Write full text details here..." required></textarea>
                            </div>
                            <button type="submit" name="add_blog" class="btn btn-primary"><i class="fa-solid fa-pen-nib"></i> Publish Article</button>
                        </form>
                    </div>

                    <div class="admin-panel-card">
                        <div class="admin-card-header">
                            <h2>Published Blog Articles</h2>
                        </div>
                        <?php foreach ($blogs as $post): ?>
                            <div class="cms-item-card">
                                <img src="../<?php echo htmlspecialchars($post['image_path']); ?>" class="img-preview-badge" style="width:60px; height:60px;">
                                <div class="cms-item-details">
                                    <h4><?php echo htmlspecialchars($post['title']); ?></h4>
                                    <span style="font-size:0.85rem; color:#64748b;">Author: <?php echo htmlspecialchars($post['author']); ?> | Published: <?php echo date('d M Y', strtotime($post['created_at'])); ?></span>
                                    <p style="font-size:0.85rem; color:#64748b; margin-top:5px;"><?php echo htmlspecialchars($post['summary']); ?></p>
                                </div>
                                <div class="cms-item-actions">
                                    <a href="cms.php?edit_blog=<?php echo $post['id']; ?>#tab-blog" class="action-btn" style="margin-right:8px;"><i class="fa-regular fa-pen-to-square"></i> Edit</a>
                                    <a href="cms.php?delete_type=blog&id=<?php echo $post['id']; ?>" onclick="return confirm('Delete this blog post?')" class="action-btn btn-action-delete">
                                        <i class="fa-regular fa-trash-can"></i> Delete
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Tab 9: Navigation Menu & page sequence -->
                <div id="tab-menu" class="cms-tab-content">
                    <!-- Add custom link box -->
                    <div class="cms-form-box">
                        <h3 style="font-size: 1.2rem; margin-bottom: 15px; color: var(--primary);">Add Custom Header Link / Page</h3>
                        <form action="cms.php" method="POST">
                            <?php echo csrfField(); ?>
                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label class="form-label">Link Label / Title</label>
                                    <input type="text" name="menu_title" class="form-control" placeholder="e.g. Services" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Link Destination URL</label>
                                    <input type="text" name="menu_url" class="form-control" placeholder="e.g. services.php" required>
                                </div>
                            </div>
                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label class="form-label">Display Sequence</label>
                                    <input type="number" name="menu_sequence" class="form-control" value="0">
                                </div>
                                <div class="form-group" style="display:flex; align-items:center; gap: 10px; padding-top: 30px;">
                                    <input type="checkbox" name="menu_is_cta" value="1" id="menu_is_cta_add" style="width:20px; height:20px;">
                                    <label for="menu_is_cta_add" style="font-weight:600; cursor:pointer;">Style as CTA Accent Button</label>
                                </div>
                            </div>
                            <button type="submit" name="add_menu_item" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add Link</button>
                        </form>
                    </div>

                    <!-- Manage current menu list -->
                    <div class="admin-panel-card">
                        <div class="admin-card-header">
                            <h2>Manage Header Menu Navigation Sequence</h2>
                            <p style="font-size:0.85rem; color:#64748b;">Sequence navigation elements. Mark active links or CTAs accordingly.</p>
                        </div>
                        <form action="cms.php" method="POST">
                            <?php echo csrfField(); ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th style="width: 80px;">Active</th>
                                            <th>Navigation Title</th>
                                            <th>URL Destination</th>
                                            <th style="width: 110px;">Sequence</th>
                                            <th style="width: 100px;">Style CTA</th>
                                            <th style="width: 80px;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($menu_items_admin as $item): ?>
                                            <tr>
                                                <td class="text-center">
                                                    <input type="checkbox" name="menu_active[<?php echo $item['id']; ?>]" value="1" <?php echo ((int)$item['is_active'] === 1) ? 'checked' : ''; ?> style="width: 18px; height: 18px; cursor: pointer;">
                                                </td>
                                                <td>
                                                    <input type="text" name="menu_title[<?php echo $item['id']; ?>]" class="form-control" value="<?php echo htmlspecialchars($item['title']); ?>" required style="padding: 6px;">
                                                </td>
                                                <td>
                                                    <input type="text" name="menu_url[<?php echo $item['id']; ?>]" class="form-control" value="<?php echo htmlspecialchars($item['url']); ?>" required style="padding: 6px;">
                                                </td>
                                                <td>
                                                    <input type="number" name="menu_sequence[<?php echo $item['id']; ?>]" class="form-control" value="<?php echo $item['sequence']; ?>" min="1" max="99" required style="padding: 6px; text-align: center;">
                                                </td>
                                                <td class="text-center">
                                                    <input type="checkbox" name="menu_cta[<?php echo $item['id']; ?>]" value="1" <?php echo ((int)$item['is_cta'] === 1) ? 'checked' : ''; ?> style="width: 18px; height: 18px; cursor: pointer;">
                                                </td>
                                                <td class="text-center">
                                                    <a href="cms.php?delete_type=menu&id=<?php echo $item['id']; ?>" onclick="return confirm('Remove this navigation link?')" class="action-btn btn-action-delete" style="padding: 4px 8px; font-size: 0.8rem;">
                                                        <i class="fa-regular fa-trash-can"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <button type="submit" name="update_menu_items" class="btn btn-teal w-full" style="margin-top: 20px;">
                                <i class="fa-solid fa-floppy-disk"></i> Update Navigation Sequences
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Client-side Tab Switcher Script -->
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const tabs = document.querySelectorAll('.cms-tab-btn');
        const contents = document.querySelectorAll('.cms-tab-content');

        function activateTab(targetTab) {
            tabs.forEach(btn => btn.classList.toggle('active', btn.getAttribute('data-tab') === targetTab));
            contents.forEach(content => content.classList.toggle('active', content.id === targetTab));
        }
        
        tabs.forEach(tab => {
            tab.addEventListener('click', () => activateTab(tab.getAttribute('data-tab')));
        });

        if (location.hash && document.querySelector(location.hash)) {
            activateTab(location.hash.replace('#', ''));
        }
    });
    </script>
</body>
</html>
