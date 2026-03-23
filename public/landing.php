<?php
require_once dirname(__DIR__) . '/config/config.php';
$pageTitle = 'People Service — Person-Centred Care Management';
include INCLUDES_PATH . '/public_header.php';
?>

<style>
/* ── Hero ───────────────────────────────────────────────────────────────────── */
.hero { background: white; padding: 5rem 0; }
.hero-content {
    max-width: 1200px; margin: 0 auto; padding: 0 20px;
    display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center;
}
.hero h1 { font-size: 3rem; font-weight: 700; color: #1f2937; line-height: 1.2; margin-bottom: 1.25rem; }
.hero h1 span { color: #7c3aed; }
.hero-subtitle { font-size: 1.2rem; color: #4b5563; line-height: 1.7; margin-bottom: 1.75rem; }
.hero-features { list-style: none; margin-bottom: 2rem; display: flex; flex-direction: column; gap: 0.875rem; }
.hero-features li { display: flex; align-items: flex-start; gap: 0.75rem; color: #374151; font-size: 1rem; }
.hero-features li i { color: #7c3aed; font-size: 1.1rem; margin-top: 0.2rem; flex-shrink: 0; }
.hero-features li:nth-child(even) i { color: #10b981; }
.hero-cta { display: flex; gap: 1rem; flex-wrap: wrap; }
.btn-hero {
    padding: 0.875rem 2rem; font-size: 1.05rem; font-weight: 600;
    border-radius: 0.5rem; text-decoration: none; display: inline-flex;
    align-items: center; gap: 0.5rem; transition: all 0.3s;
}
.btn-hero-primary { background: #7c3aed; color: white; }
.btn-hero-primary:hover { background: #6d28d9; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(124,58,237,0.4); }
.btn-hero-outline { background: transparent; color: #7c3aed; border: 2px solid #7c3aed; }
.btn-hero-outline:hover { background: #f5f3ff; transform: translateY(-2px); }
.hero-image img { width: 100%; height: 520px; object-fit: cover; border-radius: 1rem; box-shadow: 0 8px 32px rgba(0,0,0,0.12); }

/* ── Intro banner ────────────────────────────────────────────────────────────── */
.intro-banner {
    background: linear-gradient(135deg, #7c3aed 0%, #4c1d95 100%);
    color: white; padding: 3rem 0; text-align: center;
}
.intro-banner h2 { font-size: 2rem; margin-bottom: 0.75rem; }
.intro-banner p { font-size: 1.1rem; opacity: 0.9; max-width: 640px; margin: 0 auto; }

/* ── Feature slider ──────────────────────────────────────────────────────────── */
.slider-wrapper { max-width: 1200px; margin: 4rem auto; padding: 0 20px; }
.slider-wrapper h2 { font-size: 2rem; font-weight: 700; color: #1f2937; margin-bottom: 2rem; text-align: center; }
.slider { position: relative; height: 560px; border-radius: 1rem; overflow: hidden; }
.slides { display: flex; width: 300%; height: 100%; transition: transform 0.5s ease-in-out; }
.slide {
    width: 33.333%; height: 100%; position: relative; flex-shrink: 0;
    background-size: cover; background-position: center;
}
.slide::before {
    content: ''; position: absolute; inset: 0;
    background: linear-gradient(135deg, rgba(76,29,149,0.55) 0%, rgba(124,58,237,0.4) 100%); z-index: 1;
}
.slide-content {
    position: relative; z-index: 2; height: 100%; display: flex; flex-direction: column;
    justify-content: center; padding: 3rem 3rem 3rem 5rem; color: white; max-width: 680px;
    background: rgba(0,0,0,0.25); border-radius: 1rem; backdrop-filter: blur(3px); margin-left: 24px;
}
.slide-content h3 { font-size: 2.25rem; font-weight: 700; margin-bottom: 1rem; }
.slide-content p { font-size: 1.1rem; line-height: 1.8; opacity: 0.95; }
.slider-btn {
    position: absolute; top: 50%; transform: translateY(-50%);
    background: rgba(255,255,255,0.9); border: none; width: 48px; height: 48px;
    border-radius: 50%; cursor: pointer; font-size: 1.25rem; color: #7c3aed;
    z-index: 10; transition: all 0.2s; box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    display: flex; align-items: center; justify-content: center;
}
.slider-btn:hover { background: white; transform: translateY(-50%) scale(1.1); }
.slider-btn.prev { left: 1.5rem; }
.slider-btn.next { right: 1.5rem; }
.slider-dots { position: absolute; bottom: 1.5rem; left: 50%; transform: translateX(-50%); display: flex; gap: 0.6rem; z-index: 10; }
.slider-dot { width: 11px; height: 11px; border-radius: 50%; background: rgba(255,255,255,0.5); border: 2px solid white; cursor: pointer; transition: all 0.2s; }
.slider-dot.active { background: white; transform: scale(1.2); }

/* ── Feature cards ───────────────────────────────────────────────────────────── */
.features-section { background: #f9fafb; padding: 5rem 0; }
.features-section .inner { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
.features-section h2 { text-align: center; font-size: 2rem; font-weight: 700; color: #1f2937; margin-bottom: 0.75rem; }
.features-section .subtitle { text-align: center; color: #6b7280; font-size: 1.1rem; margin-bottom: 3rem; }
.feature-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.75rem; }
.feature-card {
    background: white; padding: 2rem; border-radius: 0.75rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.07); transition: all 0.3s;
}
.feature-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,0.12); }
.feature-card i { font-size: 2.5rem; color: #7c3aed; margin-bottom: 1rem; display: block; }
.feature-card:nth-child(3n+2) i { color: #10b981; }
.feature-card:nth-child(3n+3) i { color: #2563eb; }
.feature-card h3 { font-size: 1.15rem; font-weight: 600; color: #1f2937; margin-bottom: 0.75rem; }
.feature-card p { color: #6b7280; line-height: 1.7; font-size: 0.95rem; }

/* ── Feature sections ────────────────────────────────────────────────────────── */
.feature-section { padding: 5rem 0; background: white; }
.feature-section:nth-child(even) { background: #f9fafb; }
.feature-section .inner {
    max-width: 1200px; margin: 0 auto; padding: 0 20px;
    display: grid; grid-template-columns: 1fr 1fr; gap: 5rem; align-items: center;
}
.feature-section.reverse .inner { direction: rtl; }
.feature-section.reverse .inner > * { direction: ltr; }
.feature-section .text h2 { font-size: 2.25rem; font-weight: 700; color: #1f2937; margin-bottom: 1.25rem; line-height: 1.3; }
.feature-section .text p { color: #4b5563; line-height: 1.8; font-size: 1.05rem; margin-bottom: 1rem; }
.feature-section .text p:last-child { margin-bottom: 0; }
.feature-section img { width: 100%; height: 420px; object-fit: cover; border-radius: 1rem; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }

/* ── CTA ─────────────────────────────────────────────────────────────────────── */
.cta-section {
    background: linear-gradient(135deg, #7c3aed 0%, #4c1d95 100%);
    color: white; padding: 5rem 0; text-align: center;
}
.cta-section h2 { font-size: 2.5rem; font-weight: 700; margin-bottom: 1rem; }
.cta-section p { font-size: 1.2rem; opacity: 0.9; margin-bottom: 2rem; max-width: 600px; margin-left: auto; margin-right: auto; }
.btn-hero-white { background: white; color: #7c3aed; padding: 1rem 2.5rem; font-size: 1.1rem; font-weight: 700; border-radius: 0.5rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.3s; }
.btn-hero-white:hover { background: #faf5ff; transform: translateY(-2px); box-shadow: 0 4px 16px rgba(0,0,0,0.2); }

/* ── Responsive ──────────────────────────────────────────────────────────────── */
@media (max-width: 968px) {
    .hero-content { grid-template-columns: 1fr; gap: 2.5rem; }
    .hero h1 { font-size: 2.25rem; }
    .hero-image img { height: 320px; }
    .feature-grid { grid-template-columns: 1fr 1fr; }
    .feature-section .inner { grid-template-columns: 1fr; gap: 2.5rem; }
    .feature-section.reverse .inner { direction: ltr; }
    .feature-section img { height: 280px; }
    .slider { height: 480px; }
    .slide-content { padding: 2rem 2rem 2rem 3rem; }
    .slide-content h3 { font-size: 1.75rem; }
}
@media (max-width: 640px) {
    .hero { padding: 3rem 0; }
    .hero h1 { font-size: 1.875rem; }
    .feature-grid { grid-template-columns: 1fr; }
    .cta-section h2 { font-size: 1.875rem; }
}
</style>

<!-- ── Hero ────────────────────────────────────────────────────────────────── -->
<section class="hero">
    <div class="hero-content">
        <div class="hero-text">
            <h1>Person-Centred Care,<br><span>Digitally Managed.</span></h1>
            <p class="hero-subtitle">People Service gives social care providers a secure, complete digital record for every person they support — care needs, contacts, key workers, and more — all in one place.</p>
            <ul class="hero-features">
                <li><i class="fas fa-user-circle"></i> Complete profiles for every person you support</li>
                <li><i class="fas fa-heart"></i> Track care needs by category and severity</li>
                <li><i class="fas fa-user-tie"></i> Assign and manage key worker relationships</li>
                <li><i class="fas fa-address-book"></i> Record emergency contacts and next of kin</li>
                <li><i class="fas fa-plug"></i> Connects with your Staff Service for live staff data</li>
                <li><i class="fas fa-shield-alt"></i> GDPR-compliant, multi-tenant, organisation-isolated</li>
            </ul>
            <div class="hero-cta">
                <a href="<?php echo url('login.php'); ?>" class="btn-hero btn-hero-primary">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </a>
                <a href="<?php echo url('contact.php'); ?>" class="btn-hero btn-hero-outline">
                    <i class="fas fa-envelope"></i> Get in Touch
                </a>
            </div>
        </div>
        <div class="hero-image">
            <img src="https://images.unsplash.com/photo-1576091160550-2173dba999ef?auto=format&fit=crop&w=800&q=80"
                 alt="Care worker supporting a person">
        </div>
    </div>
</section>

<!-- ── Intro banner ───────────────────────────────────────────────────────── -->
<div class="intro-banner">
    <div class="container">
        <h2>Every person. Every need. One secure system.</h2>
        <p>People Service puts the individual at the centre — giving your team everything they need to provide consistent, informed, and compassionate support.</p>
    </div>
</div>

<!-- ── Feature slider ────────────────────────────────────────────────────── -->
<div class="slider-wrapper">
    <h2>Designed for the way care organisations work</h2>
    <div class="slider" id="slider">
        <div class="slides" id="slides">

            <div class="slide" style="background-image:url('https://images.unsplash.com/photo-1559757148-5c350d0d3c56?auto=format&fit=crop&w=1400&q=80')">
                <div class="slide-content">
                    <h3>Complete Person Profiles</h3>
                    <p>Every person you support has a full digital profile — personal details, status, organisational unit, and all their associated records in one place.</p>
                    <p>Quickly see active, inactive, or archived people across your whole organisation.</p>
                </div>
            </div>

            <div class="slide" style="background-image:url('https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=1400&q=80')">
                <div class="slide-content">
                    <h3>Care Needs Tracking</h3>
                    <p>Record care needs by category — mobility, communication, personal care, mental health and more. Assign severity levels so your team always knows what level of support is needed.</p>
                    <p>Group needs by category for a clear, structured view of each person's requirements.</p>
                </div>
            </div>

            <div class="slide" style="background-image:url('https://images.unsplash.com/photo-1551601651-2a8555f1a136?auto=format&fit=crop&w=1400&q=80')">
                <div class="slide-content">
                    <h3>Key Worker Relationships</h3>
                    <p>Assign staff as key workers directly from your Staff Service. Track who is responsible for each person's care, with start dates, role labels, and a full history.</p>
                    <p>Staff details stay in sync — name changes in the Staff Service update automatically.</p>
                </div>
            </div>

        </div>
        <button class="slider-btn prev" onclick="moveSlide(-1)"><i class="fas fa-chevron-left"></i></button>
        <button class="slider-btn next" onclick="moveSlide(1)"><i class="fas fa-chevron-right"></i></button>
        <div class="slider-dots" id="dots">
            <div class="slider-dot active" onclick="goToSlide(0)"></div>
            <div class="slider-dot" onclick="goToSlide(1)"></div>
            <div class="slider-dot" onclick="goToSlide(2)"></div>
        </div>
    </div>
</div>

<!-- ── Feature cards ──────────────────────────────────────────────────────── -->
<section class="features-section">
    <div class="inner">
        <h2>Everything in one place</h2>
        <p class="subtitle">Built specifically for social care — not adapted from generic software.</p>
        <div class="feature-grid">
            <div class="feature-card">
                <i class="fas fa-user-circle"></i>
                <h3>Rich Person Profiles</h3>
                <p>Store all key personal information — name, date of birth, NHS number, status, photo, organisational unit — in a single secure profile.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-heart"></i>
                <h3>Categorised Care Needs</h3>
                <p>Record care needs across multiple categories with severity ratings. A structured view of what each person needs and how urgently.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-user-tie"></i>
                <h3>Key Worker Management</h3>
                <p>Assign key workers from your Staff Service, record their role, and track the relationship over time with start and end dates.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-address-book"></i>
                <h3>Emergency Contacts</h3>
                <p>Store next of kin, emergency contacts, GPs, and other important contacts — each with relationship type, phone, and email.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-plug"></i>
                <h3>Staff Service Integration</h3>
                <p>Connect to your Staff Service so key workers can be selected from live staff data — no manual entry, no duplicated records.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-building"></i>
                <h3>Multi-Organisation</h3>
                <p>Support multiple organisations from one instance. Each organisation's people, care needs, and contacts are completely isolated.</p>
            </div>
        </div>
    </div>
</section>

<!-- ── Feature sections ───────────────────────────────────────────────────── -->
<section class="feature-section">
    <div class="inner">
        <div class="text">
            <h2>A complete picture of every person you support</h2>
            <p>Good care depends on good information. People Service gives your team a single, authoritative record for each person — so whoever picks up a shift always has the context they need.</p>
            <p>Profiles capture everything from basic personal details to organisational unit placement, active status, and associated care needs — all in one place, always up to date.</p>
        </div>
        <img src="https://images.unsplash.com/photo-1477281765962-ef34e8bb0967?auto=format&fit=crop&w=700&q=80"
             alt="Care professional reviewing a person's care plan">
    </div>
</section>

<section class="feature-section reverse">
    <div class="inner">
        <div class="text">
            <h2>Care needs that are clear, structured, and accessible</h2>
            <p>Every person's care needs are different. People Service lets you record needs across any number of categories — and assign a severity level to each — so support can be prioritised and planned appropriately.</p>
            <p>Grouped by category, care needs give any member of staff a clear picture of what a person requires and how critical each need is — before they walk through the door.</p>
        </div>
        <img src="https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=700&q=80"
             alt="Care planning in a care setting">
    </div>
</section>

<section class="feature-section">
    <div class="inner">
        <div class="text">
            <h2>Key worker relationships, tracked over time</h2>
            <p>Knowing who is responsible for each person's care — and who was responsible in the past — matters. People Service records key worker assignments with start dates, role labels, and a full history of past relationships.</p>
            <p>When a key worker changes, the history stays. When staff names change in the Staff Service, they update automatically. The record is always accurate.</p>
        </div>
        <img src="https://images.unsplash.com/photo-1551601651-2a8555f1a136?auto=format&fit=crop&w=700&q=80"
             alt="Key worker with a person they support">
    </div>
</section>

<!-- ── CTA ────────────────────────────────────────────────────────────────── -->
<section class="cta-section">
    <div class="container">
        <h2>Ready to put people first?</h2>
        <p>Sign in to get started, or get in touch to find out how People Service can work for your organisation.</p>
        <a href="<?php echo url('login.php'); ?>" class="btn-hero-white">
            <i class="fas fa-sign-in-alt"></i> Sign In
        </a>
        &nbsp;&nbsp;
        <a href="<?php echo url('contact.php'); ?>" style="color:rgba(255,255,255,0.85);text-decoration:none;font-size:1rem;margin-left:.5rem">
            or contact us <i class="fas fa-arrow-right"></i>
        </a>
    </div>
</section>

<script>
var current = 0;
var total   = 3;
var timer;

function goToSlide(n) {
    current = n;
    document.getElementById('slides').style.transform = 'translateX(-' + (100/3 * n) + '%)';
    document.querySelectorAll('.slider-dot').forEach(function(d, i) {
        d.classList.toggle('active', i === n);
    });
}
function moveSlide(dir) {
    current = (current + dir + total) % total;
    goToSlide(current);
    resetTimer();
}
function resetTimer() {
    clearInterval(timer);
    timer = setInterval(function() { moveSlide(1); }, 6000);
}
resetTimer();
</script>

<?php include INCLUDES_PATH . '/public_footer.php'; ?>
