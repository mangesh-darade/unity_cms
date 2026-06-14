<?php
$active_nav = 'about';
$page_title = "About Us | Accreditations & Quality Standards";
$meta_description = "Unity Clinical Laboratory is an ISO 9001:2015 and NABL aligned diagnostic pathology lab committed to accurate diagnostics, run by certified pathologists.";

include 'includes/db.php';
include 'includes/header.php';
?>

<!-- Page Title Header -->
<div class="page-header">
    <div class="container">
        <h1>About Our Laboratory</h1>
        <div class="breadcrumb">
            <a href="index.php">Home</a> &nbsp;/&nbsp; About Us
        </div>
    </div>
</div>

<!-- Bio & Standard Section -->
<section class="section-padding">
    <div class="container">
        <div class="grid-2 align-center">
            <div>
                <span class="section-tag" style="margin-bottom: 10px;">Our Background</span>
                <h2 style="font-size: 2.2rem; margin-bottom: 20px;">Dedicated to Precision and Patient Care since 2012</h2>
                <p style="color: var(--text-muted); margin-bottom: 20px; font-size: 1.05rem;">Unity Clinical Laboratory was founded with a single mission: to deliver accurate, reproducible, and fast diagnostic results. We understand that behind every blood vial is a patient awaiting critical answers. That is why we employ rigorous validation controls and fully automated systems to prevent manual errors.</p>
                <p style="color: var(--text-muted); margin-bottom: 20px;">We adhere strictly to the National Accreditation Board for Testing and Calibration Laboratories (NABL) guidelines. Our facility is equipped with automated barcoding systems, ensuring sample identification tracking is flawless from sample draw to final digital signature.</p>
                
                <div style="display: flex; gap: 15px; margin-top: 30px;">
                    <div style="background-color: var(--brand-teal-bg); border-left: 4px solid var(--brand-teal); padding: 15px; border-radius: 4px; flex: 1;">
                        <h4 style="color: var(--brand-teal); font-weight: 700; margin-bottom: 4px;">NABL Aligned</h4>
                        <p style="font-size: 0.85rem; color: var(--text-muted);">Complies with ISO 15189 standards for diagnostic competence.</p>
                    </div>
                    <div style="background-color: var(--brand-teal-bg); border-left: 4px solid var(--brand-teal); padding: 15px; border-radius: 4px; flex: 1;">
                        <h4 style="color: var(--brand-teal); font-weight: 700; margin-bottom: 4px;">ISO Certified</h4>
                        <p style="font-size: 0.85rem; color: var(--text-muted);">ISO 9001:2015 certified quality management systems.</p>
                    </div>
                </div>
            </div>
            
            <div>
                <img src="images/hero-lab.jpg" alt="Clinical Laboratory Facility" style="border-radius: var(--radius-lg); box-shadow: var(--shadow-xl);">
            </div>
        </div>
    </div>
</section>

<!-- Values Section -->
<section class="section-padding" style="background-color: var(--bg-light); border-top: 1px solid var(--border); border-bottom: 1px solid var(--border);">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Principles</span>
            <h2 class="section-title">Our Laboratory Core Values</h2>
            <p class="section-desc max-w-md">Our daily clinical operations are built upon four fundamental cornerstones of modern healthcare.</p>
        </div>
        
        <div class="grid-4">
            <div class="card" style="background-color: var(--white); text-align: center; padding: 30px 20px;">
                <div class="service-icon" style="color: var(--brand-teal); background-color: var(--brand-teal-bg);"><i class="fa-solid fa-clipboard-check"></i></div>
                <h3 style="font-size: 1.2rem; margin-bottom: 10px;">Absolute Integrity</h3>
                <p style="font-size: 0.9rem; color: var(--text-muted);">Uncompromised quality standards with strict internal control assays and zero tolerance for report errors.</p>
            </div>
            <div class="card" style="background-color: var(--white); text-align: center; padding: 30px 20px;">
                <div class="service-icon" style="color: var(--brand-teal); background-color: var(--brand-teal-bg);"><i class="fa-solid fa-user-doctor"></i></div>
                <h3 style="font-size: 1.2rem; margin-bottom: 10px;">Patient First</h3>
                <p style="font-size: 0.9rem; color: var(--text-muted);">Dedicated to safety, gentle sample collection technique, responsive counseling, and patient confidentiality.</p>
            </div>
            <div class="card" style="background-color: var(--white); text-align: center; padding: 30px 20px;">
                <div class="service-icon" style="color: var(--brand-teal); background-color: var(--brand-teal-bg);"><i class="fa-solid fa-microscope"></i></div>
                <h3 style="font-size: 1.2rem; margin-bottom: 10px;">Advanced Tech</h3>
                <p style="font-size: 0.9rem; color: var(--text-muted);">Investing continuously in the latest chemistry, hematology, and immunodiagnostic analyzer modules.</p>
            </div>
            <div class="card" style="background-color: var(--white); text-align: center; padding: 30px 20px;">
                <div class="service-icon" style="color: var(--brand-teal); background-color: var(--brand-teal-bg);"><i class="fa-solid fa-shield-halved"></i></div>
                <h3 style="font-size: 1.2rem; margin-bottom: 10px;">Safe Cold-Chain</h3>
                <p style="font-size: 0.9rem; color: var(--text-muted);">Strict temperature control protocol during home collection transport to protect sample degradation.</p>
            </div>
        </div>
    </div>
</section>

<!-- Pathologist Profiles Section -->
<section class="section-padding">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Medical Directors</span>
            <h2 class="section-title">Meet Our Diagnostic Experts</h2>
            <p class="section-desc max-w-md">Our laboratory reports are verified and signed by board-certified clinical pathologists.</p>
        </div>
        
        <div class="grid-3">
            <!-- Doctor 1 -->
            <div class="card" style="display: flex; gap: 20px; align-items: center; padding: 30px; flex-direction: column; text-align: center;">
                <div class="author-avatar" style="width: 100px; height: 100px; font-size: 2.2rem; border-radius: 50%; flex-shrink: 0; background: linear-gradient(135deg, var(--brand-blue), var(--brand-teal));">SV</div>
                <div>
                    <h3 style="font-size: 1.3rem; margin-bottom: 4px;">Dr. Sunita Verma</h3>
                    <p style="color: var(--brand-teal); font-weight: 600; font-size: 0.9rem; margin-bottom: 10px;">Chief Pathologist & Medical Director</p>
                    <p style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.6;">Dr. Sunita holds an MD in Pathology from AIIMS Delhi with over 15 years of diagnostic laboratory experience. She specializes in clinical biochemistry validation.</p>
                </div>
            </div>
            <!-- Doctor 2 -->
            <div class="card" style="display: flex; gap: 20px; align-items: center; padding: 30px; flex-direction: column; text-align: center;">
                <div class="author-avatar" style="width: 100px; height: 100px; font-size: 2.2rem; border-radius: 50%; flex-shrink: 0; background: linear-gradient(135deg, var(--brand-blue), var(--brand-teal));">RG</div>
                <div>
                    <h3 style="font-size: 1.3rem; margin-bottom: 4px;">Dr. Raman Gupta</h3>
                    <p style="color: var(--brand-teal); font-weight: 600; font-size: 0.9rem; margin-bottom: 10px;">Senior Microbiologist</p>
                    <p style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.6;">Dr. Raman completed his MD in Medical Microbiology and specializes in immunology, culture analytics, and infectious disease diagnostics with over 12 years of clinical service.</p>
                </div>
            </div>
            <!-- Staff 3 -->
            <div class="card" style="display: flex; gap: 20px; align-items: center; padding: 30px; flex-direction: column; text-align: center;">
                <div class="author-avatar" style="width: 100px; height: 100px; font-size: 2.2rem; border-radius: 50%; flex-shrink: 0; background: linear-gradient(135deg, var(--brand-blue), var(--brand-teal));">AR</div>
                <div>
                    <h3 style="font-size: 1.3rem; margin-bottom: 4px;">Akshay Sanjay Rakh</h3>
                    <p style="color: var(--brand-teal); font-weight: 600; font-size: 0.9rem; margin-bottom: 10px;">Senior Lab Technician (ADMLT)</p>
                    <p style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.6;">Akshay holds an Advanced Diploma in Medical Laboratory Technology (ADMLT) with First Class Distinction. He oversees laboratory instrumentation, sample runs, and quality assurance controls.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
include 'includes/footer.php';
?>
