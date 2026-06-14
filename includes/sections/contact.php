<!-- Contact Section -->
<section class="contact-section section-padding" style="border-top: 1px solid var(--border);">
    <div class="container">
        <?php renderSectionHeader($cms_sections, 'contact', [
            'tag' => 'Reach Us',
            'title' => 'Get In Touch With Unity Lab',
            'desc' => 'Find our location details or send an immediate diagnostic inquiry through the contact form.',
        ]); ?>
        
        <div class="contact-grid">
            <!-- Info & Map -->
            <div class="contact-info-list">
                <div class="contact-item">
                    <div class="contact-icon"><i class="fa-solid fa-location-arrow"></i></div>
                    <div class="contact-detail">
                        <h3>Laboratory Address</h3>
                        <p><?php echo htmlspecialchars($cms['support_address'] ?? 'Unity Clinical Laboratory, Maharashtra, India'); ?></p>
                    </div>
                </div>
                <div class="contact-item">
                    <div class="contact-icon"><i class="fa-solid fa-phone"></i></div>
                    <div class="contact-detail">
                        <h3>Call Support</h3>
                        <p><a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $cms['support_phone']); ?>"><?php echo htmlspecialchars($cms['support_phone'] ?? '+91 98507 00268'); ?></a></p>
                    </div>
                </div>
                <div class="contact-item">
                    <div class="contact-icon"><i class="fa-solid fa-envelope"></i></div>
                    <div class="contact-detail">
                        <h3>Email Contacts</h3>
                        <p><a href="mailto:<?php echo htmlspecialchars($cms['support_email'] ?? 'info@unityclinicallab.com'); ?>"><?php echo htmlspecialchars($cms['support_email'] ?? 'info@unityclinicallab.com'); ?></a></p>
                    </div>
                </div>
                
                <!-- Embedded Google Map -->
                <div class="map-container">
                    <iframe src="<?php echo htmlspecialchars($cms['maps_embed_url'] ?? ''); ?>" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
            
            <!-- Quick Inquiry Form -->
            <div class="booking-form-card">
                <h3 style="font-size: 1.5rem; margin-bottom: 24px; text-align: center;"><i class="fa-solid fa-envelope-open-text" style="color: var(--brand-teal);"></i> Send a Diagnostic Inquiry</h3>
                <form id="inquiryForm">
                    <div class="form-group">
                        <label for="inq_name" class="form-label">Your Name</label>
                        <input type="text" id="inq_name" name="name" class="form-control" placeholder="Enter your name" required>
                    </div>
                    <div class="form-group">
                        <label for="inq_mobile" class="form-label">Mobile Number</label>
                        <input type="tel" id="inq_mobile" name="mobile" class="form-control" placeholder="Enter 10-digit mobile" required>
                    </div>
                    <div class="form-group">
                        <label for="inq_email" class="form-label">Email Address</label>
                        <input type="email" id="inq_email" name="email" class="form-control" placeholder="Enter email address" required>
                    </div>
                    <div class="form-group">
                        <label for="inq_subject" class="form-label">Subject</label>
                        <input type="text" id="inq_subject" name="subject" class="form-control" placeholder="e.g. Corporate checkup, test pricing query" required>
                    </div>
                    <div class="form-group">
                        <label for="inq_msg" class="form-label">Message Details</label>
                        <textarea id="inq_msg" name="message" class="form-control" placeholder="State your requirements or question..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-teal w-full"><i class="fa-solid fa-paper-plane"></i> Submit Inquiry</button>
                </form>
            </div>
        </div>
    </div>
</section>
