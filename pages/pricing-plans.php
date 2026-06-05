<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/db.php';
include BASE_PATH . 'includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pricing-plans.css">

<div class="pricing-page-wrapper">
    <div class="pricing-overlay"></div>

    <div class="container position-relative z-3 pb-5 pricing-content" style="padding-top: 6rem; min-height: 80vh;">
        
        <div class="text-center mb-5 pb-3">
            <span class="custom-badge-category mb-3 d-inline-block text-uppercase text-success border-success">For Organizers</span>
            <h1 class="display-4 fw-bold text-white mb-3" id="pricing-h1">Simple, transparent pricing.</h1>
            <p class="text-secondary fs-5 mx-auto" id="pricing-p"style="max-width: 650px;">The Spot is currently in Open Beta. That means early adopters get full access to all our premium features for absolutely zero cost.</p>
        </div>

        <div class="row justify-content-center g-4">
            
            <div class="col-lg-4 col-md-6">
                <div class="dark-card pricing-card featured-card p-4 p-md-5 h-100 shadow-lg text-center position-relative">
                    <div class="position-absolute top-0 start-50 translate-middle badge bg-cyan text-dark rounded-pill px-3 py-2 fw-bold" style="font-size: 0.85rem;">
                        CURRENTLY ACTIVE
                    </div>
                    
                    <h3 class="text-white fw-bold mb-2">Early Adopter</h3>
                    <p class="text-secondary mb-2">Everything you need to launch and manage events today.</p>
                    
                    <div class="mb-4">
                        <span class="display-3 fw-bold text-white">0</span>
                        <span class="text-secondary fw-bold fs-5">RON</span>
                        <div class="text-info small fw-bold mt-1">Free forever for beta users</div>
                    </div>
                    
                    <ul class="list-unstyled text-start mb-4 pricing-features">
                        <li><i class="fa-solid fa-check text-cyan me-2"></i> Unlimited event listings</li>
                        <li><i class="fa-solid fa-check text-cyan me-2"></i> Standard placement in search</li>
                        <li><i class="fa-solid fa-check text-cyan me-2"></i> Attendee management</li>
                        <li><i class="fa-solid fa-check text-cyan me-2"></i> Basic analytics dashboard</li>
                        <li><i class="fa-solid fa-check text-cyan me-2"></i> Email support</li>
                    </ul>
                    
                    <div class="mt-auto pt-4">
                        <a href="<?php echo isset($_SESSION['user_id']) ? BASE_URL . 'user/dashboard.php' : BASE_URL . 'register.php'; ?>" class="btn custom-submit-btn w-100 rounded-pill py-3 fw-bold" id="pop-up-screen-buttons">
                            <?php echo isset($_SESSION['user_id']) ? 'GO TO DASHBOARD' : 'GET STARTED FOR FREE'; ?>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="dark-card pricing-card p-4 p-md-5 h-100 shadow-lg text-center">
                    <h3 class="text-white fw-bold mb-2">Pro Organizer</h3>
                    <p class="text-secondary mb-2">Advanced tools to scale your festivals and club nights.</p>
                    
                    <div class="mb-4">
                        <span class="display-4 fw-bold text-secondary opacity-50">TBA</span>
                        <div class="text-secondary small fw-medium mt-2">Coming in late 2027</div>
                    </div>
                    
                    <ul class="list-unstyled text-start mb-4 pricing-features opacity-75">
                        <li><i class="fa-solid fa-plus text-secondary me-2"></i> Everything in Early Adopter</li>
                        <li><i class="fa-solid fa-star text-warning me-2"></i> Featured event placements</li>
                        <li><i class="fa-solid fa-chart-pie text-secondary me-2"></i> Advanced demographics</li>
                        <li><i class="fa-solid fa-ticket text-secondary me-2"></i> Custom ticketing links</li>
                        <li><i class="fa-solid fa-headset text-secondary me-2"></i> Priority 24/7 support</li>
                    </ul>
                    
                    <div class="mt-auto pt-4">
                        <button class="btn btn-outline-secondary w-100 rounded-pill py-3 fw-bold disabled">
                            COMING SOON
                        </button>
                    </div>
                </div>
            </div>

        </div>

        <div class="row justify-content-center mt-3 pt-5">
            <div class="col-lg-8">
                <h4 class="text-center text-white fw-bold mb-5">Frequently Asked Questions</h4>
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <h6 class="text-white fw-bold"><i class="fa-solid fa-q me-2 text-cyan"></i> Are there any hidden fees?</h6>
                        <p class="text-secondary small">No. The platform is completely free to use for both attendees and organizers during the beta phase. I do not process payments directly, so I take zero commissions.</p>
                    </div>
                    <div class="col-md-6 mb-4">
                        <h6 class="text-white fw-bold"><i class="fa-solid fa-q me-2 text-cyan"></i> What happens after Beta?</h6>
                        <p class="text-secondary small">Users who register now will be grandfathered into special "Early Adopter" pricing tiers or keep core features free forever when I will introduce the Pro plan.</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include BASE_PATH . 'includes/footer.php'; ?>