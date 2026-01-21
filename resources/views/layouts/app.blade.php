<!DOCTYPE html>
<html lang="fr">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#FF6A3A">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Gplanning">
    <!-- Android / Huawei PWA -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="application-name" content="Gplanning">
    <meta name="msapplication-TileColor" content="#FF6A3A">
    <meta name="msapplication-TileImage" content="{{ asset('icon-144x144.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icon-192x192.png') }}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('icon-512x512.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('icon-192x192.png') }}">
    <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('icon-152x152.png') }}">
    <link rel="apple-touch-icon" sizes="144x144" href="{{ asset('icon-144x144.png') }}">
    <link rel="apple-touch-icon" sizes="128x128" href="{{ asset('icon-128x128.png') }}">
    <link rel="apple-touch-icon" sizes="96x96" href="{{ asset('icon-96x96.png') }}">
    <link rel="apple-touch-icon" sizes="72x72" href="{{ asset('icon-72x72.png') }}">
    <link rel="manifest" href="{{ url('manifest.json') }}">
    <title>@yield('title', 'Gplanning') - Gda Com</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f5f7fa;
            background-image: url('{{ asset("Bgblanc.jpg") }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            color: #303030;
            line-height: 1.6;
        }
        
        /* Exclure la page de login du fond */
        body.login-page {
            background-image: none;
            background-color: #f5f7fa;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 10px;
            }
        }
        
        /* Header */
        header {
            background: linear-gradient(135deg, #FF6A3A 0%, #e55a2a 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        header .logo-container {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        header .logo-container img {
            height: 50px;
            width: auto;
        }
        
        header h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
        }
        
        header p {
            font-size: 0.9rem;
            opacity: 0.9;
            margin: 0;
        }
        
        header .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        header .user-menu span {
            font-weight: 500;
        }
        
        header .user-menu form {
            margin: 0;
        }
        
        header .user-menu .btn {
            background-color: rgba(255,255,255,0.2);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            padding: 0.5rem 1rem;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s;
        }
        
        header .user-menu .btn:hover {
            background-color: rgba(255,255,255,0.3);
        }
        
        /* Navigation */
        nav {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        nav ul {
            list-style: none;
            display: flex;
            gap: 0;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            min-width: max-content;
        }
        
        nav li a {
            display: block;
            padding: 1rem 1.5rem;
            color: #303030;
            text-decoration: none;
            transition: all 0.3s;
            border-bottom: 3px solid transparent;
            white-space: nowrap;
        }
        
        nav li a:hover,
        nav li a.active {
            color: #FF6A3A;
            border-bottom-color: #FF6A3A;
            background-color: #f8f9fa;
        }
        
        /* Responsive Header and Navigation */
        @media (max-width: 768px) {
            header .container {
                flex-direction: column;
                gap: 0.5rem;
                text-align: center;
            }
            
            header .logo-container {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            header h1 {
                font-size: 1.25rem;
            }
            
            header p {
                font-size: 0.8rem;
            }
            
            header .user-menu {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            nav ul {
                padding: 0 10px;
            }
            
            nav li a {
                padding: 0.75rem 1rem;
                font-size: 0.9rem;
            }
        }
        
        @media (max-width: 480px) {
            header {
                padding: 0.75rem 0;
            }
            
            header .logo-container img {
                height: 40px;
            }
            
            header h1 {
                font-size: 1.1rem;
            }
            
            nav li a {
                padding: 0.625rem 0.75rem;
                font-size: 0.85rem;
            }
        }
        
        /* Alerts Container - Fixed Position */
        .alerts-container {
            position: fixed;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100%;
            max-width: 1200px;
            z-index: 9999;
            padding: 1rem;
            pointer-events: none;
        }
        
        .alerts-container .alert {
            pointer-events: auto;
            margin-bottom: 0.75rem;
            width: 100%;
        }
        
        /* Ensure alerts don't overlap with header on scroll */
        @media (min-width: 769px) {
            .alerts-container {
                top: 80px; /* Below header */
            }
        }
        
        /* Alerts */
        .alert {
            position: relative;
            padding: 1rem 1.5rem;
            margin-bottom: 1rem;
            border-radius: 8px;
            border-left: 4px solid;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            animation: slideInDown 0.4s ease-out;
            max-width: 100%;
        }
        
        .alert-icon {
            font-size: 1.5rem;
            line-height: 1;
            flex-shrink: 0;
        }
        
        .alert-content {
            flex: 1;
            min-width: 0;
        }
        
        .alert strong {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
        }
        
        .alert ul {
            margin: 0.5rem 0 0 0;
            padding-left: 1.5rem;
        }
        
        .alert li {
            margin-bottom: 0.25rem;
        }
        
        .alert-close {
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
            background: none;
            border: none;
            font-size: 1.5rem;
            line-height: 1;
            cursor: pointer;
            color: inherit;
            opacity: 0.6;
            padding: 0;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            transition: all 0.2s;
        }
        
        .alert-close:hover {
            opacity: 1;
            background-color: rgba(0,0,0,0.1);
        }
        
        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border-color: #28a745;
            color: #155724;
        }
        
        .alert-warning {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            border-color: #ff6f00;
            color: #000000;
            font-weight: 600;
            box-shadow: 0 4px 16px rgba(255, 193, 7, 0.4);
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            border-color: #bd2130;
            color: #ffffff;
            font-weight: 600;
            box-shadow: 0 4px 16px rgba(220, 53, 69, 0.4);
        }
        
        .alert-info {
            background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
            border-color: #17a2b8;
            color: #0c5460;
        }
        
        @keyframes slideInDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        /* Responsive alerts */
        @media (max-width: 768px) {
            .alerts-container {
                padding: 0.75rem;
                top: 0;
                left: 0;
                transform: none;
                width: 100%;
            }
            
            .alert {
                padding: 0.875rem 1rem;
                font-size: 0.9rem;
                margin-bottom: 0.5rem;
            }
            
            .alert-icon {
                font-size: 1.25rem;
            }
            
            .alert-close {
                top: 0.5rem;
                right: 0.5rem;
                width: 20px;
                height: 20px;
                font-size: 1.25rem;
            }
            
            .alert strong {
                font-size: 0.95rem;
            }
            
            .alert ul {
                padding-left: 1.25rem;
                font-size: 0.85rem;
            }
        }
        
        @media (max-width: 480px) {
            .alerts-container {
                padding: 0.5rem;
            }
            
            .alert {
                padding: 0.75rem;
                font-size: 0.85rem;
            }
        }
        
        /* Buttons */
        .btn {
            display: inline-block;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background-color: #FF6A3A;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #e55a2a;
        }
        
        .btn-success {
            background-color: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background-color: #218838;
        }
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
        }
        
        .btn-secondary {
            background-color: #303030;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #1a1a1a;
        }
        
        /* Cards */
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #f0f0f0;
        }
        
        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        thead {
            background-color: #FF6A3A;
            color: white;
        }
        
        th, td {
            padding: 0.75rem;
            text-align: left;
        }
        
        tbody tr {
            border-bottom: 1px solid #f0f0f0;
        }
        
        tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        /* Forms */
        .form-group {
            margin-bottom: 1rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #303030;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="date"],
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        
        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #FF6A3A;
            box-shadow: 0 0 0 3px rgba(255, 106, 58, 0.1);
        }
        
        .form-actions {
            margin-top: 1.5rem;
            display: flex;
            gap: 1rem;
        }
        
        /* Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .stat-card h3 {
            font-size: 0.9rem;
            color: #303030;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-card .value {
            font-size: 2rem;
            font-weight: 700;
            color: #FF6A3A;
        }
        
        /* Badges */
        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .badge-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #303030;
        }
        
        .empty-state svg {
            width: 64px;
            height: 64px;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        /* Page Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        /* GSAP Animation Classes */
        [data-gsap] {
            opacity: 0;
        }
        
        [data-gsap="fadeIn"] {
            opacity: 0;
        }
        
        [data-gsap="fadeInUp"] {
            opacity: 0;
            transform: translateY(20px);
        }
        
        /* Smooth transitions */
        * {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* Enhanced button hover effects */
        .btn {
            position: relative;
            overflow: hidden;
            transform: translateY(0);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        /* Enhanced card hover */
        .card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }
        
        /* Loading state */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
    </style>
    <!-- GSAP Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <!-- Gplanning UX JavaScript -->
    <script src="{{ asset('js/gplanning-ux.js') }}"></script>
    </head>
<body>
    @php
        $isTeamReadOnly = auth()->check() && auth()->user()->isTeam();
    @endphp
    <header>
        <div class="container">
            <div class="logo-container">
                <img src="{{ asset('logo.png') }}" alt="Gplanning Logo">
                <div>
                    <h1>Gplanning</h1>
                    <p>Gestion des plannings - Gda Com</p>
                </div>
            </div>
            @auth
            <div class="user-menu">
                <span>{{ Auth::user()->username }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn">Déconnexion</button>
                </form>
            </div>
            @endauth
                    </div>
                </header>
    
    <nav>
        <ul>
            <li><a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Tableau de bord</a></li>
            <li><a href="{{ route('clients.index') }}" class="{{ request()->routeIs('clients.*') ? 'active' : '' }}">Clients</a></li>
            <li><a href="{{ route('content-ideas.index') }}" class="{{ request()->routeIs('content-ideas.*') ? 'active' : '' }}">Idées de contenu</a></li>
            <li><a href="{{ route('shootings.index') }}" class="{{ request()->routeIs('shootings.*') ? 'active' : '' }}">Tournages</a></li>
            <li><a href="{{ route('publications.index') }}" class="{{ request()->routeIs('publications.*') ? 'active' : '' }}">Publications</a></li>
        </ul>
    </nav>
    
    <!-- Alerts Container - Fixed Position -->
    <div class="alerts-container" id="alerts-container">
        @if(session('success'))
            <div class="alert alert-success" data-alert>
                <span class="alert-icon">✅</span>
                <div class="alert-content">
                    <strong>Succès</strong>
                    <div>{{ session('success') }}</div>
                </div>
                <button type="button" class="alert-close" onclick="this.parentElement.remove()" aria-label="Fermer">×</button>
            </div>
        @endif
        
        @if(session('warnings'))
            <div class="alert alert-warning" data-alert>
                <span class="alert-icon">⚠️</span>
                <div class="alert-content">
                    <strong>Avertissements</strong>
                    <ul>
                        @foreach(session('warnings') as $warning)
                            <li>{{ $warning }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="alert-close" onclick="this.parentElement.remove()" aria-label="Fermer">×</button>
            </div>
        @endif
        
        @if($errors->any())
            <div class="alert alert-danger" data-alert>
                <span class="alert-icon">❌</span>
                <div class="alert-content">
                    <strong>Erreurs</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="alert-close" onclick="this.parentElement.remove()" aria-label="Fermer">×</button>
            </div>
        @endif
    </div>
    
    <div class="container">
        @yield('content')
    </div>
    
    <script>
        // GSAP Animations
        document.addEventListener('DOMContentLoaded', function() {
            // Add class to body if alerts are present
            const alertsContainer = document.getElementById('alerts-container');
            if (alertsContainer && alertsContainer.children.length > 0) {
                document.body.classList.add('has-alerts');
            }
            
            // Animate elements with data-gsap attribute
            const fadeInElements = document.querySelectorAll('[data-gsap="fadeIn"]');
            fadeInElements.forEach((el, index) => {
                gsap.to(el, {
                    opacity: 1,
                    duration: 0.6,
                    delay: index * 0.1,
                    ease: 'power2.out'
                });
            });
            
            const fadeInUpElements = document.querySelectorAll('[data-gsap="fadeInUp"]');
            fadeInUpElements.forEach((el, index) => {
                gsap.to(el, {
                    opacity: 1,
                    y: 0,
                    duration: 0.6,
                    delay: index * 0.05,
                    ease: 'power2.out'
                });
            });
            
            // Animate alerts with GSAP
            const alerts = document.querySelectorAll('[data-alert]');
            alerts.forEach((alert, index) => {
                gsap.from(alert, {
                    y: -50,
                    opacity: 0,
                    scale: 0.9,
                    duration: 0.5,
                    delay: index * 0.15,
                    ease: 'back.out(1.7)'
                });
            });
            
            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        const targetPosition = target.offsetTop - 20;
                        window.scrollTo({
                            top: targetPosition,
                            behavior: 'smooth'
                        });
                    }
                });
            });
            
            // Form submission loading states
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function() {
                    const submitBtn = this.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        // Exclure les formulaires de rapport (téléchargement de fichiers)
                        if (this.classList.contains('report-form')) {
                            // Pour les rapports, on met en chargement mais on réinitialise après un délai
                            const originalHTML = submitBtn.innerHTML;
                            submitBtn.classList.add('loading');
                            submitBtn.disabled = true;
                            submitBtn.innerHTML = 'Chargement...';
                            
                            // Réinitialiser le bouton après 3 secondes (temps suffisant pour démarrer le téléchargement)
                            setTimeout(() => {
                                submitBtn.classList.remove('loading');
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = originalHTML;
                            }, 3000);
                        } else {
                            // Comportement normal pour les autres formulaires
                            submitBtn.classList.add('loading');
                            submitBtn.disabled = true;
                            submitBtn.innerHTML = 'Chargement...';
                        }
                    }
                });
            });
            
            // Auto-hide alerts after 30 seconds (only for success messages, warnings and errors stay)
            alerts.forEach(alert => {
                const alertsContainer = document.getElementById('alerts-container');
                // Only auto-hide success alerts, keep warnings and errors visible
                if (alert.classList.contains('alert-success')) {
                    let autoHideTimeout;
                    
                    const hideAlert = () => {
                        gsap.to(alert, {
                            opacity: 0,
                            y: -30,
                            scale: 0.9,
                            duration: 0.4,
                            ease: 'power2.in',
                            onComplete: () => {
                                alert.remove();
                                // Remove has-alerts class if no more alerts
                                if (alertsContainer && alertsContainer.children.length === 0) {
                                    document.body.classList.remove('has-alerts');
                                }
                            }
                        });
                    };
                    
                    autoHideTimeout = setTimeout(hideAlert, 30000); // 30 seconds for success
                    
                    // Cancel auto-hide if user hovers over alert
                    alert.addEventListener('mouseenter', () => {
                        clearTimeout(autoHideTimeout);
                    });
                    
                    // Resume auto-hide when user leaves
                    alert.addEventListener('mouseleave', () => {
                        autoHideTimeout = setTimeout(hideAlert, 10000); // 10 more seconds
                    });
                }
                // Warnings and errors stay visible until manually closed
            });
            
            // Enhanced close button functionality
            document.querySelectorAll('.alert-close').forEach(closeBtn => {
                closeBtn.addEventListener('click', function() {
                    const alert = this.closest('.alert');
                    const alertsContainer = document.getElementById('alerts-container');
                    
                    gsap.to(alert, {
                        opacity: 0,
                        y: -30,
                        scale: 0.9,
                        duration: 0.3,
                        ease: 'power2.in',
                        onComplete: () => {
                            alert.remove();
                            // Remove has-alerts class if no more alerts
                            if (alertsContainer && alertsContainer.children.length === 0) {
                                document.body.classList.remove('has-alerts');
                            }
                        }
                    });
                });
            });
            
            // Handle created content idea/client IDs from session
            @if(session('created_content_idea_id'))
                const createdId = {{ session('created_content_idea_id') }};
                // Auto-select the created content idea if we're on a form
                const contentIdeaSelect = document.querySelector('select[name="content_idea_id"]');
                if (contentIdeaSelect) {
                    contentIdeaSelect.value = createdId;
                    gsap.to(contentIdeaSelect, {
                        scale: 1.05,
                        duration: 0.2,
                        yoyo: true,
                        repeat: 1
                    });
                }
                const contentIdeaCheckboxes = document.querySelectorAll('input[name="content_idea_ids[]"]');
                contentIdeaCheckboxes.forEach(cb => {
                    if (cb.value == createdId) {
                        cb.checked = true;
                        gsap.to(cb.parentElement, {
                            backgroundColor: '#d4edda',
                            duration: 0.3,
                            yoyo: true,
                            repeat: 1
                        });
                    }
                });
            @endif

            @if(session('created_client_id'))
                const createdClientId = {{ session('created_client_id') }};
                const clientSelect = document.querySelector('select[name="client_id"]');
                if (clientSelect) {
                    clientSelect.value = createdClientId;
                    gsap.to(clientSelect, {
                        scale: 1.05,
                        duration: 0.2,
                        yoyo: true,
                        repeat: 1
                    });
                    // Trigger change event if there's a form that needs to reload
                    if (clientSelect.onchange) {
                        clientSelect.onchange();
                    }
                }
            @endif
        });
        
    </script>
    <script src="{{ asset('js/pwa.js') }}"></script>
    </body>
</html>
