<!-- Online Report Download Section -->
<section id="report-download" class="download-section section-padding">
    <div class="container max-w-md">
        <div class="section-header text-center">
            <span class="section-tag" style="color: var(--brand-teal-light);">Patient Portal</span>
            <h2 class="section-title">Download Lab Report Online</h2>
            <p class="section-desc" style="color: var(--border);">Enter your Patient ID and Mobile Number below to download your clinical laboratory report PDF securely.</p>
        </div>
        
        <div class="download-card">
            <form id="downloadForm">
                <div class="form-group">
                    <label for="patient_id" class="form-label">Patient ID / Booking ID</label>
                    <input type="text" id="patient_id" name="patient_id" class="form-control" placeholder="e.g. PAT-1001" required>
                </div>
                <div class="form-group">
                    <label for="mobile" class="form-label">Registered Mobile Number</label>
                    <input type="tel" id="mobile" name="mobile" class="form-control" placeholder="e.g. 9876543210" required>
                </div>
                <button type="submit" class="btn btn-teal w-full" style="margin-top: 10px;">
                    <i class="fa-solid fa-cloud-arrow-down"></i> Download Report PDF
                </button>
            </form>
        </div>
    </div>
</section>
