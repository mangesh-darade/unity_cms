<?php
if (($cms['trust_strip_enabled'] ?? '1') !== '1') {
    return;
}

$trustItems = [];
for ($i = 1; $i <= 4; $i++) {
    $text = trim(cmsSetting($cms, 'trust_strip_' . $i . '_text'));
    if ($text === '') {
        continue;
    }
    $trustItems[] = [
        'icon' => cmsSetting($cms, 'trust_strip_' . $i . '_icon', 'fa-solid fa-circle-check'),
        'text' => $text,
    ];
}

$rateCardEnabled = ($cms['rate_card_enabled'] ?? '1') === '1';
$rateCardImage = cmsSetting($cms, 'rate_card_image', 'images/gallery/web/rate-card.jpg');
$rateCardText = cmsSetting($cms, 'rate_card_cta_text', 'View Rate Card');
$rateCardUrl = preg_match('#^https?://#i', $rateCardImage)
    ? $rateCardImage
    : rtrim(BASE_URL, '/') . '/' . ltrim($rateCardImage, '/');
?>
<section class="trust-strip-section" aria-label="Laboratory trust highlights">
    <div class="container">
        <div class="trust-strip-inner reveal">
            <?php if (!empty($trustItems)): ?>
            <ul class="trust-strip-list">
                <?php foreach ($trustItems as $item): ?>
                <li class="trust-strip-item">
                    <i class="<?php echo htmlspecialchars($item['icon']); ?>" aria-hidden="true"></i>
                    <span><?php echo htmlspecialchars($item['text']); ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>

            <?php if ($rateCardEnabled && $rateCardImage !== ''): ?>
            <div class="trust-strip-rate-card">
                <a href="<?php echo htmlspecialchars($rateCardUrl); ?>" class="btn btn-teal trust-rate-card-btn" target="_blank" rel="noopener">
                    <i class="fa-solid fa-tags"></i>
                    <?php echo htmlspecialchars($rateCardText); ?>
                </a>
                <span class="trust-rate-card-note">Official pathology test &amp; package pricing</span>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
