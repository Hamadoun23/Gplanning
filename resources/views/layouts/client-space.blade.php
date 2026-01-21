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
    <title>@yield('title', 'Espace Client') - Gda Com</title>
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
        
        /* Header simplifié pour espace client */
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
        
        /* Responsive Header */
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
        }
        
        header .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
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
        
        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #303030;
        }
        
        .empty-state p {
            color: #999;
            font-style: italic;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="logo-container">
                <img src="{{ asset('logo.png') }}" alt="Gplanning Logo">
                <div>
                    <h1>Gplanning</h1>
                    <p>Espace Client - Gda Com</p>
                </div>
            </div>
            <div class="user-menu">
                @auth
                    <span>{{ Auth::user()->username }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn">Déconnexion</button>
                    </form>
                @endauth
            </div>
        </div>
    </header>
    
    <div class="container">
        @yield('content')
    </div>
    <script src="{{ asset('js/pwa.js') }}"></script>
</body>
</html>
