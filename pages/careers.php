<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/db.php';
include BASE_PATH . 'includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/careers.css">

<div class="careers-page-wrapper">
    <div class="careers-overlay"></div>

    <div class="container position-relative z-3 pb-5 contact-content" style="padding-top: 5rem;">

        <div class="text-center mb-5">
            <span class="custom-badge-category mb-3 d-inline-block text-uppercase text-info border-info">Join the team</span>
            <h1 class="display-4 fw-bold text-white mb-1" id="careers-h1">Build the future of nightlife</h1>
            <p class="text-secondary fs-5 mx-auto" id="careers-p" style="max-width: 600px;">We are always looking for passionate people to help us create unforgettable experiences. Check out our open roles or drop an open application.</p>
        </div>

        <div class="row gx-lg-5">

            <div class="col-lg-5 mb-5 mb-lg-0">
                <h4 class="fw-bold text-white mb-4">Open Positions</h4>

                <div class="job-card mb-3 p-3 rounded-3">
                    <h6 class="text-white fw-bold mb-1">Senior Event Manager</h6>
                    <p class="text-secondary small mb-2">Bucharest / Hybrid</p>
                    <span class="badge bg-transparent border border-secondary text-secondary">Full-time</span>
                </div>

                <div class="job-card mb-3 p-3 rounded-3">
                    <h6 class="text-white fw-bold mb-1">Full Stack Developer</h6>
                    <p class="text-secondary small mb-2">Remote</p>
                    <span class="badge bg-transparent border border-secondary text-secondary">Full-time</span>
                </div>

                <div class="job-card mb-3 p-3 rounded-3">
                    <h6 class="text-white fw-bold mb-1">Marketing Specialist</h6>
                    <p class="text-secondary small mb-2">Cluj-Napoca / On-site</p>
                    <span class="badge bg-transparent border border-secondary text-secondary">Part-time</span>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="dark-card p-4 custom-p-md shadow-lg" style="background-color: rgba(15, 23, 42, 0.7); backdrop-filter: blur(10px);">
                    <h4 class="fw-bold text-white mb-4">Submit your application</h4>

                    <form id="careerForm" method="POST" action="<?php echo BASE_URL; ?>actions/process_career.php">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                        <div class="row gx-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label custom-label">Full Name</label>
                                <input type="text" name="name" class="form-control custom-input" placeholder="Jane Doe" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label custom-label">Email Address</label>
                                <input type="email" name="email" class="form-control custom-input" placeholder="jane@example.com" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label custom-label">Position you are applying for</label>

                            <input type="hidden" name="position" id="career-hidden-input" required>

                            <div class="dropdown">
                                <button class="form-control custom-input d-flex justify-content-between align-items-center w-100 text-start shadow-none" type="button" id="careerDropdownBtn" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false" style="font-weight: 400; cursor: pointer;">
                                    <span id="career-selected-text" style="color: #64748b;">Select position...</span>
                                    <i class="fa-solid fa-chevron-down category-arrow" style="font-size: 0.8rem; color: #64748b; transition: transform 0.3s ease;"></i>
                                </button>

                                <ul class="dropdown-menu w-100 glass-dropdown-menu border-0 shadow-lg mt-2" aria-labelledby="careerDropdownBtn">
                                    <li><a class="dropdown-item career-item" href="#" data-value="Senior Event Manager">Senior Event Manager</a></li>
                                    <li><a class="dropdown-item career-item" href="#" data-value="Full Stack Developer">Full Stack Developer</a></li>
                                    <li><a class="dropdown-item career-item" href="#" data-value="Marketing Specialist">Marketing Specialist</a></li>
                                    <li><a class="dropdown-item career-item" href="#" data-value="Open Application (Other)">Open Application (Other)</a></li>
                                </ul>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label custom-label">LinkedIn or Portfolio URL</label>
                            <input type="url" name="portfolio_url" class="form-control custom-input" placeholder="https://linkedin.com/in/..." required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label custom-label">Cover Letter / Why you?</label>
                            <textarea name="message" class="form-control custom-input" rows="4" placeholder="Tell us why you'd be a great fit..." required></textarea>
                        </div>

                        <div
                            class="cf-turnstile mb-4 d-flex justify-content-center"
                            data-sitekey="<?php echo htmlspecialchars($_ENV['TURNSTILE_SITEKEY'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-theme="dark"
                            data-appearance="interaction-only">
                        </div>
                        
                        <button type="submit" class="btn btn-outline-info w-100 rounded-pill fw-bold py-3" style="border-width: 2px;">
                            <i class="fa-solid fa-paper-plane me-2"></i> SUBMIT APPLICATION
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include BASE_PATH . 'includes/footer.php'; ?>