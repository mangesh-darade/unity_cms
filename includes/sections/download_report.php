<?php
require_once __DIR__ . '/../captcha.php';
$otp_enabled = ($cms['report_otp_enabled'] ?? '1') === '1';
?>
<!-- Online Report Download Section -->
<section id="report-download" class="download-section section-padding">
    <div class="container max-w-md">
        <div class="section-header text-center">
            <span class="section-tag" style="color: var(--brand-teal-light);"><?php echo htmlspecialchars(cmsSection($cms_sections, 'download_report', 'section_tag', 'Patient Portal')); ?></span>
            <h2 class="section-title"><?php echo htmlspecialchars(cmsSection($cms_sections, 'download_report', 'section_heading', 'Download Lab Report Online')); ?></h2>
            <p class="section-desc" style="color: var(--border);"><?php echo htmlspecialchars(cmsSection($cms_sections, 'download_report', 'section_description', 'Enter your Patient ID and mobile number to download your report securely.')); ?></p>
        </div>
        
        <div class="download-card">
            <form id="downloadForm" data-otp-enabled="<?php echo $otp_enabled ? '1' : '0'; ?>">
                <div class="form-group">
                    <label for="patient_id" class="form-label">Patient ID / Booking ID</label>
                    <input type="text" id="patient_id" name="patient_id" class="form-control" placeholder="e.g. PAT-1001" required>
                </div>
                <div class="form-group">
                    <label for="mobile" class="form-label">Registered Mobile Number</label>
                    <input type="tel" id="mobile" name="mobile" class="form-control" placeholder="e.g. 9876543210" required>
                </div>
                <?php if (captchaEnabled($cms)): echo renderCaptchaField(); endif; ?>
                <div class="form-group otp-group" style="display:none;">
                    <label for="report_otp" class="form-label">Enter 6-digit OTP</label>
                    <input type="text" id="report_otp" name="report_otp" class="form-control" placeholder="OTP from email/SMS" maxlength="6" pattern="[0-9]{6}">
                </div>
                <button type="button" id="sendOtpBtn" class="btn btn-secondary w-full" style="margin-top: 10px; <?php echo $otp_enabled ? '' : 'display:none;'; ?>">
                    <i class="fa-solid fa-paper-plane"></i> Send OTP
                </button>
                <button type="submit" id="downloadSubmitBtn" class="btn btn-teal w-full" style="margin-top: 10px;">
                    <i class="fa-solid fa-cloud-arrow-down"></i> <?php echo $otp_enabled ? 'Verify OTP &amp; Get Reports' : 'Download Report PDF'; ?>
                </button>
            </form>
        </div>
    </div>
</section>
