<?php
// Fetch services from database ordered by sequence
$home_services = $db->query("SELECT * FROM cms_services ORDER BY sequence ASC LIMIT 9")->fetchAll();

// Icon mapping helper based on category/title
function getServiceIconClass($category, $title) {
    $title_lower = strtolower($title);
    $cat_lower = strtolower($category);
    
    if (strpos($title_lower, 'blood') !== false || strpos($title_lower, 'cbc') !== false) {
        return 'fa-droplet';
    } elseif (strpos($title_lower, 'urine') !== false) {
        return 'fa-vial';
    } elseif ($cat_lower === 'diabetes panel' || strpos($title_lower, 'sugar') !== false || strpos($title_lower, 'glucose') !== false || strpos($title_lower, 'hba1c') !== false) {
        return 'fa-heart-pulse';
    } elseif (strpos($title_lower, 'thyroid') !== false) {
        return 'fa-gauge-high';
    } elseif (strpos($title_lower, 'liver') !== false || strpos($title_lower, 'lft') !== false) {
        return 'fa-lungs';
    } elseif (strpos($title_lower, 'kidney') !== false || strpos($title_lower, 'kft') !== false || strpos($title_lower, 'renal') !== false) {
        return 'fa-notes-medical';
    } elseif ($cat_lower === 'biochemistry') {
        return 'fa-dna';
    } elseif (strpos($title_lower, 'vitamin') !== false) {
        return 'fa-circle-h';
    }
    return 'fa-microscope';
}
?>
<!-- Our Services Section -->
<section class="services-section section-padding">
    <div class="container">
        <?php renderSectionHeader($cms_sections, 'services', [
            'tag' => 'Specializations',
            'title' => 'Our Pathology & Diagnostic Services',
            'desc' => 'Comprehensive laboratory services ranging from basic blood checks to advanced organic biochemistry panels.',
        ]); ?>
        <div class="grid-3 reveal-stagger">
            <?php foreach ($home_services as $service):
                $icon = getServiceIconClass($service['category'], $service['title']);
            ?>
                <div class="card service-card">
                    <span class="service-category"><?php echo htmlspecialchars($service['category']); ?></span>
                    <div class="service-icon"><i class="fa-solid <?php echo $icon; ?>"></i></div>
                    <h3><?php echo htmlspecialchars($service['title']); ?></h3>
                    <p><?php echo htmlspecialchars(substr($service['description'] ?? '', 0, 100)) . (strlen($service['description'] ?? '') > 100 ? '...' : ''); ?></p>
                    <div class="service-card-footer">
                        <span class="service-price">From ₹<?php echo number_format((float) $service['price']); ?></span>
                        <a href="services.php" class="service-link">View Details <i class="fa-solid fa-arrow-right"></i></a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center section-cta-wrap">
            <a href="services.php" class="btn btn-teal btn-lg">Browse All Pathology Tests <i class="fa-solid fa-arrow-right"></i></a>
        </div>
    </div>
</section>
