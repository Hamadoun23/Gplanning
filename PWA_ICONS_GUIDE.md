# Guide de Création des Icônes PWA

## 📱 Icônes Requises

Pour que l'application PWA fonctionne correctement, vous devez créer les icônes suivantes dans le dossier `public/` :

- `icon-72x72.png`
- `icon-96x96.png`
- `icon-128x128.png`
- `icon-144x144.png`
- `icon-152x152.png`
- `icon-192x192.png`
- `icon-384x384.png`
- `icon-512x512.png`

## 🛠️ Méthodes de Création

### Option 1: Utiliser un Outil en Ligne (Recommandé)

1. Allez sur [PWA Asset Generator](https://www.pwabuilder.com/imageGenerator)
2. Téléchargez votre logo (`public/logo.png`)
3. Le site générera automatiquement toutes les tailles nécessaires
4. Téléchargez le ZIP et extrayez les fichiers dans `public/`

### Option 2: Utiliser ImageMagick (si installé)

```bash
# Installer ImageMagick (si nécessaire)
# Windows: Télécharger depuis https://imagemagick.org/script/download.php
# Linux: sudo apt-get install imagemagick
# macOS: brew install imagemagick

# Créer toutes les icônes
for size in 72 96 128 144 152 192 384 512; do
    convert public/logo.png -resize ${size}x${size} public/icon-${size}x${size}.png
done
```

### Option 3: Utiliser GIMP ou Photoshop

1. Ouvrez `public/logo.png` dans GIMP/Photoshop
2. Pour chaque taille (72, 96, 128, 144, 152, 192, 384, 512) :
   - Redimensionnez l'image à la taille exacte (ex: 192x192px)
   - Exportez en PNG avec le nom `icon-{size}x{size}.png`
   - Placez le fichier dans `public/`

### Option 4: Utiliser le Script PHP (si GD est installé)

```bash
php create-pwa-icons.php
```

**Note:** Si l'extension GD n'est pas disponible, installez-la :
- Windows: Décommentez `extension=gd` dans `php.ini`
- Linux: `sudo apt-get install php-gd`
- macOS: `brew install php-gd`

## ✅ Vérification

Après avoir créé les icônes, vérifiez que tous les fichiers existent :

```bash
# Windows (PowerShell)
Get-ChildItem public\icon-*.png

# Linux/macOS
ls public/icon-*.png
```

Vous devriez voir 8 fichiers.

## 🎨 Recommandations

- **Format:** PNG avec transparence
- **Couleur de fond:** Transparent ou couleur de thème (#FF6A3A)
- **Style:** Simple et reconnaissable même en petite taille
- **Taille minimale:** Utilisez une image source d'au moins 512x512px pour une meilleure qualité

## 📝 Notes

- Les icônes sont utilisées par le navigateur pour :
  - L'écran d'accueil sur mobile
  - Le splash screen lors du lancement
  - L'icône dans le gestionnaire d'applications
- Assurez-vous que les icônes sont carrées (ratio 1:1)
- Testez l'installation PWA sur différents appareils pour vérifier l'affichage
