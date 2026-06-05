<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/db.php';
include BASE_PATH . 'includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/analytics.css">

<div class="analytics-page-wrapper">
    <div class="analytics-overlay"></div>

    <div class="container position-relative z-3 pb-5 analytics-content" style="padding-top: 6rem; min-height: 80vh;">
        
        <div class="text-center mb-3 pb-4">
            <span class="custom-badge-category mb-3 d-inline-block text-uppercase text-info border-info">Organizer Tools</span>
            <h1 class="display-4 fw-bold text-white mb-3" id="analytics-h1">Know your crowd.</h1>
            <p class="text-secondary fs-5 mx-auto" id="analytics-p" style="max-width: 650px;">Stop guessing. Start measuring. The Spot gives you the insights you need to grow your events, understand your audience and sell out faster.</p>
        </div>

        <div class="row g-4 justify-content-center mb-5">
            
            <div class="col-lg-4 col-md-6">
                <div class="dark-card feature-card p-4 p-md-5 h-100 shadow-lg text-center">
                    <div class="feature-icon-wrapper text-cyan mb-4 mx-auto">
                        <i class="fa-solid fa-users-rays fa-2x"></i>
                    </div>
                    <h4 class="text-white fw-bold mb-3">Real-time RSVPs</h4>
                    <p class="text-secondary mb-0">Watch your guest list grow second by second. Track exactly how many people have joined your event directly from your Organizer Dashboard.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="dark-card feature-card p-4 p-md-5 h-100 shadow-lg text-center">
                    <div class="feature-icon-wrapper text-success mb-4 mx-auto" style="background-color: rgba(40, 192, 141, 0.1);">
                        <i class="fa-solid fa-eye fa-2x"></i>
                    </div>
                    <h4 class="text-white fw-bold mb-3">Page Insights</h4>
                    <p class="text-secondary mb-0">See how many explorers are viewing your event page. Compare page views against actual joins to understand your conversion rate. <br><span class="badge bg-secondary mt-2">Coming Soon</span></p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="dark-card feature-card p-4 p-md-5 h-100 shadow-lg text-center">
                    <div class="feature-icon-wrapper text-warning mb-4 mx-auto" style="background-color: rgba(245, 158, 11, 0.1);">
                        <i class="fa-solid fa-chart-pie fa-2x"></i>
                    </div>
                    <h4 class="text-white fw-bold mb-3">Audience Demographics</h4>
                    <p class="text-secondary mb-0">Discover the age groups, locations and preferences of your attendees so you can tailor your future marketing campaigns perfectly. <br><span class="badge bg-secondary mt-2">Coming Soon</span></p>
                </div>
            </div>

        </div>

        <div class="row justify-content-center mt-5">
            <div class="col-lg-10">
                <div class="dark-card p-4 p-md-5 shadow-lg border-cyan-subtle text-center text-md-start d-flex flex-column flex-md-row align-items-center justify-content-between">
                    <div class="mb-4 mb-md-0">
                        <h3 class="text-white fw-bold mb-2">Ready to look at the numbers?</h3>
                        <p class="text-secondary mb-0">Head over to your dashboard to track your current active events.</p>
                    </div>
                    <div>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="<?php echo BASE_URL; ?>user/dashboard.php" class="btn custom-submit-btn rounded-pill px-5 py-3 fw-bold shadow-lg text-nowrap" id="pop-up-screen-buttons">
                                OPEN DASHBOARD
                            </a>
                        <?php else: ?>
                            <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" class="btn custom-submit-btn rounded-pill px-5 py-3 fw-bold shadow-lg text-nowrap" id="pop-up-screen-buttons">
                                LOG IN TO ACCESS
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include BASE_PATH . 'includes/footer.php'; ?>