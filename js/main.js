document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    function apiHeaders() {
        const headers = {};
        if (csrfToken) {
            headers['X-CSRF-Token'] = csrfToken;
        }
        return headers;
    }

    function appendCaptcha(formData, form) {
        const captchaInput = form.querySelector('[name="captcha_answer"]');
        if (captchaInput) {
            formData.append('captcha_answer', captchaInput.value);
        }
    }

    // Dynamic header offset for mobile menu
    function updateHeaderOffset() {
        const header = document.querySelector('.site-header');
        const offer = document.querySelector('.offer-banner');
        const topBar = document.querySelector('.top-bar');
        const menuOpen = document.body.classList.contains('menu-open');
        let offset = 0;
        if (offer && !menuOpen) offset += offer.offsetHeight;
        if (topBar && !menuOpen) offset += topBar.offsetHeight;
        if (header) offset += header.offsetHeight;
        document.documentElement.style.setProperty('--header-offset', `${offset}px`);
    }

    // 1. Mobile Menu Toggle
    const menuToggle = document.querySelector('.menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    const navBackdrop = document.getElementById('navBackdrop');
    const siteHeader = document.querySelector('.site-header');
    
    if (menuToggle && navMenu) {
        const setMenuOpen = (open) => {
            navMenu.classList.toggle('active', open);
            navBackdrop?.classList.toggle('is-visible', open);
            navBackdrop?.setAttribute('aria-hidden', open ? 'false' : 'true');
            menuToggle.innerHTML = open ? '✕' : '☰';
            menuToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            document.body.classList.toggle('menu-open', open);
            requestAnimationFrame(updateHeaderOffset);
        };

        menuToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            setMenuOpen(!navMenu.classList.contains('active'));
        });

        navBackdrop?.addEventListener('click', () => setMenuOpen(false));

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && navMenu.classList.contains('active')) {
                setMenuOpen(false);
            }
        });

        navMenu.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => setMenuOpen(false));
        });
    }

    updateHeaderOffset();
    window.addEventListener('resize', updateHeaderOffset, { passive: true });

    // Header scroll effect
    if (siteHeader) {
        const onScroll = () => {
            siteHeader.classList.toggle('scrolled', window.scrollY > 20);
        };
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    // Scroll reveal animations (threshold 0 so tall grids like services catalog still reveal)
    const revealEls = document.querySelectorAll('.reveal, .reveal-stagger');
    if (revealEls.length && 'IntersectionObserver' in window) {
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0, rootMargin: '0px 0px -24px 0px' });

        revealEls.forEach((el) => revealObserver.observe(el));
    } else {
        revealEls.forEach((el) => el.classList.add('is-visible'));
    }

    // 2. FAQ Accordion Toggle
    const faqQuestions = document.querySelectorAll('.faq-question');
    faqQuestions.forEach(question => {
        question.addEventListener('click', () => {
            const item = question.parentElement;
            item.classList.toggle('active');
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
            
            removeAlerts(bookingForm);

            const formData = new FormData(bookingForm);
            if (csrfToken) {
                formData.append('csrf_token', csrfToken);
            }
            appendCaptcha(formData, bookingForm);
            
            try {
                const response = await fetch('api/book.php', {
                    method: 'POST',
                    headers: apiHeaders(),
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
            if (csrfToken) {
                formData.append('csrf_token', csrfToken);
            }
            appendCaptcha(formData, inquiryForm);
            
            try {
                const response = await fetch('api/inquiry.php', {
                    method: 'POST',
                    headers: apiHeaders(),
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

    // 5. Report Download with OTP flow
    const downloadForm = document.getElementById('downloadForm');
    if (downloadForm) {
        const otpEnabled = downloadForm.dataset.otpEnabled === '1';
        const sendOtpBtn = document.getElementById('sendOtpBtn');
        const otpGroup = downloadForm.querySelector('.otp-group');
        const otpInput = document.getElementById('report_otp');

        if (sendOtpBtn) {
            sendOtpBtn.addEventListener('click', async () => {
                removeAlerts(downloadForm);
                removeReportPicker(downloadForm);

                const patientId = document.getElementById('patient_id').value.trim();
                const mobile = document.getElementById('mobile').value.trim();

                if (!patientId || !mobile) {
                    showAlert(downloadForm, 'error', 'Please fill in both Patient ID and Mobile Number.');
                    return;
                }

                sendOtpBtn.disabled = true;
                sendOtpBtn.innerHTML = 'Sending OTP...';

                const formData = new FormData();
                formData.append('action', 'send');
                formData.append('patient_id', patientId);
                formData.append('mobile', mobile);
                if (csrfToken) formData.append('csrf_token', csrfToken);
                appendCaptcha(formData, downloadForm);

                try {
                    const response = await fetch('api/report-otp.php', {
                        method: 'POST',
                        headers: apiHeaders(),
                        body: formData
                    });
                    const result = await response.json();

                    if (result.success) {
                        showAlert(downloadForm, 'success', result.message || 'OTP sent successfully.');
                        if (otpGroup) otpGroup.style.display = 'block';
                        if (otpInput) otpInput.focus();
                    } else {
                        showAlert(downloadForm, 'error', result.message || 'Unable to send OTP.');
                    }
                } catch (error) {
                    showAlert(downloadForm, 'error', 'Network error while sending OTP.');
                } finally {
                    sendOtpBtn.disabled = false;
                    sendOtpBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Send OTP';
                }
            });
        }

        downloadForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const submitBtn = downloadForm.querySelector('#downloadSubmitBtn') || downloadForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = otpEnabled ? 'Verifying OTP...' : 'Verifying...';
            
            removeAlerts(downloadForm);
            removeReportPicker(downloadForm);
            
            const patientId = document.getElementById('patient_id').value.trim();
            const mobile = document.getElementById('mobile').value.trim();
            
            if (!patientId || !mobile) {
                showAlert(downloadForm, 'error', 'Please fill in both Patient ID and Mobile Number.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                return;
            }
            
            try {
                if (otpEnabled) {
                    const otp = otpInput ? otpInput.value.trim() : '';
                    if (!otp) {
                        showAlert(downloadForm, 'error', 'Please enter the OTP sent to your email or mobile.');
                        return;
                    }

                    const verifyData = new FormData();
                    verifyData.append('action', 'verify');
                    verifyData.append('patient_id', patientId);
                    verifyData.append('mobile', mobile);
                    verifyData.append('otp', otp);
                    if (csrfToken) verifyData.append('csrf_token', csrfToken);

                    const verifyResponse = await fetch('api/report-otp.php', {
                        method: 'POST',
                        headers: apiHeaders(),
                        body: verifyData
                    });
                    const verifyResult = await verifyResponse.json();

                    if (!verifyResult.success) {
                        showAlert(downloadForm, 'error', verifyResult.message || 'OTP verification failed.');
                        return;
                    }

                    handleReportsList(downloadForm, patientId, mobile, verifyResult.reports || []);
                    return;
                }

                const formData = new FormData();
                formData.append('action', 'verify');
                formData.append('patient_id', patientId);
                formData.append('mobile', mobile);
                if (csrfToken) formData.append('csrf_token', csrfToken);
                appendCaptcha(formData, downloadForm);

                const response = await fetch('api/report-otp.php', {
                    method: 'POST',
                    headers: apiHeaders(),
                    body: formData
                });
                const result = await response.json();

                if (!result.success) {
                    showAlert(downloadForm, 'error', result.message || 'Report not found.');
                    return;
                }

                handleReportsList(downloadForm, patientId, mobile, result.reports || []);
            } catch (error) {
                showAlert(downloadForm, 'error', 'Verification failed due to a network error.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    }

    function handleReportsList(form, patientId, mobile, reports) {
        if (reports.length === 0) {
            showAlert(form, 'error', 'No reports available for this patient.');
            return;
        }

        if (reports.length === 1) {
            showAlert(form, 'success', 'Verification successful! Starting download...');
            window.location.href = buildDownloadUrl(patientId, mobile, reports[0].id);
            return;
        }

        showAlert(form, 'success', 'Verification successful. Select a report to download.');
        renderReportPicker(form, patientId, mobile, reports);
    }

    function buildDownloadUrl(patientId, mobile, reportId) {
        return `api/download-report.php?patient_id=${encodeURIComponent(patientId)}&mobile=${encodeURIComponent(mobile)}&report_id=${encodeURIComponent(reportId)}`;
    }

    function renderReportPicker(form, patientId, mobile, reports) {
        const wrapper = document.createElement('div');
        wrapper.className = 'report-picker';
        wrapper.style.marginTop = '16px';

        const label = document.createElement('p');
        label.style.fontWeight = '600';
        label.style.marginBottom = '10px';
        label.textContent = 'Available reports:';
        wrapper.appendChild(label);

        reports.forEach((report) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-secondary w-full';
            btn.style.marginBottom = '8px';
            btn.innerHTML = `<i class="fa-solid fa-file-pdf"></i> ${escapeHtml(report.test_name)}`;
            btn.addEventListener('click', () => {
                window.location.href = buildDownloadUrl(patientId, mobile, report.id);
            });
            wrapper.appendChild(btn);
        });

        form.appendChild(wrapper);
    }

    function removeReportPicker(form) {
        form.querySelectorAll('.report-picker').forEach(el => el.remove());
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function showAlert(form, type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.innerHTML = message;
        form.insertBefore(alertDiv, form.firstChild);
        alertDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function removeAlerts(form) {
        form.querySelectorAll('.alert').forEach(alert => alert.remove());
    }
});
