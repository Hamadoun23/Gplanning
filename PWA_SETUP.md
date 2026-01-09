# 🚀 Configuration PWA - Gplanning

## ✅ Fichiers Créés

L'application a été configurée comme PWA avec les fichiers suivants :

### 1. Manifest (`public/manifest.json`)
- Définit les métadonnées de l'application
- Configure l'affichage en mode standalone
- Définit les icônes et les raccourcis

### 2. Service Worker (`public/sw.js`)
- Gère la mise en cache des ressources
- Permet le fonctionnement hors ligne (offline)
- Stratégie de cache : Network First, puis Cache

### 3. Script PWA (`public/js/pwa.js`)
- Enregistre le service worker
- Gère l'installation de l'app
- Gère les mises à jour

### 4. Modifications des Layouts
- `resources/views/layouts/app.blade.php`
- `resources/views/layouts/client-space.blade.php`
- `resources/views/auth/login.blade.php`

Tous les layouts incluent maintenant :
- Les meta tags PWA
- Le lien vers le manifest
- Le script PWA

## 📋 Étapes de Finalisation

### 1. Créer les Icônes PWA

**IMPORTANT:** Vous devez créer les icônes avant de tester la PWA.

Voir le fichier `PWA_ICONS_GUIDE.md` pour les instructions détaillées.

**Méthode rapide:**
1. Allez sur https://www.pwabuilder.com/imageGenerator
2. Téléchargez `public/logo.png`
3. Générez les icônes
4. Téléchargez et extrayez dans `public/`

### 2. Tester la PWA

#### Sur Chrome/Edge (Desktop)
1. Ouvrez l'application dans Chrome
2. Ouvrez les DevTools (F12)
3. Allez dans l'onglet "Application"
4. Vérifiez que le Service Worker est enregistré
5. Vérifiez que le Manifest est valide
6. Testez l'installation via le bouton dans la barre d'adresse

#### Sur Mobile (Android)
1. Ouvrez l'application dans Chrome
2. Le navigateur proposera automatiquement l'installation
3. Ou utilisez le menu (⋮) → "Ajouter à l'écran d'accueil"

#### Sur iOS (Safari)
1. Ouvrez l'application dans Safari
2. Appuyez sur le bouton de partage (□↑)
3. Sélectionnez "Sur l'écran d'accueil"
4. L'app apparaîtra comme une application native

### 3. Vérifier le Mode Offline

1. Installez l'application PWA
2. Ouvrez l'application
3. Ouvrez les DevTools → Network
4. Activez "Offline"
5. Rechargez la page
6. L'application devrait fonctionner avec les ressources mises en cache

## 🔧 Configuration Serveur

### Apache (.htaccess)

Assurez-vous que votre `.htaccess` permet l'accès aux fichiers PWA :

```apache
# Autoriser l'accès aux fichiers PWA
<Files "manifest.json">
    Header set Content-Type "application/manifest+json"
</Files>

<Files "sw.js">
    Header set Content-Type "application/javascript"
    Header set Service-Worker-Allowed "/"
</Files>
```

### Nginx

Ajoutez dans votre configuration :

```nginx
location /manifest.json {
    add_header Content-Type application/manifest+json;
}

location /sw.js {
    add_header Content-Type application/javascript;
    add_header Service-Worker-Allowed /;
}
```

## 🐛 Dépannage

### Le Service Worker ne s'enregistre pas

1. Vérifiez la console pour les erreurs
2. Assurez-vous que l'application est servie en HTTPS (ou localhost)
3. Vérifiez que `sw.js` est accessible à la racine

### Les icônes ne s'affichent pas

1. Vérifiez que tous les fichiers `icon-*.png` existent dans `public/`
2. Vérifiez les chemins dans `manifest.json`
3. Videz le cache du navigateur

### L'app ne fonctionne pas hors ligne

1. Vérifiez que le Service Worker est actif
2. Vérifiez la console pour les erreurs de cache
3. Testez avec les DevTools → Application → Cache Storage

## 📱 Fonctionnalités PWA

### ✅ Implémentées

- [x] Manifest.json avec métadonnées complètes
- [x] Service Worker avec stratégie de cache
- [x] Installation sur l'écran d'accueil
- [x] Mode standalone (sans barre d'adresse)
- [x] Thème color personnalisé (#FF6A3A)
- [x] Raccourcis vers Dashboard et Calendrier
- [x] Support iOS (Apple Touch Icon)

### 🔄 Améliorations Futures Possibles

- [ ] Notification push
- [ ] Synchronisation en arrière-plan
- [ ] Mise à jour automatique du cache
- [ ] Mode offline complet avec IndexedDB
- [ ] Partage de fichiers (Web Share API)

## 📚 Ressources

- [MDN - Progressive Web Apps](https://developer.mozilla.org/fr/docs/Web/Progressive_web_apps)
- [PWA Builder](https://www.pwabuilder.com/)
- [Service Worker API](https://developer.mozilla.org/fr/docs/Web/API/Service_Worker_API)
- [Web App Manifest](https://developer.mozilla.org/fr/docs/Web/Manifest)

## ✅ Checklist de Déploiement

Avant de mettre en production :

- [ ] Toutes les icônes sont créées et présentes
- [ ] Le manifest.json est valide (tester avec PWA Builder)
- [ ] Le Service Worker fonctionne correctement
- [ ] L'application fonctionne en mode offline
- [ ] L'installation fonctionne sur Android et iOS
- [ ] Les meta tags sont corrects
- [ ] HTTPS est activé (requis pour PWA en production)
- [ ] Les permissions serveur sont correctes

---

**Date de création:** 2026-01-09  
**Version:** 1.0.0
