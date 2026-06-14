<?php
/**
 * Premium legal document layout (Privacy, Terms, etc.)
 * Expects: $page, $doc_icon, optional $related (url, label)
 */
$doc_icon = $doc_icon ?? 'fa-solid fa-file-contract';
$related = $related ?? null;
?>
<section class="section-padding portal-section legal-section">
    <div class="container container-article">
        <div class="legal-doc-card card reveal">
            <div class="legal-doc-header">
                <div class="legal-doc-header-main">
                    <span class="legal-doc-badge"><i class="<?php echo htmlspecialchars($doc_icon); ?>"></i> Legal Document</span>
                    <h2 class="legal-doc-title"><?php echo htmlspecialchars($page['page_heading'] ?? $page['breadcrumb_label'] ?? 'Document'); ?></h2>
                    <?php if (!empty($page['meta_description'])): ?>
                    <p class="legal-doc-intro"><?php echo htmlspecialchars($page['meta_description']); ?></p>
                    <?php endif; ?>
                </div>
                <div class="legal-doc-actions">
                    <button type="button" class="btn btn-secondary btn-sm legal-print-btn" onclick="window.print()">
                        <i class="fa-solid fa-print"></i> Print
                    </button>
                </div>
            </div>

            <div class="legal-doc-divider" aria-hidden="true"></div>

            <div class="cms-page-body legal-page-body">
                <?php renderPageBodyContent($page['page_body'] ?? '', ''); ?>
            </div>

            <div class="legal-doc-footer">
                <?php if ($related && !empty($related['url'])): ?>
                <a href="<?php echo htmlspecialchars($related['url']); ?>" class="legal-related-link">
                    <i class="fa-solid fa-arrow-right"></i>
                    <?php echo htmlspecialchars($related['label'] ?? 'Related document'); ?>
                </a>
                <?php endif; ?>
                <a href="contact.php" class="legal-related-link legal-related-muted">
                    <i class="fa-solid fa-headset"></i> Contact support
                </a>
            </div>
        </div>

        <div class="legal-trust-strip reveal">
            <div class="legal-trust-item">
                <i class="fa-solid fa-lock"></i>
                <span>Secure patient data handling</span>
            </div>
            <div class="legal-trust-item">
                <i class="fa-solid fa-shield-halved"></i>
                <span>NABL-aligned laboratory standards</span>
            </div>
            <div class="legal-trust-item">
                <i class="fa-solid fa-user-shield"></i>
                <span>Authorized report access only</span>
            </div>
        </div>
    </div>
</section>
