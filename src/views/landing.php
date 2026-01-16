<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Kindergarten Spiele Organizer - Die moderne Verwaltungslösung für Kindergartenspiele und Materialien">
    <title>Kindergarten Spiele Organizer</title>
    <style>
        :root {
            --color-primary: #4F46E5;
            --color-primary-dark: #4338CA;
            --color-primary-light: #6366F1;
            --color-primary-bg: #EEF2FF;
            --color-success: #22C55E;
            --color-warning: #F59E0B;
            --color-danger: #EF4444;
            --color-gray-50: #F9FAFB;
            --color-gray-100: #F3F4F6;
            --color-gray-200: #E5E7EB;
            --color-gray-400: #9CA3AF;
            --color-gray-500: #6B7280;
            --color-gray-600: #4B5563;
            --color-gray-700: #374151;
            --color-gray-800: #1F2937;
            --color-gray-900: #111827;
            --font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: var(--font-family);
            line-height: 1.6;
            color: var(--color-gray-900);
            background: var(--color-gray-50);
        }

        /* Header */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--color-gray-200);
            z-index: 1000;
        }

        .header-inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--color-primary);
            text-decoration: none;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: var(--color-primary);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .nav {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .nav-link {
            color: var(--color-gray-600);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .nav-link:hover {
            color: var(--color-primary);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            font-size: 0.9375rem;
            font-weight: 600;
            border-radius: 0.5rem;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-primary {
            background: var(--color-primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--color-primary-dark);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--color-gray-200);
            color: var(--color-gray-700);
        }

        .btn-outline:hover {
            border-color: var(--color-primary);
            color: var(--color-primary);
        }

        .btn-lg {
            padding: 0.875rem 2rem;
            font-size: 1.0625rem;
        }

        /* Hero Section */
        .hero {
            padding: 8rem 2rem 4rem;
            background: linear-gradient(135deg, var(--color-primary-bg) 0%, white 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .hero-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }

        .hero-content h1 {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            color: var(--color-gray-900);
        }

        .hero-content h1 span {
            color: var(--color-primary);
        }

        .hero-content p {
            font-size: 1.25rem;
            color: var(--color-gray-600);
            margin-bottom: 2rem;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .hero-image {
            position: relative;
        }

        .hero-screenshot {
            width: 100%;
            border-radius: 1rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            border: 1px solid var(--color-gray-200);
        }

        .floating-card {
            position: absolute;
            background: white;
            border-radius: 0.75rem;
            padding: 1rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        }

        .floating-card-1 {
            top: 10%;
            right: -40px;
            animation: float 6s ease-in-out infinite;
        }

        .floating-card-2 {
            bottom: 15%;
            left: -40px;
            animation: float 6s ease-in-out infinite 0.5s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        /* Features Section */
        .features {
            padding: 6rem 2rem;
            background: white;
        }

        .section-inner {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-header {
            text-align: center;
            max-width: 600px;
            margin: 0 auto 4rem;
        }

        .section-header h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .section-header p {
            color: var(--color-gray-600);
            font-size: 1.125rem;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
        }

        .feature-card {
            padding: 2rem;
            background: var(--color-gray-50);
            border-radius: 1rem;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .feature-icon {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.25rem;
        }

        .feature-card h3 {
            font-size: 1.25rem;
            margin-bottom: 0.75rem;
        }

        .feature-card p {
            color: var(--color-gray-600);
            font-size: 0.9375rem;
        }

        /* Stats Section */
        .stats {
            padding: 4rem 2rem;
            background: var(--color-primary);
            color: white;
        }

        .stats-grid {
            max-width: 1000px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
            text-align: center;
        }

        .stat-item h3 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-item p {
            opacity: 0.9;
            font-size: 1rem;
        }

        /* Screenshots Section */
        .screenshots {
            padding: 6rem 2rem;
            background: var(--color-gray-50);
        }

        .screenshots-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
        }

        .screenshot-card {
            background: white;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s;
        }

        .screenshot-card:hover {
            transform: scale(1.02);
        }

        .screenshot-card img {
            width: 100%;
            aspect-ratio: 16/10;
            object-fit: cover;
            background: var(--color-gray-100);
        }

        .screenshot-card-body {
            padding: 1.25rem;
        }

        .screenshot-card h4 {
            font-size: 1rem;
            margin-bottom: 0.375rem;
        }

        .screenshot-card p {
            font-size: 0.875rem;
            color: var(--color-gray-500);
        }

        /* Security Section */
        .security {
            padding: 6rem 2rem;
            background: white;
        }

        .security-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }

        .security-list {
            list-style: none;
        }

        .security-list li {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .security-check {
            flex-shrink: 0;
            width: 28px;
            height: 28px;
            background: var(--color-success);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        /* CTA Section */
        .cta {
            padding: 6rem 2rem;
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);
            text-align: center;
            color: white;
        }

        .cta h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .cta p {
            font-size: 1.25rem;
            opacity: 0.9;
            margin-bottom: 2rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .cta .btn-white {
            background: white;
            color: var(--color-primary);
        }

        .cta .btn-white:hover {
            background: var(--color-gray-100);
        }

        /* Footer */
        .footer {
            padding: 3rem 2rem;
            background: var(--color-gray-900);
            color: var(--color-gray-400);
            text-align: center;
        }

        .footer p {
            margin-bottom: 1rem;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 2rem;
        }

        .footer-links a {
            color: var(--color-gray-400);
            text-decoration: none;
        }

        .footer-links a:hover {
            color: white;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .hero-inner,
            .security-inner {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .hero-image {
                order: -1;
            }

            .floating-card {
                display: none;
            }

            .hero-content h1 {
                font-size: 2.5rem;
            }

            .features-grid,
            .screenshots-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 640px) {
            .header-inner {
                padding: 0.75rem 1rem;
            }

            .nav {
                display: none;
            }

            .hero {
                padding: 6rem 1rem 3rem;
            }

            .hero-content h1 {
                font-size: 2rem;
            }

            .features-grid,
            .screenshots-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .stat-item h3 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-inner">
            <a href="/" class="logo">
                <span class="logo-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polygon points="10 8 16 12 10 16 10 8" fill="currentColor"></polygon>
                    </svg>
                </span>
                Kindergarten Spiele
            </a>
            <nav class="nav">
                <a href="#features" class="nav-link">Funktionen</a>
                <a href="#security" class="nav-link">Sicherheit</a>
                <a href="/login" class="btn btn-primary">Anmelden</a>
            </nav>
        </div>
    </header>

    <!-- Hero -->
    <section class="hero">
        <div class="hero-inner">
            <div class="hero-content">
                <h1>Organisieren Sie Ihre <span>Kindergartenspiele</span> effizient</h1>
                <p>Die moderne Verwaltungslösung für Kindergartenpädagogen. Verwalten Sie Spiele, Materialien und Boxen an einem zentralen Ort.</p>
                <div class="hero-buttons">
                    <a href="/login" class="btn btn-primary btn-lg">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                            <polyline points="10 17 15 12 10 7"></polyline>
                            <line x1="15" y1="12" x2="3" y2="12"></line>
                        </svg>
                        Jetzt anmelden
                    </a>
                    <a href="#features" class="btn btn-outline btn-lg">Mehr erfahren</a>
                </div>
            </div>
            <div class="hero-image">
                <div style="width: 100%; aspect-ratio: 16/10; background: linear-gradient(135deg, var(--color-gray-100) 0%, var(--color-gray-200) 100%); border-radius: 1rem; display: flex; align-items: center; justify-content: center; color: var(--color-gray-400);">
                    <svg width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="3" y1="9" x2="21" y2="9"></line>
                        <line x1="9" y1="21" x2="9" y2="9"></line>
                    </svg>
                </div>
                <div class="floating-card floating-card-1">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <div style="width: 40px; height: 40px; background: var(--color-success); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </div>
                        <div>
                            <div style="font-weight: 600; font-size: 0.875rem;">Neues Spiel</div>
                            <div style="color: var(--color-gray-500); font-size: 0.75rem;">hinzugefügt</div>
                        </div>
                    </div>
                </div>
                <div class="floating-card floating-card-2">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <div style="width: 40px; height: 40px; background: var(--color-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" fill="currentColor"></polygon>
                            </svg>
                        </div>
                        <div>
                            <div style="font-weight: 600; font-size: 0.875rem;">24 Favoriten</div>
                            <div style="color: var(--color-gray-500); font-size: 0.75rem;">gespeichert</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="features" id="features">
        <div class="section-inner">
            <div class="section-header">
                <h2>Alles was Sie brauchen</h2>
                <p>Eine vollständige Lösung zur Verwaltung Ihrer Kindergartenspiele und Materialien</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon" style="background: var(--color-primary-bg); color: var(--color-primary);">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polygon points="10 8 16 12 10 16 10 8"></polygon>
                        </svg>
                    </div>
                    <h3>Spieleverwaltung</h3>
                    <p>Verwalten Sie alle Ihre Kindergartenspiele mit Beschreibungen, Bildern und Schwierigkeitsgraden.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon" style="background: #dcfce7; color: var(--color-success);">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        </svg>
                    </div>
                    <h3>Materialverfolgung</h3>
                    <p>Behalten Sie den Überblick über alle Spielmaterialien und deren Zustand.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon" style="background: #fef3c7; color: var(--color-warning);">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            <path d="M3.3 7l8.7 5 8.7-5"></path>
                            <path d="M12 22V12"></path>
                        </svg>
                    </div>
                    <h3>Box-Organisation</h3>
                    <p>Organisieren Sie Materialien in Boxen und wissen Sie immer, wo alles zu finden ist.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon" style="background: #fee2e2; color: var(--color-danger);">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                    </div>
                    <h3>Kalender</h3>
                    <p>Planen Sie Spielaktivitäten und verfolgen Sie, welche Spiele wann gespielt wurden.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon" style="background: #e0e7ff; color: #4f46e5;">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </div>
                    <h3>Schnelle Suche</h3>
                    <p>Finden Sie Spiele und Materialien blitzschnell mit der integrierten Volltextsuche.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon" style="background: #fae8ff; color: #a855f7;">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                            <line x1="7" y1="7" x2="7.01" y2="7"></line>
                        </svg>
                    </div>
                    <h3>Tags & Kategorien</h3>
                    <p>Kategorisieren Sie Spiele nach Altersgruppen und Themen für eine bessere Organisation.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats -->
    <section class="stats">
        <div class="stats-grid">
            <div class="stat-item">
                <h3>100+</h3>
                <p>Spiele verwalten</p>
            </div>
            <div class="stat-item">
                <h3>500+</h3>
                <p>Materialien tracken</p>
            </div>
            <div class="stat-item">
                <h3>24/7</h3>
                <p>Verfügbarkeit</p>
            </div>
            <div class="stat-item">
                <h3>100%</h3>
                <p>Sicher & Privat</p>
            </div>
        </div>
    </section>

    <!-- Screenshots -->
    <section class="screenshots">
        <div class="section-inner">
            <div class="section-header">
                <h2>Einblicke in die Anwendung</h2>
                <p>Entdecken Sie die intuitive Benutzeroberfläche</p>
            </div>
            <div class="screenshots-grid">
                <div class="screenshot-card">
                    <div style="width: 100%; aspect-ratio: 16/10; background: linear-gradient(135deg, var(--color-primary-bg) 0%, var(--color-gray-100) 100%); display: flex; align-items: center; justify-content: center; color: var(--color-primary);">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                            <path d="M3 9h18"></path>
                            <path d="M9 21V9"></path>
                        </svg>
                    </div>
                    <div class="screenshot-card-body">
                        <h4>Dashboard</h4>
                        <p>Übersicht aller Statistiken und schneller Zugriff</p>
                    </div>
                </div>
                <div class="screenshot-card">
                    <div style="width: 100%; aspect-ratio: 16/10; background: linear-gradient(135deg, #dcfce7 0%, var(--color-gray-100) 100%); display: flex; align-items: center; justify-content: center; color: var(--color-success);">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polygon points="10 8 16 12 10 16 10 8"></polygon>
                        </svg>
                    </div>
                    <div class="screenshot-card-body">
                        <h4>Spieleübersicht</h4>
                        <p>Alle Spiele auf einen Blick mit Filtern</p>
                    </div>
                </div>
                <div class="screenshot-card">
                    <div style="width: 100%; aspect-ratio: 16/10; background: linear-gradient(135deg, #fee2e2 0%, var(--color-gray-100) 100%); display: flex; align-items: center; justify-content: center; color: var(--color-danger);">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                    </div>
                    <div class="screenshot-card-body">
                        <h4>Kalender</h4>
                        <p>Planen und verfolgen Sie Spielaktivitäten</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Security -->
    <section class="security" id="security">
        <div class="security-inner">
            <div>
                <h2>Sicherheit hat Priorität</h2>
                <p style="color: var(--color-gray-600); margin-bottom: 2rem;">Ihre Daten sind bei uns sicher. Wir verwenden modernste Sicherheitstechnologien.</p>
                <ul class="security-list">
                    <li>
                        <span class="security-check">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </span>
                        <div>
                            <strong>CSRF-Schutz</strong>
                            <p style="color: var(--color-gray-500); font-size: 0.875rem;">Alle Formulare sind gegen Cross-Site Request Forgery geschützt</p>
                        </div>
                    </li>
                    <li>
                        <span class="security-check">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </span>
                        <div>
                            <strong>SQL-Injection-Schutz</strong>
                            <p style="color: var(--color-gray-500); font-size: 0.875rem;">Alle Datenbankabfragen verwenden sichere Prepared Statements</p>
                        </div>
                    </li>
                    <li>
                        <span class="security-check">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </span>
                        <div>
                            <strong>Sichere Passwörter</strong>
                            <p style="color: var(--color-gray-500); font-size: 0.875rem;">Bcrypt-Hashing mit Komplexitätsanforderungen</p>
                        </div>
                    </li>
                    <li>
                        <span class="security-check">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </span>
                        <div>
                            <strong>Rate Limiting & IP-Schutz</strong>
                            <p style="color: var(--color-gray-500); font-size: 0.875rem;">Automatischer Schutz gegen Brute-Force-Angriffe</p>
                        </div>
                    </li>
                </ul>
            </div>
            <div style="text-align: center;">
                <div style="width: 300px; height: 300px; margin: 0 auto; background: linear-gradient(135deg, var(--color-success) 0%, #16a34a 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                    <svg width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        <path d="M9 12l2 2 4-4"></path>
                    </svg>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta">
        <h2>Bereit loszulegen?</h2>
        <p>Melden Sie sich jetzt an und beginnen Sie mit der Organisation Ihrer Kindergartenspiele.</p>
        <a href="/login" class="btn btn-white btn-lg">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                <polyline points="10 17 15 12 10 7"></polyline>
                <line x1="15" y1="12" x2="3" y2="12"></line>
            </svg>
            Jetzt anmelden
        </a>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; <?= date('Y') ?> Kindergarten Spiele Organizer. Alle Rechte vorbehalten.</p>
        <div class="footer-links">
            <a href="/login">Anmelden</a>
            <a href="#features">Funktionen</a>
            <a href="#security">Sicherheit</a>
        </div>
    </footer>
</body>
</html>
