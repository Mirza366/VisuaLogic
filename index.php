
<?php
session_start();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VISUALOGIC - Design Beyond Pixels</title>

    <meta name="description" content="VISUALOGIC adalah platform kreatif untuk desain grafis, coding, dan teknologi AI.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<!-- NAVBAR -->
<header class="navbar">
    <div class="container nav-content">

        <a href="index.php" class="logo">
            <span class="logo-icon"><i class="fa-solid fa-layer-group"></i></span>
            VISUAL<span>OGIC</span>
        </a>

        <button class="menu-toggle" id="menuToggle" aria-label="Buka menu">
            <i class="fa-solid fa-bars"></i>
        </button>

        <nav class="nav-menu" id="navMenu">
            <a href="#home" class="active">Home</a>
            <a href="#features">Features</a>
            <a href="#about">About</a>
            <a href="#workflow">How It Works</a>
        </nav>

        <div class="nav-actions">
            <a href="auth/login.php" class="btn-login">Login</a>
            <a href="auth/register.php" class="btn-primary btn-small">Get Started <i class="fa-solid fa-arrow-right"></i></a>
        </div>

    </div>
</header>


<!-- HERO SECTION -->
<section class="hero" id="home">

    <div class="hero-glow glow-one"></div>
    <div class="hero-glow glow-two"></div>

    <div class="container hero-grid">

        <div class="hero-content">

            <div class="hero-badge">
                <span class="status-dot"></span>
                THE FUTURE OF CREATIVE WORKSPACE
            </div>

            <h1>
                Design Beyond
                <span>Pixels.</span>
                Create Beyond
                <span>Limits.</span>
            </h1>

            <p class="hero-description">
                Where Visual Creativity Meets Code Intelligence.
                Satu platform untuk menciptakan desain grafis,
                menulis kode, dan mengubah ide menjadi karya digital.
            </p>

            <div class="hero-buttons">
                <a href="auth/register.php" class="btn-primary">
                    Start Creating <i class="fa-solid fa-arrow-right"></i>
                </a>

                <a href="#features" class="btn-outline">
                    Explore Features <i class="fa-solid fa-play"></i>
                </a>
            </div>

            <div class="hero-stats">
                <div>
                    <h3>01</h3>
                    <p>Design Studio</p>
                </div>
                <div>
                    <h3>02</h3>
                    <p>Code Studio</p>
                </div>
                <div>
                    <h3>03</h3>
                    <p>AI Creativity</p>
                </div>
            </div>

        </div>


        <!-- EDITOR PREVIEW -->
        <div class="hero-visual">

            <div class="editor-window">

                <div class="editor-header">
                    <div class="window-dots">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>

                    <div class="editor-title">
                        visualogic / workspace
                    </div>

                    <div class="editor-status">
                        <span></span> Live
                    </div>
                </div>

                <div class="editor-body">

                    <div class="editor-sidebar">
                        <i class="fa-solid fa-layer-group"></i>
                        <i class="fa-solid fa-folder"></i>
                        <i class="fa-solid fa-code"></i>
                        <i class="fa-solid fa-image"></i>
                        <i class="fa-solid fa-gear"></i>
                    </div>

                    <div class="editor-main">

                        <div class="editor-tabs">
                            <span class="tab-active">
                                <i class="fa-brands fa-html5"></i> index.html
                            </span>
                            <span>
                                <i class="fa-brands fa-css3-alt"></i> style.css
                            </span>
                        </div>

                        <div class="code-area">
                            <div class="code-line"><span>01</span> <b class="purple">const</b> project = {</div>
                            <div class="code-line"><span>02</span> &nbsp; name: <b class="green">'Creative Vision'</b>,</div>
                            <div class="code-line"><span>03</span> &nbsp; design: <b class="orange">'Unlimited'</b>,</div>
                            <div class="code-line"><span>04</span> &nbsp; creativity: <b class="blue">'100%'</b></div>
                            <div class="code-line"><span>05</span> };</div>
                            <div class="code-line"><span>06</span></div>
                            <div class="code-line"><span>07</span> <b class="purple">function</b> <b class="blue">createFuture</b>() {</div>
                            <div class="code-line"><span>08</span> &nbsp; <b class="purple">return</b> <b class="green">'Your Idea'</b>;</div>
                            <div class="code-line"><span>09</span> }</div>
                        </div>

                        <div class="preview-card">
                            <div class="preview-label">DESIGN PREVIEW</div>
                            <div class="preview-art">
                                <div class="art-circle"></div>
                                <div class="art-shape"></div>
                                <div class="art-text">
                                    <small>CREATIVE STUDIO</small>
                                    <h2>Think.<br>Design.<br><span>Create.</span></h2>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="editor-footer">
                    <span><i class="fa-solid fa-circle-check"></i> All changes saved</span>
                    <span>VISUALOGIC WORKSPACE</span>
                </div>

            </div>

            <div class="floating-card">
                <div class="floating-icon"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
                <div>
                    <strong>AI Powered</strong>
                    <small>Creative Intelligence</small>
                </div>
            </div>

        </div>

    </div>
</section>


<!-- FEATURES -->
<section class="features section-padding" id="features">

    <div class="container">

        <div class="section-heading">
            <span class="section-label">OUR FEATURES</span>
            <h2>Everything You Need to <span>Create.</span></h2>
            <p>Semua kebutuhan kreatif digital dalam satu ruang kerja.</p>
        </div>

        <div class="feature-grid">

            <div class="feature-card">
                <div class="feature-icon teal">
                    <i class="fa-solid fa-pen-ruler"></i>
                </div>
                <h3>Design Studio</h3>
                <p>Buat desain grafis melalui canvas interaktif dengan berbagai elemen kreatif.</p>
                <a href="auth/register.php">Explore Design <i class="fa-solid fa-arrow-right"></i></a>
            </div>

            <div class="feature-card">
                <div class="feature-icon blue-bg">
                    <i class="fa-solid fa-code"></i>
                </div>
                <h3>Code Studio</h3>
                <p>Tulis dan edit HTML, CSS, serta JavaScript dalam satu lingkungan coding.</p>
                <a href="auth/register.php">Explore Coding <i class="fa-solid fa-arrow-right"></i></a>
            </div>

            <div class="feature-card">
                <div class="feature-icon purple-bg">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                </div>
                <h3>AI Design Assistant</h3>
                <p>Bantu proses kreatif dengan teknologi AI untuk menganalisis ide dan desain.</p>
                <a href="auth/register.php">Explore AI <i class="fa-solid fa-arrow-right"></i></a>
            </div>

            <div class="feature-card">
                <div class="feature-icon orange-bg">
                    <i class="fa-solid fa-repeat"></i>
                </div>
                <h3>Design to Code</h3>
                <p>Ubah elemen desain menjadi struktur kode frontend yang dapat dikembangkan.</p>
                <a href="auth/register.php">Explore Converter <i class="fa-solid fa-arrow-right"></i></a>
            </div>

            <div class="feature-card">
                <div class="feature-icon pink-bg">
                    <i class="fa-solid fa-eye"></i>
                </div>
                <h3>Live Preview</h3>
                <p>Lihat hasil tampilan kode secara langsung melalui area preview.</p>
                <a href="auth/register.php">Explore Preview <i class="fa-solid fa-arrow-right"></i></a>
            </div>

            <div class="feature-card">
                <div class="feature-icon green-bg">
                    <i class="fa-solid fa-folder-open"></i>
                </div>
                <h3>Project Management</h3>
                <p>Simpan, kelola, dan lanjutkan semua proyek kreatif dalam satu tempat.</p>
                <a href="auth/register.php">Manage Projects <i class="fa-solid fa-arrow-right"></i></a>
            </div>

        </div>
    </div>
</section>


<!-- ABOUT -->
<section class="about section-padding" id="about">

    <div class="container about-grid">

        <div class="about-visual">
            <div class="about-main-card">
                <div class="about-symbol">
                    <i class="fa-solid fa-shapes"></i>
                </div>
                <h2>Ideas Become<br><span>Reality.</span></h2>
                <p>One workspace. Endless possibilities.</p>

                <div class="about-decoration">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>

        <div class="about-content">
            <span class="section-label">ABOUT VISUALOGIC</span>
            <h2>One Platform.<br><span>Infinite Creativity.</span></h2>

            <p>
                VISUALOGIC adalah platform kreatif digital yang menggabungkan
                desain visual, pengembangan frontend, dan kecerdasan buatan.
            </p>

            <p>
                Kami menghadirkan ruang kerja yang membantu pengguna
                mengembangkan ide, membuat desain, menulis kode, dan
                mengelola karya digital secara lebih terorganisir.
            </p>

            <a href="#workflow" class="btn-primary">
                Discover More <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

    </div>
</section>


<!-- WORKFLOW -->
<section class="workflow section-padding" id="workflow">

    <div class="container">

        <div class="section-heading">
            <span class="section-label">SIMPLE WORKFLOW</span>
            <h2>From Idea to <span>Creation.</span></h2>
            <p>Mulai dari ide sederhana hingga menjadi karya digital.</p>
        </div>

        <div class="workflow-grid">

            <div class="workflow-item">
                <div class="workflow-number">01</div>
                <i class="fa-solid fa-lightbulb"></i>
                <h3>Imagine</h3>
                <p>Mulai dengan ide dan konsep kreatif kamu.</p>
            </div>

            <div class="workflow-item">
                <div class="workflow-number">02</div>
                <i class="fa-solid fa-pen-nib"></i>
                <h3>Design</h3>
                <p>Bangun desain visual sesuai kreativitas.</p>
            </div>

            <div class="workflow-item">
                <div class="workflow-number">03</div>
                <i class="fa-solid fa-code"></i>
                <h3>Develop</h3>
                <p>Kembangkan ide menjadi karya berbasis kode.</p>
            </div>

            <div class="workflow-item">
                <div class="workflow-number">04</div>
                <i class="fa-solid fa-rocket"></i>
                <h3>Launch</h3>
                <p>Simpan dan kembangkan hasil proyek kamu.</p>
            </div>

        </div>

    </div>
</section>


<!-- CTA -->
<section class="cta-section">
    <div class="container cta-content">
        <span class="section-label">YOUR CREATIVE JOURNEY STARTS HERE</span>
        <h2>Ready to Create<br><span>Something Amazing?</span></h2>
        <p>Wujudkan ide kreatif kamu bersama VISUALOGIC.</p>
        <a href="auth/register.php" class="btn-primary">
            Get Started Now <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>
</section>


<!-- FOOTER -->
<footer class="footer">

    <div class="container footer-content">

        <div class="footer-brand">
            <a href="index.php" class="logo">
                <span class="logo-icon"><i class="fa-solid fa-layer-group"></i></span>
                VISUAL<span>OGIC</span>
            </a>

            <p>
                Design Beyond Pixels — Where Visual Creativity
                Meets Code Intelligence.
            </p>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?php echo date("Y"); ?> VISUALOGIC. All Rights Reserved.</p>
            <p>Designed for Creative Minds.</p>
        </div>

    </div>

</footer>

<script src="assets/js/script.js"></script>

</body>
</html>
