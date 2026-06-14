<?php
// Fetch FAQs ordered by sequence
$home_faqs = $db->query("SELECT * FROM cms_faqs ORDER BY sequence ASC")->fetchAll();
?>
<!-- FAQ Section -->
<section class="faq-section section-padding">
    <div class="container">
        <?php renderSectionHeader($cms_sections, 'faqs', [
            'tag' => 'Help Desk',
            'title' => 'Frequently Asked Questions',
            'desc' => 'Get answers to the most common queries regarding booking, home visits, and reporting.',
        ]); ?>
        
        <!-- Accordion FAQ -->
        <div class="faq-grid">
            <?php foreach ($home_faqs as $faq): ?>
                <div class="faq-item">
                    <div class="faq-question">
                        <span><?php echo htmlspecialchars($faq['question']); ?></span>
                        <span class="faq-icon">+</span>
                    </div>
                    <div class="faq-answer">
                        <p><?php echo htmlspecialchars($faq['answer']); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
