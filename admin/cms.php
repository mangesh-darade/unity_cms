<?php
include '../includes/db.php';
include '../includes/auth.php';
requireAdmin();

$msg = '';

// Helper to handle image uploads to images/ directory
function handleCMSImageUpload($file_field) {
    if (isset($_FILES[$file_field]) && $_FILES[$file_field]['error'] === 0) {
        $file = $_FILES[$file_field];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
        
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
    
    // 1. General Content Settings
    if (isset($_POST['update_general'])) {
        try {
            $stmt = $db->prepare("UPDATE cms_settings SET value = :value WHERE key = :key");
            
            $keys = [
                'site_name', 'logo_text', 'support_phone', 'support_email', 
                'support_address', 'whatsapp_number', 'hero_tagline', 
                'hero_headline', 'hero_subheadline', 'maps_embed_url',
                'logo_type', 'logo_icon', 'top_offer_text', 'footer_about',
                'working_hours_weekday', 'working_hours_sunday', 'footer_copyright'
            ];
            
            // Check if user uploaded a new hero background image
            $new_hero_bg = handleCMSImageUpload('hero_bg_image_file');
            if (!empty($new_hero_bg)) {
                $stmt->execute([':key' => 'hero_bg_image', ':value' => $new_hero_bg]);
            }

            // Check if user uploaded a new logo image
            $new_logo_img = handleCMSImageUpload('logo_image_file');
            if (!empty($new_logo_img)) {
                $stmt->execute([':key' => 'logo_image', ':value' => $new_logo_img]);
            }
            
            foreach ($keys as $k) {
                if (isset($_POST[$k])) {
                    $stmt->execute([':key' => $k, ':value' => trim($_POST[$k])]);
                }
            }

            // Checkbox for banner active status
            $top_offer_active = isset($_POST['top_offer_active']) ? '1' : '0';
            $stmt->execute([':key' => 'top_offer_active', ':value' => $top_offer_active]);
            
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
    
    // 2. Sections Order & Active status
    if (isset($_POST['update_sections'])) {
        try {
            $db->beginTransaction();
            $stmt = $db->prepare("UPDATE cms_sections SET sequence = :seq, is_active = :active WHERE id = :id");
            
            foreach ($_POST['sequence'] as $id => $seq) {
                $active = isset($_POST['is_active'][$id]) ? 1 : 0;
                $stmt->execute([
                    ':seq' => (int)$seq,
                    ':active' => $active,
                    ':id' => (int)$id
                ]);
            }
            $db->commit();
            $msg = '<div class="alert alert-success">Homepage section sequence and active statuses saved successfully!</div>';
        } catch (PDOException $e) {
            $db->rollBack();
            $msg = '<div class="alert alert-error">Database error: ' . $e->getMessage() . '</div>';
        }
    }
    
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
            $stmt = $db->prepare("INSERT INTO cms_testimonials (text, author, designation, sequence) VALUES (?, ?, ?, ?)");
            $stmt->execute([$text, $author, $desig, $seq]);
            $msg = '<div class="alert alert-success">Testimonial card added.</div>';
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
        }
    } catch (PDOException $e) {
        $msg = '<div class="alert alert-error">Database deletion error: ' . $e->getMessage() . '</div>';
    }
}

// ==========================================
// QUERY CURRENT RECORDS
// ==========================================
$sections = $db->query("SELECT * FROM cms_sections ORDER BY sequence ASC")->fetchAll();
$services = $db->query("SELECT * FROM cms_services ORDER BY sequence ASC")->fetchAll();
$packages = $db->query("SELECT * FROM cms_packages ORDER BY sequence ASC")->fetchAll();
$faqs = $db->query("SELECT * FROM cms_faqs ORDER BY sequence ASC")->fetchAll();
$testimonials = $db->query("SELECT * FROM cms_testimonials ORDER BY sequence ASC")->fetchAll();
$gallery = $db->query("SELECT * FROM cms_gallery ORDER BY sequence ASC")->fetchAll();
$equipment = $db->query("SELECT * FROM cms_equipment ORDER BY sequence ASC")->fetchAll();
$blogs = $db->query("SELECT * FROM cms_blogs ORDER BY id DESC")->fetchAll();
$menu_items_admin = $db->query("SELECT * FROM cms_menu ORDER BY sequence ASC")->fetchAll();
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

    <!-- Admin Sidebar -->
    <div class="admin-sidebar">
        <div class="admin-sidebar-header">
            <div class="logo-icon" style="width: 32px; height: 32px; font-size: 1.1rem;"><i class="fa-solid fa-flask"></i></div>
            <span style="font-family: 'Outfit', sans-serif; font-size: 1.25rem; font-weight: 700; color: #ffffff;">Unity Lab Admin</span>
        </div>
        
        <ul class="admin-menu">
            <li class="admin-menu-item"><a href="index.php"><i class="fa-solid fa-chart-line"></i> <span>Dashboard</span></a></li>
            <li class="admin-menu-item"><a href="bookings.php"><i class="fa-solid fa-calendar-check"></i> <span>Bookings</span></a></li>
            <li class="admin-menu-item"><a href="patients.php"><i class="fa-solid fa-users"></i> <span>Patients</span></a></li>
            <li class="admin-menu-item"><a href="reports.php"><i class="fa-solid fa-file-pdf"></i> <span>Upload Reports</span></a></li>
            <li class="admin-menu-item"><a href="inquiries.php"><i class="fa-solid fa-envelope-open-text"></i> <span>Inquiries</span></a></li>
            <li class="admin-menu-item active"><a href="cms.php"><i class="fa-solid fa-file-pen"></i> <span>CMS Settings</span></a></li>
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
                <li><button class="cms-tab-btn" data-tab="tab-sections"><i class="fa-solid fa-list-ol"></i> Home Sections</button></li>
                <li><button class="cms-tab-btn" data-tab="tab-services"><i class="fa-solid fa-microscope"></i> Pathology Tests</button></li>
                <li><button class="cms-tab-btn" data-tab="tab-packages"><i class="fa-solid fa-box-tissue"></i> Health Packages</button></li>
                <li><button class="cms-tab-btn" data-tab="tab-faqs"><i class="fa-solid fa-circle-question"></i> FAQ Accordion</button></li>
                <li><button class="cms-tab-btn" data-tab="tab-testimonials"><i class="fa-solid fa-star"></i> Testimonials</button></li>
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
                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label class="form-label">Business Name</label>
                                    <input type="text" name="site_name" class="form-control" value="<?php echo htmlspecialchars($cms['site_name']); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Logo Brand Text (Fallback)</label>
                                    <input type="text" name="logo_text" class="form-control" value="<?php echo htmlspecialchars($cms['logo_text']); ?>">
                                </div>
                            </div>

                            <div class="admin-form-row" style="background-color: #f8fafc; padding: 15px; border-radius: 6px; margin: 15px 0; border: 1px solid #e2e8f0;">
                                <div class="form-group">
                                    <label class="form-label" style="font-weight: 700; color: var(--primary);">Logo Display Type</label>
                                    <div style="display: flex; gap: 20px; margin-top: 8px;">
                                        <label style="cursor: pointer; display: flex; align-items: center; gap: 5px;">
                                            <input type="radio" name="logo_type" value="text" <?php echo ($cms['logo_type'] ?? 'text') === 'text' ? 'checked' : ''; ?>> Text Brand Logo
                                        </label>
                                        <label style="cursor: pointer; display: flex; align-items: center; gap: 5px;">
                                            <input type="radio" name="logo_type" value="image" <?php echo ($cms['logo_type'] ?? 'text') === 'image' ? 'checked' : ''; ?>> Image Logo Upload
                                        </label>
                                        <label style="cursor: pointer; display: flex; align-items: center; gap: 5px;">
                                            <input type="radio" name="logo_type" value="icon" <?php echo ($cms['logo_type'] ?? 'text') === 'icon' ? 'checked' : ''; ?>> Medical Icon + Text
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Select Medical Icon (For 'Medical Icon' option)</label>
                                    <select name="logo_icon" class="form-control">
                                        <option value="fa-solid fa-flask" <?php echo ($cms['logo_icon'] ?? '') === 'fa-solid fa-flask' ? 'selected' : ''; ?>>Flask (Flask Icon)</option>
                                        <option value="fa-solid fa-heart-pulse" <?php echo ($cms['logo_icon'] ?? '') === 'fa-solid fa-heart-pulse' ? 'selected' : ''; ?>>Heart Pulse (Heart Rate)</option>
                                        <option value="fa-solid fa-house-medical" <?php echo ($cms['logo_icon'] ?? '') === 'fa-solid fa-house-medical' ? 'selected' : ''; ?>>Medical Clinic House</option>
                                        <option value="fa-solid fa-stethoscope" <?php echo ($cms['logo_icon'] ?? '') === 'fa-solid fa-stethoscope' ? 'selected' : ''; ?>>Stethoscope</option>
                                        <option value="fa-solid fa-droplet" <?php echo ($cms['logo_icon'] ?? '') === 'fa-solid fa-droplet' ? 'selected' : ''; ?>>Blood Droplet</option>
                                        <option value="fa-solid fa-microscope" <?php echo ($cms['logo_icon'] ?? '') === 'fa-solid fa-microscope' ? 'selected' : ''; ?>>Microscope</option>
                                    </select>
                                </div>
                            </div>

                            <div class="admin-form-row align-center" style="background-color: #f8fafc; padding: 15px; border-radius: 6px; margin: 15px 0; border: 1px solid #e2e8f0;">
                                <div class="form-group">
                                    <label class="form-label">Upload Custom Image Logo (Replaces text brand logo)</label>
                                    <input type="file" name="logo_image_file" class="form-control" accept="image/*" style="padding: 8px;">
                                    <small style="color: #64748b;">Recommended height: 45px (transparent PNG preferred).</small>
                                </div>
                                <div>
                                    <label class="form-label">Current Logo Image Preview</label>
                                    <?php if (!empty($cms['logo_image'])): ?>
                                        <img src="../<?php echo htmlspecialchars($cms['logo_image']); ?>" class="img-preview-badge" style="width:140px; height: 50px; object-fit: contain;">
                                    <?php else: ?>
                                        <span style="font-size: 0.85rem; color: #94a3b8;">No Image Uploaded</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label class="form-label">Support Telephone</label>
                                    <input type="text" name="support_phone" class="form-control" value="<?php echo htmlspecialchars($cms['support_phone']); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Support Email Address</label>
                                    <input type="email" name="support_email" class="form-control" value="<?php echo htmlspecialchars($cms['support_email']); ?>">
                                </div>
                            </div>

                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label class="form-label">WhatsApp Contact Number (Prefix country code, no space)</label>
                                    <input type="text" name="whatsapp_number" class="form-control" value="<?php echo htmlspecialchars($cms['whatsapp_number']); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Google Maps Embed URL</label>
                                    <input type="text" name="maps_embed_url" class="form-control" value="<?php echo htmlspecialchars($cms['maps_embed_url']); ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Physical Address</label>
                                <textarea name="support_address" class="form-control" rows="2"><?php echo htmlspecialchars($cms['support_address']); ?></textarea>
                            </div>

                            <div style="border-top: 1px solid var(--border); margin-top: 20px; padding-top: 20px; background-color: #f0fdfa; padding: 15px; border-radius: 6px; border: 1px solid #ccfbf1; margin-bottom: 20px;">
                                <h3 style="font-size: 1.1rem; margin-bottom: 15px; color: var(--primary);"><i class="fa-solid fa-bullhorn"></i> Header Announcement & Offer Banner</h3>
                                
                                <div class="form-group" style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                                    <input type="checkbox" name="top_offer_active" value="1" id="top_offer_active_check" <?php echo ($cms['top_offer_active'] ?? '0') === '1' ? 'checked' : ''; ?> style="width: 20px; height: 20px; cursor: pointer;">
                                    <label for="top_offer_active_check" style="font-weight: 600; cursor: pointer;">Enable top offer announcement bar above menu</label>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Announcement Banner Text</label>
                                    <input type="text" name="top_offer_text" class="form-control" value="<?php echo htmlspecialchars($cms['top_offer_text'] ?? ''); ?>" placeholder="e.g. Free Sugar Test with Health Package today!">
                                </div>
                            </div>

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
                            </div>

                            <div style="border-top: 1px solid var(--border); margin-top: 20px; padding-top: 20px;">
                                <h3 style="font-size: 1.1rem; margin-bottom: 15px; color: var(--primary);">Footer Details & Timings</h3>
                                
                                <div class="form-group">
                                    <label class="form-label">Footer About Description</label>
                                    <textarea name="footer_about" class="form-control" rows="2" required><?php echo htmlspecialchars($cms['footer_about'] ?? ''); ?></textarea>
                                </div>

                                <div class="admin-form-row">
                                    <div class="form-group">
                                        <label class="form-label">Weekday Working Hours (Mon - Sat)</label>
                                        <input type="text" name="working_hours_weekday" class="form-control" value="<?php echo htmlspecialchars($cms['working_hours_weekday'] ?? ''); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Sunday Working Hours</label>
                                        <input type="text" name="working_hours_sunday" class="form-control" value="<?php echo htmlspecialchars($cms['working_hours_sunday'] ?? ''); ?>" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Footer Copyright Notice</label>
                                    <input type="text" name="footer_copyright" class="form-control" value="<?php echo htmlspecialchars($cms['footer_copyright'] ?? ''); ?>" required>
                                </div>
                            </div>

                            <button type="submit" name="update_general" class="btn btn-teal w-full" style="margin-top: 20px;">
                                <i class="fa-solid fa-circle-check"></i> Save General Configurations
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Tab 2: Homepage Sections Ordering -->
                <div id="tab-sections" class="cms-tab-content">
                    <div class="admin-panel-card">
                        <div class="admin-card-header">
                            <h2>Home Sections Sequences & Ordering</h2>
                            <p style="font-size:0.85rem; color:#64748b;">Set sequence orders and toggle display active checks.</p>
                        </div>
                        
                        <form action="cms.php" method="POST">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th style="width: 80px;">Active</th>
                                            <th>Homepage Section Block</th>
                                            <th style="width: 150px;">Display Sequence</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($sections as $sec): ?>
                                            <tr>
                                                <td class="text-center">
                                                    <input type="checkbox" name="is_active[<?php echo $sec['id']; ?>]" value="1" <?php echo ((int)$sec['is_active'] === 1) ? 'checked' : ''; ?> style="width: 20px; height: 20px; cursor: pointer;">
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($sec['section_title']); ?></strong><br>
                                                    <span style="font-size:0.8rem; color:#64748b;">Identifier: <code>includes/sections/<?php echo htmlspecialchars($sec['section_code']); ?>.php</code></span>
                                                </td>
                                                <td>
                                                    <input type="number" name="sequence[<?php echo $sec['id']; ?>]" class="form-control" value="<?php echo $sec['sequence']; ?>" min="1" max="99" required style="padding: 6px; text-align: center;">
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <button type="submit" name="update_sections" class="btn btn-teal w-full" style="margin-top: 20px;">
                                <i class="fa-solid fa-list-ol"></i> Save Display Sequence Layout
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Tab 3: Pathology Tests catalog -->
                <div id="tab-services" class="cms-tab-content">
                    <!-- Add form box -->
                    <div class="cms-form-box">
                        <h3 style="font-size: 1.2rem; margin-bottom: 15px; color: var(--primary);">Add Pathology Test / Service</h3>
                        <form action="cms.php" method="POST">
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
                    <!-- Add package box -->
                    <div class="cms-form-box">
                        <h3 style="font-size: 1.2rem; margin-bottom: 15px; color: var(--primary);">Add Value Health Package</h3>
                        <form action="cms.php" method="POST">
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
                    <div class="cms-form-box">
                        <h3 style="font-size: 1.2rem; margin-bottom: 15px; color: var(--primary);">Add FAQ Item</h3>
                        <form action="cms.php" method="POST">
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
                    <div class="cms-form-box">
                        <h3 style="font-size: 1.2rem; margin-bottom: 15px; color: var(--primary);">Add Patient Testimonial Review</h3>
                        <form action="cms.php" method="POST">
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
                                    <input type="text" name="designation" class="form-control" placeholder="e.g. Patient, Gurugram">
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
                            <h2>Current Testimonials</h2>
                        </div>
                        <?php foreach ($testimonials as $tst): ?>
                            <div class="cms-item-card">
                                <div class="cms-item-details">
                                    <p style="font-style:italic; margin-bottom:10px;">"<?php echo htmlspecialchars($tst['text']); ?>"</p>
                                    <h4>- <?php echo htmlspecialchars($tst['author']); ?> <span style="font-size:0.85rem; color:#64748b; font-weight:400;">(<?php echo htmlspecialchars($tst['designation']); ?>)</span></h4>
                                </div>
                                <div class="cms-item-actions">
                                    <a href="cms.php?delete_type=testimonial&id=<?php echo $tst['id']; ?>" onclick="return confirm('Delete testimonial?')" class="action-btn btn-action-delete">
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
                    <div class="cms-form-box">
                        <h3 style="font-size: 1.2rem; margin-bottom: 15px; color: var(--primary);">Publish Health Blog Article</h3>
                        <form action="cms.php" method="POST" enctype="multipart/form-data">
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
        
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const targetTab = tab.getAttribute('data-tab');
                
                // Set active tab button
                tabs.forEach(btn => btn.classList.remove('active'));
                tab.classList.add('active');
                
                // Show corresponding tab content
                contents.forEach(content => {
                    if (content.id === targetTab) {
                        content.classList.add('active');
                    } else {
                        content.classList.remove('active');
                    }
                });
            });
        });
    });
    </script>
</body>
</html>
