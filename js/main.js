document.addEventListener('DOMContentLoaded', () => {
    // 1. Mobile Menu Toggle
    const menuToggle = document.querySelector('.menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (menuToggle && navMenu) {
        menuToggle.addEventListener('click', () => {
            navMenu.classList.toggle('active');
            menuToggle.innerHTML = navMenu.classList.contains('active') ? '✕' : '☰';
        });
        
        // Close menu when clicking outside or on links
        document.addEventListener('click', (e) => {
            if (!navMenu.contains(e.target) && !menuToggle.contains(e.target)) {
                navMenu.classList.remove('active');
                menuToggle.innerHTML = '☰';
            }
        });
    }

    // 2. FAQ Accordion Toggle
    const faqQuestions = document.querySelectorAll('.faq-question');
    faqQuestions.forEach(question => {
        question.addEventListener('click', () => {
            const item = question.parentElement;
            
            // Toggle active state of current item
            item.classList.toggle('active');
            
            // Close other items
            document.querySelectorAll('.faq-item').forEach(otherItem => {
                if (otherItem !== item) {
                    otherItem.classList.remove('active');
                }
            });
        });
    });

    // 3. AJAX Submission: Home Collection Booking Form
    const bookingForm = document.getElementById('bookingForm');
    if (bookingForm) {
        bookingForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const submitBtn = bookingForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Processing...';
            
            // Clear existing alerts
            removeAlerts(bookingForm);

            const formData = new FormData(bookingForm);
            
            try {
                const response = await fetch('api/book.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert(bookingForm, 'success', result.message);
                    bookingForm.reset();
                } else {
                    showAlert(bookingForm, 'error', result.message || 'Something went wrong. Please try again.');
                }
            } catch (error) {
                showAlert(bookingForm, 'error', 'Network error. Please try again later.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    }

    // 4. AJAX Submission: Contact / Inquiry Form
    const inquiryForm = document.getElementById('inquiryForm');
    if (inquiryForm) {
        inquiryForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const submitBtn = inquiryForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Sending...';
            
            removeAlerts(inquiryForm);
            
            const formData = new FormData(inquiryForm);
            
            try {
                const response = await fetch('api/inquiry.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert(inquiryForm, 'success', result.message);
                    inquiryForm.reset();
                } else {
                    showAlert(inquiryForm, 'error', result.message || 'Something went wrong. Please try again.');
                }
            } catch (error) {
                showAlert(inquiryForm, 'error', 'Network error. Please try again later.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    }

    // 5. AJAX Submission: Report Download Form
    const downloadForm = document.getElementById('downloadForm');
    if (downloadForm) {
        downloadForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const submitBtn = downloadForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Verifying...';
            
            removeAlerts(downloadForm);
            
            const patientId = document.getElementById('patient_id').value.trim();
            const mobile = document.getElementById('mobile').value.trim();
            
            if (!patientId || !mobile) {
                showAlert(downloadForm, 'error', 'Please fill in both Patient ID and Mobile Number.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                return;
            }
            
            try {
                // We make a fetch request to download-report check
                const response = await fetch(`api/download-report.php?check=1&patient_id=${encodeURIComponent(patientId)}&mobile=${encodeURIComponent(mobile)}`);
                const result = await response.json();
                
                if (result.success) {
                    showAlert(downloadForm, 'success', 'Verification successful! Starting download...');
                    // Redirect browser to trigger PDF streaming
                    window.location.href = `api/download-report.php?patient_id=${encodeURIComponent(patientId)}&mobile=${encodeURIComponent(mobile)}`;
                } else {
                    showAlert(downloadForm, 'error', result.message || 'Report not found. Please double-check details or contact the lab.');
                }
            } catch (error) {
                showAlert(downloadForm, 'error', 'Verification failed due to a network error.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    }

    // Helper functions for alerts
    function showAlert(form, type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.innerHTML = message;
        
        // Insert alert at the top of the form
        form.insertBefore(alertDiv, form.firstChild);
        
        // Smooth scroll to alert if needed
        alertDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function removeAlerts(form) {
        const alerts = form.querySelectorAll('.alert');
        alerts.forEach(alert => alert.remove());
    }
});
