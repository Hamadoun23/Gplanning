// Script PWA pour enregistrer le service worker et gérer l'installation
(function() {
    'use strict';

    // Vérifier si le navigateur supporte les service workers
    if ('serviceWorker' in navigator) {
        // Enregistrer le service worker
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('/sw.js')
                .then(function(registration) {
                    console.log('[PWA] Service Worker enregistré avec succès:', registration.scope);

                    // Vérifier les mises à jour du service worker
                    registration.addEventListener('updatefound', function() {
                        const newWorker = registration.installing;
                        newWorker.addEventListener('statechange', function() {
                            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                // Nouveau service worker disponible
                                showUpdateNotification();
                            }
                        });
                    });
                })
                .catch(function(error) {
                    console.error('[PWA] Erreur lors de l\'enregistrement du Service Worker:', error);
                });

            // Écouter les messages du service worker
            navigator.serviceWorker.addEventListener('message', function(event) {
                console.log('[PWA] Message reçu du Service Worker:', event.data);
            });
        });

        // Gérer l'événement de mise à jour du service worker
        let refreshing = false;
        navigator.serviceWorker.addEventListener('controllerchange', function() {
            if (!refreshing) {
                refreshing = true;
                window.location.reload();
            }
        });
    }

    // Gérer l'installation de l'app PWA
    let deferredPrompt;
    const installButton = document.getElementById('install-pwa-btn');

    window.addEventListener('beforeinstallprompt', function(e) {
        // Empêcher l'affichage automatique du prompt
        e.preventDefault();
        // Conserver l'événement pour l'afficher plus tard
        deferredPrompt = e;
        
        // Afficher le bouton d'installation si présent
        if (installButton) {
            installButton.style.display = 'block';
            installButton.addEventListener('click', installPWA);
        }
    });

    // Fonction pour installer l'app
    function installPWA() {
        if (!deferredPrompt) {
            return;
        }

        // Afficher le prompt d'installation
        deferredPrompt.prompt();

        // Attendre la réponse de l'utilisateur
        deferredPrompt.userChoice.then(function(choiceResult) {
            if (choiceResult.outcome === 'accepted') {
                console.log('[PWA] L\'utilisateur a accepté l\'installation');
            } else {
                console.log('[PWA] L\'utilisateur a refusé l\'installation');
            }
            
            deferredPrompt = null;
            
            // Masquer le bouton d'installation
            if (installButton) {
                installButton.style.display = 'none';
            }
        });
    }

    // Vérifier si l'app est déjà installée
    window.addEventListener('appinstalled', function() {
        console.log('[PWA] Application installée');
        deferredPrompt = null;
        if (installButton) {
            installButton.style.display = 'none';
        }
    });

    // Fonction pour afficher une notification de mise à jour
    function showUpdateNotification() {
        // Vous pouvez personnaliser cette notification
        if (confirm('Une nouvelle version de l\'application est disponible. Voulez-vous recharger la page ?')) {
            window.location.reload();
        }
    }

    // Exposer la fonction d'installation globalement
    window.installPWA = installPWA;
})();
