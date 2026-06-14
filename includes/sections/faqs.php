<?php
// Fetch FAQs ordered by sequence
$home_faqs = $db->query("SELECT * FROM cms_faqs ORDER BY sequence ASC")->fetchAll();
?>
<!-- FAQ Section -->
<section class="faq-section section-padding">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Help Desk</span>
            <h2 class="section-title">Frequently Asked Questions</h2>
            <p class="section-desc max-w-md">Get answers to the most common queries regarding booking, home visits, and reporting.</p>
        </div>
        
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
