<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/db.php';
include BASE_PATH . 'includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/how-it-works.css">

<div class="hiw-page-wrapper">
    <div class="hiw-overlay"></div>

    <div class="container position-relative z-3 pb-5 hiw-content" style="padding-top: 6rem; min-height: 80vh;">
        
        <div class="text-center mb-3 pb-3">
            <span class="custom-badge-category mb-3 d-inline-block text-uppercase text-warning border-warning">Platform Guide</span>
            <h1 class="display-4 fw-bold text-white mb-1" id="howitworks-h1">Your nightlife, simplified.</h1>
            <p class="text-secondary fs-5 mx-auto" id="howitworks-p" style="max-width: 650px;">Whether you're looking for the best party in town or organizing a massive festival, The Spot connects you with the right crowd.</p>
        </div>

        <div class="mb-0">
            <h3 class="fw-bold text-white mb-4 text-center text-md-start">For Explorers</h3>
            <div class="row g-4">
                
                <div class="col-md-4">
                    <div class="dark-card step-card p-4 text-center h-100 shadow-lg">
                        <div class="step-icon-wrapper text-info mb-4 mx-auto">
                            <i class="fa-solid fa-magnifying-glass fa-2x"></i>
                        </div>
                        <h5 class="text-white fw-bold mb-3">1. Discover</h5>
                        <p class="text-secondary mb-0">Browse through thousands of events across different cities. Filter by category, date or vibe to find exactly what you're looking for.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="dark-card step-card p-4 text-center h-100 shadow-lg">
                        <div class="step-icon-wrapper text-warning mb-4 mx-auto" style="background-color: rgba(245, 158, 11, 0.1);">
                            <i class="fa-solid fa-ticket fa-2x"></i>
                        </div>
                        <h5 class="text-white fw-bold mb-3">2. Join & Book</h5>
                        <p class="text-secondary mb-0">Found the perfect spot? Hit 'Join' to secure your place. Track all your upcoming events directly from your personal dashboard.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="dark-card step-card p-4 text-center h-100 shadow-lg">
                        <div class="step-icon-wrapper text-danger mb-4 mx-auto" style="background-color: rgba(239, 68, 68, 0.1);">
                            <i class="fa-solid fa-martini-glass-citrus fa-2x"></i>
                        </div>
                        <h5 class="text-white fw-bold mb-3">3. Experience</h5>
                        <p class="text-secondary mb-0">Show up, meet the crowd and make memories. Share the events with your friends and explore the city's best nightlife.</p>
                    </div>
                </div>

            </div>
        </div>

        <div class="mb-2 mt-5 pt-4 border-top border-secondary" style="border-color: rgba(255,255,255,0.1) !important;">
            <h3 class="fw-bold text-white mb-4 mt-4 text-center text-md-start">For Organizers</h3>
            <div class="row g-4">
                
                <div class="col-md-4">
                    <div class="dark-card step-card p-4 text-center h-100 shadow-lg">
                        <div class="step-icon-wrapper text-success mb-4 mx-auto" style="background-color: rgba(40, 192, 141, 0.1);">
                            <i class="fa-solid fa-calendar-plus fa-2x"></i>
                        </div>
                        <h5 class="text-white fw-bold mb-3">1. Create</h5>
                        <p class="text-secondary mb-0">Set up your event page in minutes. Add a description, set the location, upload a poster and choose your ticket price.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="dark-card step-card p-4 text-center h-100 shadow-lg">
                        <div class="step-icon-wrapper text-primary mb-4 mx-auto" style="background-color: rgba(59, 130, 246, 0.1);">
                            <i class="fa-solid fa-users-viewfinder fa-2x"></i>
                        </div>
                        <h5 class="text-white fw-bold mb-3">2. Reach</h5>
                        <p class="text-secondary mb-0">Instantly publish your event to an active community of partygoers. Gain visibility in top cities and specific categories.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="dark-card step-card p-4 text-center h-100 shadow-lg">
                        <div class="step-icon-wrapper text-info mb-4 mx-auto" style="background-color: rgba(0, 204, 255, 0.1);">
                            <i class="fa-solid fa-chart-line fa-2x"></i>
                        </div>
                        <h5 class="text-white fw-bold mb-3">3. Manage</h5>
                        <p class="text-secondary mb-0">Track attendees in real-time, edit your event details on the fly and use the organizer dashboard to manage everything effortlessly.</p>
                    </div>
                </div>

            </div>
        </div>

        <div class="text-center mt-3 pt-4">
            <a href="<?php echo BASE_URL; ?>register.php" class="btn btn-outline-info rounded-pill px-5 py-3 fw-bold shadow-lg" style="border-width: 2px;">
                START YOUR JOURNEY NOW
            </a>
        </div>

    </div>
</div>

<?php include BASE_PATH . 'includes/footer.php'; ?>