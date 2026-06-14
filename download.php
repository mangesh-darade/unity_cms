<?php
$active_nav = 'download';
$page_title = "Download Laboratory Reports Online | Patient Portal";
$meta_description = "Access your digital pathology reports online. Enter your unique Patient ID and mobile number to securely download clinical laboratory PDFs.";

include 'includes/db.php';
include 'includes/header.php';
?>

<!-- Page Title Header -->
<div class="page-header">
    <div class="container">
        <h1>Patient Portal</h1>
        <div class="breadcrumb">
            <a href="index.php">Home</a> &nbsp;/&nbsp; Download Lab Report
        </div>
    </div>
</div>

<!-- Main Portal Layout -->
<section class="section-padding" style="background-color: var(--bg-light);">
    <div class="container" style="max-width: 900px;">
        <div class="grid-2" style="grid-template-columns: 1fr 1.3fr; gap: 40px; align-items: center;">
            
            <!-- Instructions and Help Info -->
            <div>
                <div style="background-color: var(--white); border: 1px solid var(--border); padding: 30px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm);">
                    <h3 style="font-size: 1.3rem; margin-bottom: 20px; color: var(--primary);"><i class="fa-solid fa-lock" style="color: var(--brand-teal);"></i> Secure Patient Access</h3>
                    <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 20px; line-height: 1.7;">To protect patient confidentiality and medical records, reports can only be downloaded by matching both your unique Patient ID and registered Mobile Number.</p>
                    
                    <h4 style="font-size: 0.95rem; margin-bottom: 8px; color: var(--primary);"><i class="fa-regular fa-lightbulb" style="color: var(--warning);"></i> Where to find Patient ID?</h4>
                    <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 20px;">The Patient ID is generated when your sample is collected. It is printed on the physical billing receipt invoice at the top-right corner (e.g. <strong>PAT-1001</strong>). You will also receive it via SMS or WhatsApp confirmation.</p>
                    
                    <div style="border-top: 1px solid var(--border); padding-top: 20px; font-size: 0.85rem; color: var(--text-muted);">
                        <p><i class="fa-solid fa-circle-info" style="color: var(--brand-teal);"></i> <strong>Note:</strong> Routine test reports are available within 8 to 12 hours. Cultured or specialized molecular tests might require 24 to 48 hours before appearing on the portal.</p>
                    </div>
                </div>
            </div>
            
            <!-- Download Card Form -->
            <div class="download-card" style="background: var(--white); border: 1px solid var(--border); box-shadow: var(--shadow-md); color: var(--text-main); border-radius: var(--radius-md);">
                <div style="text-align: center; margin-bottom: 24px;">
                    <i class="fa-solid fa-file-pdf" style="font-size: 3rem; color: #ef4444; margin-bottom: 15px;"></i>
                    <h2 style="font-size: 1.8rem; color: var(--primary); margin-bottom: 6px;">Download Lab Report</h2>
                    <p style="color: var(--text-muted); font-size: 0.9rem;">Fill in your details below to obtain the PDF file.</p>
                </div>
                
                <form id="downloadForm">
                    <div class="form-group">
                        <label for="patient_id" class="form-label" style="color: var(--primary-light);">Patient ID / Bill ID</label>
                        <input type="text" id="patient_id" name="patient_id" class="form-control" placeholder="e.g. PAT-1001" required style="border-color: var(--border);">
                    </div>
                    
                    <div class="form-group">
                        <label for="mobile" class="form-label" style="color: var(--primary-light);">Registered Mobile Number</label>
                        <input type="tel" id="mobile" name="mobile" class="form-control" placeholder="e.g. 9876543210" required style="border-color: var(--border);">
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-full" style="margin-top: 10px;">
                        <i class="fa-solid fa-cloud-arrow-down"></i> Verify and Download Report
                    </button>
                </form>
                
                <div style="margin-top: 25px; text-align: center; font-size: 0.85rem; border-top: 1px solid var(--border); padding-top: 15px; color: var(--text-muted);">
                    Need support downloading? <a href="contact.php" style="color: var(--brand-teal); font-weight: 600;">Contact Lab support</a> or call <a href="tel:+919876543210" style="color: var(--brand-teal); font-weight: 600;">+91 98765 43210</a>
                </div>
            </div>
            
        </div>
    </div>
</section>

<?php
include 'includes/footer.php';
?>
