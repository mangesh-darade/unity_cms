<?php

function cmsServiceBookSlug(string $title): string
{
    return strtolower(str_replace([' ', "'", '"'], '', $title));
}

/**
 * @return array{items: list<array>, total: int, page: int, per_page: int, pages: int}
 */
function cmsServicesCatalog(PDO $db, array $opts = []): array
{
    $page = max(1, (int) ($opts['page'] ?? 1));
    $perPage = max(12, min(48, (int) ($opts['per_page'] ?? 24)));
    $search = trim((string) ($opts['q'] ?? ''));
    $category = trim((string) ($opts['category'] ?? ''));

    $where = [];
    $params = [];

    if ($search !== '') {
        $where[] = '(title LIKE :q OR category LIKE :q OR description LIKE :q)';
        $params[':q'] = '%' . $search . '%';
    }
    if ($category !== '' && strcasecmp($category, 'all') !== 0) {
        $where[] = 'category = :category';
        $params[':category'] = $category;
    }

    $whereSql = $where !== [] ? ' WHERE ' . implode(' AND ', $where) : '';

    $countStmt = $db->prepare("SELECT COUNT(*) FROM cms_services{$whereSql}");
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();

    $pages = max(1, (int) ceil($total / $perPage));
    if ($page > $pages) {
        $page = $pages;
    }
    $offset = ($page - 1) * $perPage;

    $sql = "SELECT id, title, category, price, description, sample_type, prep_instructions, sequence
            FROM cms_services{$whereSql}
            ORDER BY sequence ASC, id ASC
            LIMIT :limit OFFSET :offset";

    $stmt = $db->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    return [
        'items' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        'total' => $total,
        'page' => $page,
        'per_page' => $perPage,
        'pages' => $pages,
    ];
}

/** @return list<string> */
function cmsServiceCategories(PDO $db): array
{
    $rows = $db->query('SELECT DISTINCT category FROM cms_services ORDER BY category ASC')->fetchAll(PDO::FETCH_COLUMN);
    return array_values(array_filter(array_map('strval', $rows)));
}

function cmsRenderServiceCard(array $service): void
{
    $searchTerms = strtolower(
        ($service['title'] ?? '') . ' ' . ($service['category'] ?? '') . ' ' . ($service['description'] ?? '')
    );
    $bookSlug = cmsServiceBookSlug((string) ($service['title'] ?? ''));
    ?>
    <div class="card test-card" data-title="<?php echo htmlspecialchars($searchTerms, ENT_QUOTES, 'UTF-8'); ?>">
        <div class="test-card-top">
            <span class="test-category"><?php echo htmlspecialchars((string) $service['category']); ?></span>
            <strong class="test-price">₹<?php echo number_format((float) $service['price']); ?></strong>
        </div>
        <h3 class="test-card-title"><?php echo htmlspecialchars((string) $service['title']); ?></h3>
        <?php if (!empty($service['description'])): ?>
            <p class="test-card-desc"><?php echo htmlspecialchars((string) $service['description']); ?></p>
        <?php endif; ?>
        <div class="test-card-meta">
            <div><i class="fa-solid fa-droplet" aria-hidden="true"></i> <strong>Sample:</strong> <?php echo htmlspecialchars((string) ($service['sample_type'] ?? 'Blood')); ?></div>
            <div><i class="fa-solid fa-circle-info" aria-hidden="true"></i> <strong>Pre-test:</strong> <?php echo htmlspecialchars((string) ($service['prep_instructions'] ?? 'No fasting required')); ?></div>
        </div>
        <a href="collection.php?test=<?php echo urlencode($bookSlug); ?>" class="btn btn-secondary w-full test-card-book">Book Test</a>
    </div>
    <?php
}
