<?php
include 'includes/db.php';
require_once 'includes/captcha.php';

$page = cmsPage($cms_pages, 'download', [
    'meta_title' => 'Download Laboratory Reports Online | Patient Portal',
    'meta_description' => 'Access your digital pathology reports online using Patient ID and mobile number.',
]);
$cms_page_context = $page;
$active_nav = 'download';
$page_title = $page['meta_title'];
$meta_description = $page['meta_description'];
$meta_keywords = $page['meta_keywords'] ?? null;
$otp_enabled = ($cms['report_otp_enabled'] ?? '1') === '1';
$support_phone = cmsSetting($cms, 'support_phone');
$phone_href = preg_replace('/[^0-9+]/', '', $support_phone);

include 'includes/header.php';
?>

<?php renderPageHeader($page, 'Patient Portal', 'Download Lab Report'); ?>

<section class="section-padding portal-section">
    <div class="container container-narrow">
        <div class="collection-layout reveal">
            <div class="info-panel card reveal reveal-delay-1">
                <h3 class="info-panel-title"><i class="fa-solid fa-lock"></i> Secure Patient Access</h3>
                <p class="info-panel-desc">To protect patient confidentiality and medical records, reports can only be downloaded by matching both your unique Patient ID and registered Mobile Number.</p>

                <div class="info-tip">
                    <h4><i class="fa-regular fa-lightbulb"></i> Where to find Patient ID?</h4>
                    <p>The Patient ID is generated when your sample is collected. It is printed on the physical billing receipt invoice at the top-right corner (e.g. <strong>PAT-1001</strong>). You will also receive it via SMS or WhatsApp confirmation.</p>
                </div>

                <div class="info-note">
                    <i class="fa-solid fa-circle-info"></i>
                    <p><strong>Note:</strong> Routine test reports are available within 8 to 12 hours. Cultured or specialized molecular tests might require 24 to 48 hours before appearing on the portal.</p>
                </div>
            </div>

            <div class="download-card premium-form-card reveal reveal-delay-2">
                <div class="form-card-header form-card-header-center">
                    <span class="form-card-icon form-card-icon-pdf"><i class="fa-solid fa-file-pdf"></i></span>
                    <h3>Download Lab Report</h3>
                    <p>Fill in your details below to obtain the PDF file.</p>
                </div>

                <form id="downloadForm" data-otp-enabled="<?php echo $otp_enabled ? '1' : '0'; ?>">
                    <div class="form-group">
                        <label for="patient_id" class="form-label">Patient ID / Bill ID</label>
                        <input type="text" id="patient_id" name="patient_id" class="form-control" placeholder="e.g. PAT-1001" required>
                    </div>
                    <div class="form-group">
                        <label for="mobile" class="form-label">Registered Mobile Number</label>
                        <input type="tel" id="mobile" name="mobile" class="form-control" placeholder="e.g. 9876543210" required>
                    </div>
                    <?php if (captchaEnabled($cms)) { echo renderCaptchaField(); } ?>
                    <div class="form-group otp-group" style="display:none;">
                        <label for="report_otp" class="form-label">Enter 6-digit OTP</label>
                        <input type="text" id="report_otp" name="report_otp" class="form-control" placeholder="OTP from email/SMS" maxlength="6">
                    </div>
                    <button type="button" id="sendOtpBtn" class="btn btn-secondary w-full" <?php echo $otp_enabled ? '' : 'style="display:none;"'; ?>>
                        <i class="fa-solid fa-paper-plane"></i> Send OTP
                    </button>
                    <button type="submit" id="downloadSubmitBtn" class="btn btn-primary w-full">
                        <i class="fa-solid fa-cloud-arrow-down"></i> <?php echo $otp_enabled ? 'Verify OTP & Get Reports' : 'Verify and Download Report'; ?>
                    </button>
                </form>

                <div class="form-card-footer">
                    Need support downloading? <a href="contact.php">Contact Lab support</a>
                    <?php if ($support_phone !== ''): ?>
                    or call <a href="tel:<?php echo htmlspecialchars($phone_href); ?>"><?php echo htmlspecialchars($support_phone); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
