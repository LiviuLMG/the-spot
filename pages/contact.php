<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/db.php';
include BASE_PATH . 'includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/contact.css">

<div class="contact-page-wrapper">

    <div class="contact-overlay"></div>

    <div class="container position-relative z-3 pb-5 contact-content" style="padding-top: 5rem; min-height: 75vh;">
        <div class="row gx-lg-5 align-items-center">

            <div class="col-lg-5 mb-5 mb-lg-0 text-center text-lg-start">
                <span class="custom-badge-category mb-3 d-inline-block text-uppercase text-info border-info mx-auto mx-lg-0">Support</span>
                <h1 class="display-4 fw-bold text-white mb-2" id="contact-h1">Get in touch</h1>
                <p class="text-secondary fs-5 mb-4 mx-auto mx-lg-0" id="contact-p" style="max-width: 400px;">Have a question about an event? Want to partner with us or report a bug? Drop us a message and we'll get back to you shortly.</p>

                <div class="d-inline-flex flex-column text-start">
                    <div class="d-flex align-items-center mb-4">
                        <div class="stat-icon-wrapper me-3">
                            <i class="fa-solid fa-envelope"></i>
                        </div>
                        <div>
                            <h6 class="text-white mb-1 fw-bold">Email Us</h6>
                            <a href="mailto:contact@thespot.ro" class="text-secondary text-decoration-none">contact@thespot.ro</a>
                        </div>
                    </div>

                    <div class="d-flex align-items-center mb-4">
                        <div class="stat-icon-wrapper me-3">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                        <div>
                            <h6 class="text-white mb-1 fw-bold">Office</h6>
                            <span class="text-secondary">Slatina Olt, Romania</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="dark-card p-4 custom-p-md shadow-lg" style="background-color: rgba(15, 23, 42, 0.7); backdrop-filter: blur(10px);">
                    <h4 class="fw-bold text-white mb-4">Send a Message</h4>

                    <form id="contactForm" method="POST" action="<?php echo BASE_URL; ?>actions/process_contact.php">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                        <div class="row gx-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label custom-label">Your Name</label>
                                <input type="text" name="name" class="form-control custom-input" placeholder="John Doe" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label custom-label">Email Address</label>
                                <input type="email" name="email" class="form-control custom-input" placeholder="john@example.com" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label custom-label">Subject</label>

                            <input type="hidden" name="subject" id="subject-hidden-input" required>

                            <div class="dropdown">
                                <button class="form-control custom-input d-flex justify-content-between align-items-center w-100 text-start shadow-none" type="button" id="subjectDropdownBtn" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false" style="font-weight: 400; cursor: pointer;">
                                    <span id="subject-selected-text" style="color: #64748b;">Choose a subject...</span>
                                    <i class="fa-solid fa-chevron-down category-arrow" style="font-size: 0.8rem; color: #64748b; transition: transform 0.3s ease;"></i>
                                </button>

                                <ul class="dropdown-menu w-100 glass-dropdown-menu border-0 shadow-lg mt-2" aria-labelledby="subjectDropdownBtn">
                                    <li><a class="dropdown-item subject-item" href="#" data-value="General Inquiry">General Inquiry</a></li>
                                    <li><a class="dropdown-item subject-item" href="#" data-value="Event Support">Event Support</a></li>
                                    <li><a class="dropdown-item subject-item" href="#" data-value="Partnership">Partnership</a></li>
                                    <li><a class="dropdown-item subject-item" href="#" data-value="Bug Report">Bug Report</a></li>
                                </ul>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label custom-label">Message</label>
                            <textarea name="message" class="form-control custom-input" rows="5" placeholder="How can we help you?" required></textarea>
                        </div>

                        <div
                            class="cf-turnstile mb-4 d-flex justify-content-center"
                            data-sitekey="<?php echo htmlspecialchars($_ENV['TURNSTILE_SITEKEY'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-theme="dark"
                            data-appearance="interaction-only">
                        </div>
                        
                        <button type="submit" class="btn btn-outline-info w-100 rounded-pill fw-bold py-3" style="border-width: 2px;">
                            <i class="fa-solid fa-paper-plane me-2"></i> SEND MESSAGE
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include BASE_PATH . 'includes/footer.php'; ?>