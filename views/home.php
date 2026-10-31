<?php

$title = 'Selamat Datang di Qubu - Info Lomba & Kolaborasi Mahasiswa';
$extraCss = 'public/css/home.css';
$extraScript = 'public/js/carousel.js';

require_once __DIR__ . '/layouts/headers.php';
?>

<div class="home-page">

    <header class="home-nav">

        <a href="index.php" class="home-logo-link" aria-label="Qubu Homepage">
            <svg class="home-logo-svg" viewBox="0 0 160 55" fill="none" xmlns="http://www.w3.org/2000/svg">

                <defs>
                    <linearGradient id="logoGradBlue" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#06b6d4" />
                        <stop offset="100%" stop-color="#2563eb" />
                    </linearGradient>
                    <linearGradient id="logoGradPurple" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#a855f7" />
                        <stop offset="100%" stop-color="#6366f1" />
                    </linearGradient>
                </defs>

                <circle cx="16" cy="11" r="5.5" fill="url(#logoGradBlue)" />

                <circle cx="28" cy="11" r="5.5" fill="url(#logoGradPurple)" />

                <path d="M 10 24 C 7 32, 14 42, 22 42 C 30 42, 36 34, 34 25 C 32 17, 23 17, 18 20 C 13 23, 11 31, 14 36" 
                      stroke="url(#logoGradBlue)" stroke-width="4.5" stroke-linecap="round" fill="none" />
                <path d="M 22 42 C 26 42, 33 40, 36 34 C 38 29, 36 22, 30 20" 
                      stroke="url(#logoGradPurple)" stroke-width="4.5" stroke-linecap="round" fill="none" />
                <path d="M 28 35 L 36 43" 
                      stroke="url(#logoGradPurple)" stroke-width="4.5" stroke-linecap="round" />

                <text x="48" y="36" font-family="'Plus Jakarta Sans', system-ui, sans-serif" font-size="28" font-weight="900" fill="#2563eb" letter-spacing="-0.03em">QUBU</text>
            </svg>
        </a>

        <a href="<?= url('?page=register') ?>" class="nav-daftar-link" id="nav-daftar">
            Daftar
        </a>
    </header>

    <main class="hero-header">
        <h1 class="hero-title">
            <span class="hero-line">SELAMAT</span>
            <span class="hero-line">DATANG DI QUBU</span>
        </h1>
    </main>

    <section class="carousel-section" aria-label="Kategori Lomba Qubu">
        <div class="carousel-viewport" id="carousel-viewport" title="Geser (drag) atau scroll untuk berpindah kategori">

            <button class="carousel-arrow carousel-arrow-prev" id="btn-prev" aria-label="Geser ke Kiri (Sebelumnya)">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </button>

            <div class="carousel-track" id="carousel-track">

                <article class="carousel-card card-frontend" data-index="0" data-title="Frontend">
                    <div class="card-sparkle card-sparkle-1">✦</div>
                    <div class="card-top">
                        <span class="card-badge">AYO BERLOMBA</span>
                        <h2 class="card-title">Frontend</h2>
                        <div class="card-tag">&lt;&gt;</div>
                    </div>
                    <div class="card-art">
                        <div class="art-laptop">
                            <div class="laptop-screen">
                                <span class="screen-code">&lt; / &gt;</span>
                            </div>
                            <div class="laptop-base">
                                <div class="laptop-notch"></div>
                            </div>
                        </div>
                        <div class="art-sphere-blue"></div>
                    </div>
                </article>

                <article class="carousel-card card-robotics" data-index="1" data-title="Robotic">
                    <div class="card-sparkle card-sparkle-1">✦</div>
                    <div class="card-top">
                        <span class="card-badge">AYO BERLOMBA</span>
                        <h2 class="card-title">Robotic</h2>
                        <div class="card-tag">&lt;&gt;</div>
                    </div>
                    <div class="card-art">
                        <div class="art-robot-box">
                            <div class="robot-card">
                                <div class="robot-header">
                                    <div class="robot-dots">
                                        <span class="dot-pink"></span>
                                        <span class="dot-pink"></span>
                                        <span class="dot-pink"></span>
                                    </div>
                                    <span class="robot-label">ROBOT-V3</span>
                                </div>
                                <div class="robot-avatar">
                                    <div class="robot-antennas">
                                        <div class="antenna-ball"></div>
                                        <div class="antenna-ball"></div>
                                    </div>
                                    <div class="robot-head">
                                        <div class="robot-visor">
                                            <div class="robot-eye"></div>
                                            <div class="robot-eye"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="robot-footer">
                                    <span class="badge-aiservo">AI/SERVO</span>
                                    <span class="robot-status-pill">ONLINE</span>
                                </div>
                            </div>

                            <div class="art-chip" title="AI Microchip">
                                <div class="chip-pins chip-pins-t"></div>
                                <div class="chip-pins chip-pins-b"></div>
                                <div class="chip-core">
                                    <span class="chip-icon">🤖</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>

                <article class="carousel-card card-uiux" data-index="2" data-title="UI/UX Design">
                    <div class="card-top">
                        <span class="card-badge">AYO BERLOMBA</span>
                        <h2 class="card-title">UI/UX Design</h2>
                        <div class="card-sub-row">
                            <span class="card-tag">&lt;&gt;</span>
                            <img src="<?= url('public/images/uiux-sparkle-star.png') ?>" alt="Sparkle" class="uiux-star-accent" />
                        </div>
                    </div>
                    <div class="card-art">
                        <div class="art-uiux-composition">

                            <img src="<?= url('public/images/uiux-window-assembly.png') ?>" 
                                 alt="UI/UX Wireframe Window & Stylus" 
                                 class="uiux-window-assembly-img" />

                            <img src="<?= url('public/images/uiux-squircle-dot.png') ?>" 
                                 alt="Control Button" 
                                 class="uiux-squircle-btn" />
                        </div>
                    </div>
                </article>

                <article class="carousel-card card-backend" data-index="3" data-title="Backend">
                    <div class="card-sparkle card-sparkle-1">✦</div>
                    <div class="card-top">
                        <span class="card-badge">AYO BERLOMBA</span>
                        <h2 class="card-title">Backend</h2>
                        <div class="card-tag">&lt;&gt;</div>
                    </div>
                    <div class="card-art">
                        <div class="art-server-box">
                            <div class="server-card">
                                <div class="server-header">
                                    <span><span class="server-status-dot"></span>SERVER CLUSTER</span>
                                    <span class="server-tag">REST/SQL</span>
                                </div>
                                <div class="server-row">
                                    <span>api-node-01</span>
                                    <span class="server-badge badge-green">200 OK</span>
                                </div>
                                <div class="server-row">
                                    <span>pg-primary</span>
                                    <span class="server-badge badge-sync">SYNC</span>
                                </div>
                                <div class="server-row">
                                    <span>redis-cache</span>
                                    <span class="server-badge badge-fast">0.2ms</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>

                <article class="carousel-card card-datascience" data-index="4" data-title="Data Science">
                    <div class="card-sparkle card-sparkle-1">✦</div>
                    <div class="card-top">
                        <span class="card-badge">AYO BERLOMBA</span>
                        <h2 class="card-title">Data Science</h2>
                        <div class="card-tag">&lt;&gt;</div>
                    </div>
                    <div class="card-art">
                        <div class="art-data-box">
                            <div class="analytics-card">
                                <div class="analytics-header">
                                    <span class="analytics-dot"></span>
                                    <span>ANALYTICS</span>
                                </div>
                                <div class="analytics-chart">
                                    <div class="chart-bar"></div>
                                    <div class="chart-bar"></div>
                                    <div class="chart-bar"></div>
                                    <div class="chart-bar"></div>
                                </div>
                                <div class="chart-labels">
                                    <span>Q1</span>
                                    <span>Q2</span>
                                    <span>Q3</span>
                                    <span>Q4</span>
                                </div>
                            </div>
                            <div class="art-pill-trend">↗</div>
                            <div class="art-database-coin">🗄️</div>
                        </div>
                    </div>
                </article>

            </div>

            <button class="carousel-arrow carousel-arrow-next" id="btn-next" aria-label="Geser ke Kanan (Selanjutnya)">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </button>
        </div>

        <div class="carousel-dots" id="carousel-dots" role="tablist" aria-label="Indikator Kategori Lomba"></div>
    </section>

    <div class="hero-cta-container">
        <a href="<?= url('?page=login') ?>" class="btn-hero-login" id="btn-hero-login">
            Login
        </a>
    </div>
</div>

<?php
require_once __DIR__ . '/layouts/footer.php';
?>
