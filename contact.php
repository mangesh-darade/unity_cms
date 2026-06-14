<?php
$active_nav = 'contact';
$page_title = "Contact Us | Diagnostic Lab Location & Helpline";
$meta_description = "Get in touch with Unity Clinical Laboratory. Find our location address, helpline phone numbers, email address, and send general test inquiries.";

include 'includes/db.php';
include 'includes/header.php';
?>

<!-- Page Title Header -->
<div class="page-header">
    <div class="container">
        <h1>Contact Us</h1>
        <div class="breadcrumb">
            <a href="index.php">Home</a> &nbsp;/&nbsp; Contact Us
        </div>
    </div>
</div>

<!-- Contact Information and Form Grid -->
<section class="section-padding">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Helpline Support</span>
            <h2 class="section-title">We are Here to Assist You</h2>
            <p class="section-desc max-w-md">Reach out to book a test, query report status, or explore corporate checkup partnerships.</p>
        </div>
        
        <div class="contact-grid">
            <!-- Left Column: Contact details and Map -->
            <div class="contact-info-list">
                <div class="card" style="padding: 30px;">
                    <h3 style="font-size: 1.3rem; margin-bottom: 20px; color: var(--primary); border-bottom: 1px solid var(--border); padding-bottom: 10px;">Contact Information</h3>
                    
                    <div style="display: flex; flex-direction: column; gap: 20px;">
                        <div class="contact-item">
                            <div class="contact-icon"><i class="fa-solid fa-location-dot"></i></div>
                            <div class="contact-detail">
                                <h3>Laboratory Address</h3>
                                <p>102 Health Plaza, Sector 15, Gurugram, Haryana - 122001</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon"><i class="fa-solid fa-phone"></i></div>
                            <div class="contact-detail">
                                <h3>Helpline Number</h3>
                                <p><a href="tel:+919876543210" style="color: var(--brand-blue); font-weight: 600;">+91 98765 43210</a></p>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon"><i class="fa-solid fa-envelope"></i></div>
                            <div class="contact-detail">
                                <h3>Email Support</h3>
                                <p><a href="mailto:info@unityclinicallab.com" style="color: var(--brand-blue); font-weight: 600;">info@unityclinicallab.com</a></p>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon" style="color: #25d366; background-color: rgba(37, 211, 102, 0.1);"><i class="fa-brands fa-whatsapp"></i></div>
                            <div class="contact-detail">
                                <h3>WhatsApp Support</h3>
                                <p><a href="https://wa.me/919876543210?text=Hi,%20I%20have%20a%20query%20about%20my%20lab%20test." target="_blank" style="color: #25d366; font-weight: 700;">Chat Live with Us</a></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card" style="padding: 30px;">
                    <h3 style="font-size: 1.3rem; margin-bottom: 20px; color: var(--primary); border-bottom: 1px solid var(--border); padding-bottom: 10px;">Operating Hours</h3>
                    <ul style="list-style: none; display: flex; flex-direction: column; gap: 10px; font-size: 0.95rem;">
                        <li style="display: flex; justify-content: space-between;">
                            <strong>Monday - Saturday:</strong>
                            <span>07:00 AM - 09:00 PM</span>
                        </li>
                        <li style="display: flex; justify-content: space-between;">
                            <strong>Sunday:</strong>
                            <span>07:00 AM - 02:00 PM</span>
                        </li>
                        <li style="color: var(--text-muted); font-size: 0.85rem; border-top: 1px dashed var(--border); padding-top: 8px; margin-top: 8px;">
                            <i class="fa-solid fa-truck-medical"></i> Home collection requests are serviced from 6:00 AM daily.
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Right Column: Inquiry Form Card -->
            <div class="booking-form-card" style="box-shadow: var(--shadow-md); border-radius: var(--radius-md);">
                <h3 style="font-size: 1.5rem; margin-bottom: 24px; text-align: center; color: var(--primary);"><i class="fa-solid fa-envelope-open-text" style="color: var(--brand-teal);"></i> Diagnostic Inquiry Form</h3>
                <form id="inquiryForm">
                    <div class="form-group">
                        <label for="name" class="form-label">Your Full Name</label>
                        <input type="text" id="name" name="name" class="form-control" placeholder="Enter name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="mobile" class="form-label">Mobile Number</label>
                        <input type="tel" id="mobile" name="mobile" class="form-control" placeholder="Enter 10-digit mobile" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="name@example.com" required>
                    </div>

                    <div class="form-group">
                        <label for="subject" class="form-label">Inquiry Subject</label>
                        <input type="text" id="subject" name="subject" class="form-control" placeholder="e.g. Health packages, test discounts, report correction" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="message" class="form-label">Message Details</label>
                        <textarea id="message" name="message" class="form-control" placeholder="Write details about the tests you require, or any questions you have for our pathologists..." required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-full" style="margin-top: 10px;">
                        <i class="fa-solid fa-paper-plane"></i> Submit Inquiry Details
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Google Map Iframe Section -->
        <div style="margin-top: 50px;">
            <div class="map-container" style="height: 400px; border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow-md);">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m18!1m2!1s0x390d19d6d5555555%3A0x8e82efc6ff6222b6!2sSector%2015%2C%20Gurugram%2C%20Haryana!5e0!3m2!1sen!2sin!4v1680000000000!5m2!1sen!2sin" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" style="width: 100%; height: 100%; border: 0;"></iframe>
            </div>
        </div>
    </div>
</section>

<?php
include 'includes/footer.php';
?>
