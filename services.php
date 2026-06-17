<?php
include 'includes/db.php';
require_once __DIR__ . '/includes/services_catalog.php';

$page = cmsPage($cms_pages, 'services', [
    'meta_title' => 'Pathology Tests & Services | Accurate Blood & Urine Tests',
    'meta_description' => 'Browse our NABL-standard diagnostic tests catalog including Blood Tests, Urine analysis, Liver Function, Thyroid profiles, and Diabetes monitoring panels.',
]);
$cms_page_context = $page;
$active_nav = 'services';
$page_title = $page['meta_title'];
$meta_description = $page['meta_description'];
$meta_keywords = $page['meta_keywords'] ?? null;

$filterQ = trim((string) ($_GET['q'] ?? ''));
$filterCategory = trim((string) ($_GET['category'] ?? ''));
$filterPage = max(1, (int) ($_GET['page'] ?? 1));

$catalog = cmsServicesCatalog($db, [
    'q' => $filterQ,
    'category' => $filterCategory,
    'page' => $filterPage,
    'per_page' => 24,
]);
$categories = cmsServiceCategories($db);
$services = $catalog['items'];
$totalServices = (int) $catalog['total'];

$categoryCounts = [];
foreach ($db->query('SELECT category, COUNT(*) AS cnt FROM cms_services GROUP BY category ORDER BY category ASC') as $row) {
    $categoryCounts[$row['category']] = (int) $row['cnt'];
}

include 'includes/header.php';
?>

<?php renderPageHeader($page, 'Pathology Tests & Services', 'Tests & Services'); ?>

<section class="section-padding services-catalog-section">
    <div class="container">
        <?php renderPageSectionHeader($page, [
            'tag' => 'Diagnostics Catalog',
            'title' => 'Search Diagnostic Pathology Tests',
            'desc' => 'Find pricing, patient pre-test guidelines, and sample requirements for our clinical lab tests.',
        ]); ?>

        <div class="services-catalog-toolbar reveal">
            <div class="search-box search-box-premium services-search-wrap">
                <div class="services-search-field">
                    <input type="search" id="testSearch" class="form-control" value="<?php echo htmlspecialchars($filterQ, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Search tests by name or category (e.g. CBC, Liver, Thyroid…)" autocomplete="off" enterkeyhint="search">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                </div>
                <button type="button" class="btn btn-teal" id="clearSearch">Clear</button>
            </div>

            <div class="services-category-bar" id="categoryBar" role="tablist" aria-label="Filter by category">
                <button type="button" class="services-category-chip<?php echo $filterCategory === '' ? ' is-active' : ''; ?>" data-category="">All (<?php echo $totalServices; ?>)</button>
                <?php foreach ($categories as $cat): ?>
                    <button type="button" class="services-category-chip<?php echo strcasecmp($filterCategory, $cat) === 0 ? ' is-active' : ''; ?>" data-category="<?php echo htmlspecialchars($cat, ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo htmlspecialchars($cat); ?> (<?php echo $categoryCounts[$cat] ?? 0; ?>)
                    </button>
                <?php endforeach; ?>
            </div>

            <p class="services-result-count" id="resultCount" aria-live="polite">
                Showing <?php echo count($services); ?> of <?php echo (int) $catalog['total']; ?> tests
                <?php if ($filterQ !== ''): ?> matching “<?php echo htmlspecialchars($filterQ, ENT_QUOTES, 'UTF-8'); ?>”<?php endif; ?>
            </p>
        </div>

        <div class="grid-3 services-tests-grid" id="testsGrid">
            <?php if ($services === []): ?>
                <div class="services-empty-state" id="emptyState">
                    <i class="fa-regular fa-face-frown" aria-hidden="true"></i>
                    <h3>No Diagnostic Tests Found</h3>
                    <p>Try another search term or category, or contact us on <?php echo htmlspecialchars(cmsSetting($cms, 'support_phone')); ?>.</p>
                </div>
            <?php else: ?>
                <?php foreach ($services as $service) {
                    cmsRenderServiceCard($service);
                } ?>
            <?php endif; ?>
        </div>

        <div id="noResults" class="services-empty-state" style="display: none;" hidden>
            <i class="fa-regular fa-face-frown" aria-hidden="true"></i>
            <h3>No Diagnostic Tests Found</h3>
            <p>Try typing another query or pick a different category.</p>
        </div>

        <nav class="services-pagination" id="servicesPagination" aria-label="Tests pagination"<?php echo $catalog['pages'] <= 1 ? ' hidden' : ''; ?>>
            <?php if ($catalog['page'] > 1): ?>
                <button type="button" class="btn btn-secondary services-page-btn" data-page="<?php echo $catalog['page'] - 1; ?>">← Previous</button>
            <?php endif; ?>
            <span class="services-page-info">Page <?php echo (int) $catalog['page']; ?> of <?php echo (int) $catalog['pages']; ?></span>
            <?php if ($catalog['page'] < $catalog['pages']): ?>
                <button type="button" class="btn btn-teal services-page-btn" data-page="<?php echo $catalog['page'] + 1; ?>">Next →</button>
            <?php endif; ?>
        </nav>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const apiUrl = 'api/services.php';
    const searchInput = document.getElementById('testSearch');
    const clearBtn = document.getElementById('clearSearch');
    const grid = document.getElementById('testsGrid');
    const noResults = document.getElementById('noResults');
    const resultCount = document.getElementById('resultCount');
    const pagination = document.getElementById('servicesPagination');
    const categoryBar = document.getElementById('categoryBar');

    let activeCategory = <?php echo json_encode($filterCategory, JSON_UNESCAPED_UNICODE); ?>;
    let currentPage = <?php echo (int) $catalog['page']; ?>;
    let debounceTimer = null;
    let fetchController = null;

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function renderCard(item) {
        const desc = item.description
            ? `<p class="test-card-desc">${escapeHtml(item.description)}</p>`
            : '';
        const searchTerms = escapeHtml(`${item.title} ${item.category} ${item.description}`.toLowerCase());
        return `
            <div class="card test-card" data-title="${searchTerms}">
                <div class="test-card-top">
                    <span class="test-category">${escapeHtml(item.category)}</span>
                    <strong class="test-price">₹${Number(item.price).toLocaleString('en-IN')}</strong>
                </div>
                <h3 class="test-card-title">${escapeHtml(item.title)}</h3>
                ${desc}
                <div class="test-card-meta">
                    <div><i class="fa-solid fa-droplet" aria-hidden="true"></i> <strong>Sample:</strong> ${escapeHtml(item.sample_type)}</div>
                    <div><i class="fa-solid fa-circle-info" aria-hidden="true"></i> <strong>Pre-test:</strong> ${escapeHtml(item.prep_instructions)}</div>
                </div>
                <a href="collection.php?test=${encodeURIComponent(item.book_slug)}" class="btn btn-secondary w-full test-card-book">Book Test</a>
            </div>`;
    }

    function renderPagination(page, pages) {
        if (!pagination) return;
        if (pages <= 1) {
            pagination.hidden = true;
            pagination.innerHTML = '';
            return;
        }
        pagination.hidden = false;
        let html = '';
        if (page > 1) {
            html += `<button type="button" class="btn btn-secondary services-page-btn" data-page="${page - 1}">← Previous</button>`;
        }
        html += `<span class="services-page-info">Page ${page} of ${pages}</span>`;
        if (page < pages) {
            html += `<button type="button" class="btn btn-teal services-page-btn" data-page="${page + 1}">Next →</button>`;
        }
        pagination.innerHTML = html;
    }

    function updateCategoryChips() {
        categoryBar?.querySelectorAll('.services-category-chip').forEach((chip) => {
            const cat = chip.getAttribute('data-category') || '';
            chip.classList.toggle('is-active', cat === activeCategory);
        });
    }

    function setLoading(isLoading) {
        grid.classList.toggle('is-loading', isLoading);
    }

    async function loadServices(page = 1) {
        const q = searchInput.value.trim();
        currentPage = page;

        if (fetchController) fetchController.abort();
        fetchController = new AbortController();
        setLoading(true);

        const params = new URLSearchParams({ page: String(page), per_page: '24' });
        if (q) params.set('q', q);
        if (activeCategory) params.set('category', activeCategory);

        try {
            const res = await fetch(`${apiUrl}?${params.toString()}`, {
                signal: fetchController.signal,
                headers: { 'Accept': 'application/json' },
            });
            const data = await res.json();
            if (!data.success) throw new Error(data.message || 'Load failed');

            if (data.items.length === 0) {
                grid.innerHTML = '';
                grid.style.display = 'none';
                noResults.hidden = false;
                noResults.style.display = 'block';
            } else {
                grid.innerHTML = data.items.map(renderCard).join('');
                grid.style.display = 'grid';
                noResults.hidden = true;
                noResults.style.display = 'none';
            }

            const qLabel = q ? ` matching “${q}”` : '';
            resultCount.textContent = `Showing ${data.items.length} of ${data.total} tests${qLabel}`;
            renderPagination(data.page, data.pages);

            const url = new URL(window.location.href);
            url.searchParams.set('page', String(data.page));
            if (q) url.searchParams.set('q', q); else url.searchParams.delete('q');
            if (activeCategory) url.searchParams.set('category', activeCategory); else url.searchParams.delete('category');
            history.replaceState(null, '', url);
        } catch (err) {
            if (err.name !== 'AbortError') {
                grid.innerHTML = '<div class="services-empty-state"><p>Unable to load tests. Please refresh the page.</p></div>';
            }
        } finally {
            setLoading(false);
        }
    }

    searchInput?.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => loadServices(1), 280);
    });

    clearBtn?.addEventListener('click', () => {
        searchInput.value = '';
        activeCategory = '';
        updateCategoryChips();
        loadServices(1);
    });

    categoryBar?.addEventListener('click', (e) => {
        const chip = e.target.closest('.services-category-chip');
        if (!chip) return;
        activeCategory = chip.getAttribute('data-category') || '';
        updateCategoryChips();
        loadServices(1);
    });

    pagination?.addEventListener('click', (e) => {
        const btn = e.target.closest('.services-page-btn');
        if (!btn) return;
        loadServices(parseInt(btn.getAttribute('data-page'), 10) || 1);
        document.querySelector('.services-catalog-section')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
});
</script>

<?php
$serviceListSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'ItemList',
    'name' => 'Pathology Tests & Diagnostic Services',
    'numberOfItems' => $totalServices,
    'itemListElement' => [],
];
$pos = 1;
foreach (array_slice($services, 0, 24) as $svc) {
    $serviceListSchema['itemListElement'][] = [
        '@type' => 'ListItem',
        'position' => $pos++,
        'item' => [
            '@type' => 'MedicalTest',
            'name' => $svc['title'],
            'description' => $svc['description'] ?? '',
            'offers' => [
                '@type' => 'Offer',
                'price' => (string) $svc['price'],
                'priceCurrency' => 'INR',
            ],
        ],
    ];
}
renderJsonLd($serviceListSchema);

include 'includes/footer.php'; ?>
