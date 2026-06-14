<?php
include 'includes/db.php';

$active_nav = 'services';
$page_title = "Pathology Tests & Services | Accurate Blood & Urine Tests";
$meta_description = "Browse our NABL-standard diagnostic tests catalog including Blood Tests, Urine analysis, Liver Function, Thyroid profiles, and Diabetes monitoring panels.";

include 'includes/header.php';

// Fetch all services ordered by sequence
try {
    $services = $db->query("SELECT * FROM cms_services ORDER BY sequence ASC")->fetchAll();
} catch (PDOException $e) {
    $services = [];
}
?>

<!-- Page Title Header -->
<div class="page-header">
    <div class="container">
        <h1>Pathology Tests & Services</h1>
        <div class="breadcrumb">
            <a href="index.php">Home</a> &nbsp;/&nbsp; Tests & Services
        </div>
    </div>
</div>

<!-- Tests Catalog Section -->
<section class="section-padding">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Diagnostics Catalog</span>
            <h2 class="section-title">Search Diagnostic Pathology Tests</h2>
            <p class="section-desc max-w-md">Find pricing, patient pre-test guidelines, and sample requirements for our clinical lab tests.</p>
        </div>
        
        <!-- Search Box -->
        <div class="search-box">
            <div style="flex-grow: 1; position: relative;">
                <input type="text" id="testSearch" class="form-control" placeholder="Search tests by name or category (e.g. CBC, Liver, Thyroid, Diabetes...)" style="padding-left: 45px; border-radius: var(--radius-md);">
                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 16px; top: 16px; color: var(--text-muted);"></i>
            </div>
            <button class="btn btn-teal" id="clearSearch" style="border-radius: var(--radius-md);">Clear</button>
        </div>
        
        <!-- Tests Catalog Grid -->
        <div class="grid-3" id="testsGrid">
            <?php if (empty($services)): ?>
                <div class="text-center" style="grid-column: 1 / -1; padding: 40px;">No tests found.</div>
            <?php else: ?>
                <?php foreach ($services as $service): 
                    $search_terms = strtolower($service['title'] . ' ' . $service['category'] . ' ' . ($service['description'] ?? ''));
                ?>
                    <div class="card test-card" data-title="<?php echo htmlspecialchars($search_terms); ?>">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                            <span style="font-size: 0.8rem; font-weight: 700; color: var(--brand-teal); background-color: var(--brand-teal-bg); padding: 4px 10px; border-radius: 4px;"><?php echo htmlspecialchars($service['category']); ?></span>
                            <strong style="color: var(--brand-blue); font-size: 1.3rem;">₹<?php echo number_format($service['price']); ?></strong>
                        </div>
                        <h3 style="font-size: 1.25rem; margin-bottom: 8px;"><?php echo htmlspecialchars($service['title']); ?></h3>
                        <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 15px;"><?php echo htmlspecialchars($service['description'] ?? ''); ?></p>
                        
                        <div style="border-top: 1px dashed var(--border); padding-top: 12px; font-size: 0.85rem; color: var(--text-muted);">
                            <div style="margin-bottom: 6px;"><i class="fa-solid fa-droplet" style="color: #ef4444; width: 16px;"></i> <strong>Sample:</strong> <?php echo htmlspecialchars($service['sample_type'] ?? 'Blood'); ?></div>
                            <div><i class="fa-solid fa-circle-info" style="color: var(--brand-teal); width: 16px;"></i> <strong>Pre-test:</strong> <?php echo htmlspecialchars($service['prep_instructions'] ?? 'No fasting required'); ?></div>
                        </div>
                        <a href="collection.php?test=<?php echo urlencode(strtolower(str_replace([' ', "'", '"'], '', $service['title']))); ?>" class="btn btn-secondary w-full" style="margin-top: 20px; font-size: 0.85rem; padding: 10px;">Book Test</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Not Found Message -->
        <div id="noResults" class="text-center" style="display: none; padding: 40px; border: 1px dashed var(--border); border-radius: var(--radius-md); margin-top: 20px;">
            <i class="fa-regular fa-face-frown" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 15px;"></i>
            <h3>No Diagnostic Tests Found</h3>
            <p style="color: var(--text-muted);">Try typing another query or filter term, or contact our support directly to ask for your test.</p>
        </div>
    </div>
</section>

<!-- Search script -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('testSearch');
    const clearBtn = document.getElementById('clearSearch');
    const cards = document.querySelectorAll('.test-card');
    const noResults = document.getElementById('noResults');
    const grid = document.getElementById('testsGrid');
    
    function filterTests() {
        const query = searchInput.value.toLowerCase().trim();
        let visibleCount = 0;
        
        cards.forEach(card => {
            const dataTitle = card.getAttribute('data-title');
            if (dataTitle.includes(query)) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });
        
        if (visibleCount === 0) {
            noResults.style.display = 'block';
            grid.style.display = 'none';
        } else {
            noResults.style.display = 'none';
            grid.style.display = 'grid';
        }
    }
    
    searchInput.addEventListener('input', filterTests);
    
    clearBtn.addEventListener('click', () => {
        searchInput.value = '';
        cards.forEach(card => card.style.display = 'flex');
        noResults.style.display = 'none';
        grid.style.display = 'grid';
    });
});
</script>

<?php
include 'includes/footer.php';
?>
