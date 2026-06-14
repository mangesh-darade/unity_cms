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
        return 'fa-kidney-beans" style="font-family: \'Font Awesome 6 Free\'; font-weight: 900;';
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
        <div class="section-header">
            <span class="section-tag">Specializations</span>
            <h2 class="section-title">Our Pathology & Diagnostic Services</h2>
            <p class="section-desc max-w-md">Comprehensive laboratory services ranging from basic blood checks to advanced organic biochemistry panels.</p>
        </div>
        <div class="grid-3">
            <?php foreach ($home_services as $service): 
                $icon = getServiceIconClass($service['category'], $service['title']);
            ?>
                <div class="card service-card">
                    <div class="service-icon"><i class="fa-solid <?php echo $icon; ?>"></i></div>
                    <h3><?php echo htmlspecialchars($service['title']); ?></h3>
                    <p><?php echo htmlspecialchars(substr($service['description'] ?? '', 0, 100)) . (strlen($service['description'] ?? '') > 100 ? '...' : ''); ?></p>
                    <a href="services.php" class="service-link">View Details <i class="fa-solid fa-arrow-right"></i></a>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center" style="margin-top: 40px;">
            <a href="services.php" class="btn btn-teal">Search All pathology Tests <i class="fa-solid fa-arrow-right"></i></a>
        </div>
    </div>
</section>
