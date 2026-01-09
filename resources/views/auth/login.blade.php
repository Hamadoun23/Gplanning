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
    <link rel="icon" type="image/jpeg" href="{{ asset('Icones.jpg') }}">
    <link rel="apple-touch-icon" href="{{ asset('Icones.jpg') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <title>Gplanning | Gda Com</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow-x: hidden;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-image: url('{{ asset('bg.jpg') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
            height: 100%;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-y: auto;
            box-sizing: border-box;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            min-height: 100%;
            background: rgba(0, 0, 0, 0.4);
            z-index: 0;
            pointer-events: none;
        }
        
        /* Animated background shapes */
        .bg-shapes {
            position: fixed;
            width: 100vw;
            height: 100vh;
            min-height: 100%;
            top: 0;
            left: 0;
            overflow: hidden;
            z-index: 1;
            pointer-events: none;
        }
        
        .shape {
            position: absolute;
            border-radius: 50%;
            opacity: 0.1;
            animation: float 20s infinite ease-in-out;
        }
        
        .shape-1 {
            width: 300px;
            height: 300px;
            background: #FF6A3A;
            top: -100px;
            left: -100px;
            animation-delay: 0s;
        }
        
        .shape-2 {
            width: 200px;
            height: 200px;
            background: #ffffff;
            bottom: -50px;
            right: -50px;
            animation-delay: 5s;
        }
        
        .shape-3 {
            width: 150px;
            height: 150px;
            background: #FF6A3A;
            top: 50%;
            right: 10%;
            animation-delay: 10s;
        }
        
        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            33% { transform: translate(30px, -30px) rotate(120deg); }
            66% { transform: translate(-20px, 20px) rotate(240deg); }
        }
        
        .login-container {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 420px;
            animation: slideUp 0.8s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 2rem 2.5rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
            width: 100%;
        }
        
        @media (max-width: 480px) {
            .login-card {
                padding: 2rem 1.5rem;
                border-radius: 20px;
                max-height: 95vh;
            }
        }
        
        .logo-section {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        .logo-section img {
            height: 60px;
            width: auto;
            margin-bottom: 0.5rem;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));
            animation: logoFloat 3s ease-in-out infinite;
        }
        
        @keyframes logoFloat {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .logo-section h1 {
            font-size: 1.6rem;
            font-weight: 700;
            background: linear-gradient(135deg, #FF6A3A 0%, #e55a2a 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.3rem;
        }
        
        .logo-section p {
            color: #666;
            font-size: 0.85rem;
        }
        
        .form-group {
            margin-bottom: 1.25rem;
            position: relative;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #303030;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .form-input-wrapper {
            position: relative;
        }
        
        .form-input {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
            color: #303030;
        }
        
        .form-input-wrapper:has(.password-toggle-btn) .form-input {
            padding-left: 1rem;
            padding-right: 3.5rem;
        }
        
        /* Masquer les icônes natives du navigateur pour les champs password */
        #password::-webkit-credentials-auto-fill-button,
        #password::-webkit-strong-password-auto-fill-button,
        #password::-webkit-inner-spin-button,
        #password::-webkit-outer-spin-button {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
            pointer-events: none !important;
            position: absolute !important;
            right: -9999px !important;
        }
        
        #password::-ms-reveal,
        #password::-ms-clear {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }
        
        /* Masquer les suggestions de mot de passe du navigateur */
        #password::-webkit-autofill {
            -webkit-box-shadow: 0 0 0 1000px white inset !important;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #FF6A3A;
            box-shadow: 0 0 0 4px rgba(255, 106, 58, 0.1);
            transform: translateY(-2px);
        }
        
        .form-input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            width: 20px;
            height: 20px;
            transition: color 0.3s ease;
            pointer-events: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .form-input-icon svg {
            width: 100%;
            height: 100%;
            stroke: currentColor;
            fill: none;
        }
        
        .form-input-wrapper:focus-within .form-input-icon {
            color: #FF6A3A;
        }
        
        .password-toggle-btn {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            transition: color 0.3s ease;
            z-index: 10;
        }
        
        .password-toggle-btn:hover {
            color: #FF6A3A;
        }
        
        .password-toggle-btn:focus {
            outline: none;
            color: #FF6A3A;
        }
        
        .password-toggle-icon {
            width: 20px;
            height: 20px;
            stroke: currentColor;
            fill: none;
        }
        
        
        .error-message {
            color: #dc3545;
            font-size: 0.85rem;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            animation: shake 0.5s ease;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.25rem;
        }
        
        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #FF6A3A;
        }
        
        .remember-me label {
            font-size: 0.9rem;
            color: #666;
            cursor: pointer;
            user-select: none;
        }
        
        .btn-login {
            width: 100%;
            padding: 1rem;
            background: #FF6A3A !important;
            background-color: #FF6A3A !important;
            color: #FFFFFF !important;
            border: 2px solid #FF6A3A !important;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 106, 58, 0.6) !important;
            position: relative;
            overflow: visible;
            z-index: 10;
            display: block;
            margin-top: 0.5rem;
            opacity: 1 !important;
            visibility: visible !important;
        }
        
        .btn-login:hover {
            background: #FF8533 !important;
            background-color: #FF8533 !important;
            border-color: #FF8533 !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 106, 58, 0.7) !important;
        }
        
        .btn-login:active {
            transform: translateY(0);
            background: #e55a2a !important;
            background-color: #e55a2a !important;
            border-color: #e55a2a !important;
        }
        
        .btn-login:focus {
            outline: 3px solid rgba(255, 106, 58, 0.5);
            outline-offset: 2px;
            background: #FF6A3A !important;
            background-color: #FF6A3A !important;
        }
        
        .btn-login span {
            position: relative;
            z-index: 10;
            color: #FFFFFF !important;
            display: block;
            font-weight: 700;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }
        
        .session-status {
            padding: 0.85rem;
            border-radius: 12px;
            margin-bottom: 1.25rem;
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border-left: 4px solid #28a745;
            color: #155724;
            font-size: 0.85rem;
        }
        
        .loading {
            opacity: 0.7;
            pointer-events: none;
        }
        
        .loading .btn-login span::after {
            content: '...';
            animation: dots 1.5s steps(4, end) infinite;
        }
        
        @keyframes dots {
            0%, 20% { content: '.'; }
            40% { content: '..'; }
            60%, 100% { content: '...'; }
        }
        
        /* Responsive - Tablette */
        @media (max-width: 768px) {
            body {
                padding: 15px;
                background-attachment: scroll;
            }
            
            .login-container {
                max-width: 100%;
            }
            
            .login-card {
                padding: 2rem 1.75rem;
                border-radius: 20px;
            }
            
            .logo-section {
                margin-bottom: 1.5rem;
            }
            
            .logo-section h1 {
                font-size: 1.5rem;
            }
            
            .logo-section img {
                height: 60px;
            }
            
            .form-input {
                padding: 0.9rem 0.9rem 0.9rem 2.8rem;
                font-size: 0.95rem;
            }
            
            .form-input-icon {
                left: 0.9rem;
                width: 18px;
                height: 18px;
            }
            
            .form-group {
                margin-bottom: 1.25rem;
            }
            
            .remember-me {
                margin-bottom: 1.25rem;
            }
            
            .btn-login {
                background: #FF6A3A !important;
                color: white !important;
            }
            
            .shape-1 {
                width: 200px;
                height: 200px;
            }
            
            .shape-2 {
                width: 150px;
                height: 150px;
            }
            
            .shape-3 {
                width: 100px;
                height: 100px;
            }
        }
        
        /* Responsive - Mobile */
        @media (max-width: 480px) {
            body {
                padding: 10px;
                align-items: center;
                justify-content: center;
                padding-top: 1rem;
                padding-bottom: 1rem;
                min-height: 100vh;
                min-height: -webkit-fill-available; /* Pour iOS */
            }
            
            body::before {
                background: rgba(0, 0, 0, 0.5);
            }
            
            .login-container {
                max-width: 100%;
                width: 100%;
            }
            
            .login-card {
                padding: 1.5rem 1.25rem;
                border-radius: 16px;
                box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
                max-height: calc(100vh - 3rem);
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            .logo-section {
                margin-bottom: 1.25rem;
            }
            
            .logo-section h1 {
                font-size: 1.35rem;
            }
            
            .logo-section p {
                font-size: 0.8rem;
            }
            
            .logo-section img {
                height: 50px;
                margin-bottom: 0.4rem;
            }
            
            .form-group {
                margin-bottom: 1rem;
            }
            
            .form-label {
                font-size: 0.85rem;
                margin-bottom: 0.4rem;
            }
            
            .form-input {
                padding: 0.9rem 0.9rem 0.9rem 2.6rem;
                font-size: 16px; /* Évite le zoom sur iOS */
                border-radius: 10px;
                -webkit-appearance: none;
            }
            
            .form-input-wrapper:has(.password-toggle-btn) .form-input {
                padding-left: 0.9rem;
                padding-right: 3rem;
            }
            
            .form-input-icon {
                left: 0.9rem;
                width: 18px;
                height: 18px;
            }
            
            .form-input:focus {
                box-shadow: 0 0 0 3px rgba(255, 106, 58, 0.15);
                transform: translateY(0); /* Pas de transformation sur mobile */
            }
            
            .error-message {
                font-size: 0.8rem;
                margin-top: 0.4rem;
            }
            
            .remember-me {
                margin-bottom: 1rem;
            }
            
            .btn-login {
                padding: 0.95rem;
                background: #FF6A3A !important;
                color: white !important;
                font-size: 1rem;
                border-radius: 10px;
                min-height: 48px; /* Taille minimale pour le touch */
            }
            
            .remember-me label {
                font-size: 0.85rem;
            }
            
            .remember-me input[type="checkbox"] {
                width: 20px;
                height: 20px;
            }
            
            .session-status {
                padding: 0.85rem;
                font-size: 0.85rem;
                margin-bottom: 1.25rem;
            }
            
            .bg-shapes {
                display: none; /* Masquer les formes sur mobile pour de meilleures performances */
            }
        }
        
        /* Responsive - Très petits écrans */
        @media (max-width: 360px) {
            body {
                padding: 8px;
                align-items: center;
                justify-content: center;
                padding-top: 1rem;
                padding-bottom: 1rem;
            }
            
            .login-card {
                padding: 1.25rem 1rem;
                border-radius: 14px;
            }
            
            .logo-section {
                margin-bottom: 1rem;
            }
            
            .logo-section h1 {
                font-size: 1.2rem;
            }
            
            .logo-section p {
                font-size: 0.75rem;
            }
            
            .logo-section img {
                height: 45px;
                margin-bottom: 0.3rem;
            }
            
            .form-group {
                margin-bottom: 0.9rem;
            }
            
            .form-label {
                font-size: 0.8rem;
                margin-bottom: 0.35rem;
            }
            
            .form-input {
                padding: 0.85rem 0.85rem 0.85rem 2.4rem;
                font-size: 16px;
            }
            
            .form-input-icon {
                left: 0.85rem;
                width: 16px;
                height: 16px;
            }
            
            .btn-login {
                padding: 0.85rem;
                font-size: 0.95rem;
            }
            
            .remember-me {
                margin-bottom: 0.9rem;
            }
            
            .remember-me label {
                font-size: 0.8rem;
            }
        }
        
        /* Responsive - Grands écrans */
        @media (min-width: 1200px) {
            .login-container {
                max-width: 480px;
            }
            
            .login-card {
                padding: 3.5rem;
            }
        }
        
        /* Orientation paysage sur mobile */
        @media (max-width: 768px) and (orientation: landscape) {
            body {
                padding: 10px;
                align-items: center;
                padding-top: 1rem;
                padding-bottom: 1rem;
            }
            
            .login-card {
                padding: 1.25rem 1.5rem;
                max-height: 95vh;
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            .logo-section {
                margin-bottom: 0.75rem;
            }
            
            .logo-section img {
                height: 45px;
                margin-bottom: 0.3rem;
            }
            
            .logo-section h1 {
                font-size: 1.2rem;
            }
            
            .logo-section p {
                font-size: 0.75rem;
            }
            
            .form-group {
                margin-bottom: 0.9rem;
            }
            
            .form-input {
                padding: 0.8rem 0.8rem 0.8rem 2.5rem;
            }
            
            .remember-me {
                margin-bottom: 0.9rem;
            }
            
            .btn-login {
                padding: 0.85rem;
            }
        }
        
        /* Amélioration pour les écrans tactiles */
        @media (hover: none) and (pointer: coarse) {
            .btn-login {
                min-height: 48px;
            }
            
            .form-input {
                font-size: 16px; /* Évite le zoom automatique sur iOS */
            }
            
            .remember-me input[type="checkbox"] {
                width: 22px;
                height: 22px;
            }
        }
    </style>
    <!-- GSAP Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
</head>
<body>
    <div class="bg-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
        </div>

    <div class="login-container">
        <div class="login-card">
            <div class="logo-section">
                <img src="{{ asset('logo.png') }}" alt="Gplanning Logo">
                <h1>Gplanning</h1>
                <p>Gestion des plannings - Gda Com</p>
        </div>

            @if(session('status'))
                <div class="session-status">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf
                
                <div class="form-group">
                    <label for="username" class="form-label">Nom d'utilisateur</label>
                    <div class="form-input-wrapper">
                        <input 
                            id="username" 
                            type="text" 
                            name="username" 
                            class="form-input" 
                            required 
                            autofocus 
                            autocomplete="off"
                            value=""
                            placeholder="Entrez votre nom d'utilisateur">
                        <span class="form-input-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </span>
                    </div>
                    @error('username')
                        <div class="error-message">
                            <span>⚠️</span>
                            <span>{{ $message }}</span>
                        </div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Mot de passe</label>
                    <div class="form-input-wrapper">
                        <input 
                            id="password" 
                            type="password" 
                            name="password" 
                            class="form-input" 
                            required 
                            autocomplete="new-password"
                            value=""
                            placeholder="Entrez votre mot de passe">
                        <button type="button" class="password-toggle-btn" id="passwordToggle" aria-label="Afficher le mot de passe">
                            <svg class="password-toggle-icon" id="passwordToggleIcon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <div class="error-message">
                            <span>⚠️</span>
                            <span>{{ $message }}</span>
                        </div>
                    @enderror
                </div>
                
                <div class="remember-me">
                    <input type="checkbox" id="remember_me" name="remember">
                    <label for="remember_me">Se souvenir de moi</label>
                </div>
                
                <button type="submit" class="btn-login" id="loginBtn">
                    <span>Se connecter</span>
                </button>
            </form>
        </div>
    </div>
    
    <script>
        // GSAP Animations
        document.addEventListener('DOMContentLoaded', function() {
            // Animate login card entrance
            gsap.from('.login-card', {
                opacity: 0,
                y: 50,
                scale: 0.9,
                duration: 0.8,
                ease: 'back.out(1.7)'
            });
            
            // Animate form inputs
            gsap.from('.form-group', {
                opacity: 0,
                x: -30,
                duration: 0.6,
                stagger: 0.1,
                delay: 0.3,
                ease: 'power2.out'
            });
            
            // Animate button
            gsap.from('.btn-login', {
                opacity: 0,
                y: 20,
                duration: 0.6,
                delay: 0.7,
                ease: 'power2.out'
            });
            
            // Input focus animations
            const inputs = document.querySelectorAll('.form-input');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    gsap.to(this, {
                        scale: 1.02,
                        duration: 0.3,
                        ease: 'power2.out'
                    });
                });
                
                input.addEventListener('blur', function() {
                    gsap.to(this, {
                        scale: 1,
                        duration: 0.3,
                        ease: 'power2.out'
                    });
                });
            });
            
            // Form submission
            const form = document.getElementById('loginForm');
            const btn = document.getElementById('loginBtn');
            
            form.addEventListener('submit', function() {
                btn.classList.add('loading');
                btn.disabled = true;
                
                gsap.to(btn, {
                    scale: 0.98,
                    duration: 0.2,
                    yoyo: true,
                    repeat: 1
                });
            });
            
            // Error animations
            const errors = document.querySelectorAll('.error-message');
            errors.forEach(error => {
                gsap.from(error, {
                    opacity: 0,
                    x: -20,
                    duration: 0.5,
                    ease: 'back.out(1.7)'
                });
            });
            
            // Toggle password visibility
            const passwordToggle = document.getElementById('passwordToggle');
            const passwordInput = document.getElementById('password');
            const passwordToggleIcon = document.getElementById('passwordToggleIcon');
            
            if (passwordToggle && passwordInput && passwordToggleIcon) {
                passwordToggle.addEventListener('click', function() {
                    const isPassword = passwordInput.type === 'password';
                    passwordInput.type = isPassword ? 'text' : 'password';
                    
                    // Changer le contenu de l'icône
                    if (isPassword) {
                        // Afficher l'icône "eye-off" (masquer)
                        passwordToggleIcon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
                        passwordToggle.setAttribute('aria-label', 'Masquer le mot de passe');
                    } else {
                        // Afficher l'icône "eye" (afficher)
                        passwordToggleIcon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
                        passwordToggle.setAttribute('aria-label', 'Afficher le mot de passe');
                    }
                });
            }
        });
    </script>
    <script src="{{ asset('js/pwa.js') }}"></script>
</body>
</html>
