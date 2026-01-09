# 🧪 Plan de Tests pour la Mise en Production - Gplanning

## 📋 Table des matières

1. [Tests Fonctionnels](#tests-fonctionnels)
2. [Tests de Sécurité](#tests-de-sécurité)
3. [Tests d'Interface Utilisateur (UI/UX)](#tests-dinterface-utilisateur-uiux)
4. [Tests de Performance](#tests-de-performance)
5. [Tests de Compatibilité](#tests-de-compatibilité)
6. [Tests de Données](#tests-de-données)
7. [Tests d'Intégration](#tests-dintégration)
8. [Tests de Déploiement](#tests-de-déploiement)

---

## 1. Tests Fonctionnels

### 1.1 Authentification et Autorisation

#### ✅ Tests à effectuer :

- [ ] **Connexion Admin**
  - Se connecter avec un compte admin
  - Vérifier la redirection vers `/dashboard`
  - Vérifier l'accès à toutes les fonctionnalités admin

- [ ] **Connexion Client**
  - Se connecter avec un compte client (ex: Gda, TMC, Motors)
  - Vérifier la redirection vers `/clients/{client_id}/dashboard`
  - Vérifier que le client ne peut accéder qu'à son propre dashboard

- [ ] **Tentative d'accès non autorisé**
  - Client essayant d'accéder à `/dashboard` (admin) → doit être bloqué (403)
  - Client essayant d'accéder à `/clients/{autre_client_id}/dashboard` → doit être bloqué (403)
  - Utilisateur non connecté essayant d'accéder à une route protégée → redirection vers login

- [ ] **Déconnexion**
  - Cliquer sur "Déconnexion" depuis l'espace client
  - Cliquer sur "Déconnexion" depuis l'espace admin
  - Vérifier la redirection vers la page de login
  - Vérifier que les sessions sont bien détruites

- [ ] **Messages d'erreur de connexion**
  - Tentative avec mauvais nom d'utilisateur → message "Nom d'utilisateur ou mot de passe incorrect"
  - Tentative avec mauvais mot de passe → message "Nom d'utilisateur ou mot de passe incorrect"
  - Vérifier que les champs sont vides après une erreur

### 1.2 Gestion des Clients (Admin uniquement)

- [ ] **Création d'un client**
  - Accéder à `/clients/create`
  - Remplir le formulaire avec un nom d'entreprise
  - Soumettre et vérifier la création
  - Vérifier l'apparition dans la liste des clients

- [ ] **Modification d'un client**
  - Accéder à `/clients/{id}/edit`
  - Modifier le nom d'entreprise
  - Soumettre et vérifier la mise à jour

- [ ] **Suppression d'un client**
  - Supprimer un client
  - Vérifier que les tournages et publications associés sont gérés (cascade ou protection)

- [ ] **Affichage des statistiques client**
  - Vérifier le nombre de tournages affichés
  - Vérifier le nombre de publications affichées
  - Vérifier le nombre de règles de publication

### 1.3 Gestion des Idées de Contenu (Admin uniquement)

- [ ] **Création d'une idée de contenu**
  - Créer une idée avec type "vidéo"
  - Créer une idée avec type "image"
  - Créer une idée avec type "texte"
  - Vérifier que les idées sont globales (visibles pour tous les clients)

- [ ] **Modification d'une idée de contenu**
  - Modifier le titre
  - Modifier le type
  - Vérifier la mise à jour

- [ ] **Suppression d'une idée de contenu**
  - Supprimer une idée utilisée dans un tournage → vérifier le comportement
  - Supprimer une idée utilisée dans une publication → vérifier le comportement

### 1.4 Gestion des Tournages (Admin uniquement)

- [ ] **Création d'un tournage**
  - Accéder à `/shootings/create`
  - Sélectionner un client
  - Choisir une date
  - Sélectionner une ou plusieurs idées de contenu
  - Ajouter une description optionnelle
  - Vérifier les alertes de conflits de dates
  - Soumettre et vérifier la création

- [ ] **Modification d'un tournage**
  - Modifier la date, le client, les idées de contenu
  - Vérifier les alertes de conflits

- [ ] **Actions sur un tournage**
  - Marquer comme "Complété" → vérifier le changement de statut
  - Marquer comme "Annulé" → vérifier le changement de statut
  - Reprogrammer → vérifier la mise à jour de la date

- [ ] **Affichage du calendrier des tournages**
  - Naviguer entre les mois (← et →)
  - Sélectionner un mois/année dans les dropdowns
  - Vérifier que le calendrier se met à jour sans rechargement de page (AJAX)
  - Cliquer sur une date pour voir les détails

- [ ] **Export Excel du calendrier des tournages**
  - Exporter le calendrier
  - Vérifier le format du fichier CSV
  - Vérifier que toutes les données sont présentes

### 1.5 Gestion des Publications (Admin uniquement)

- [ ] **Création d'une publication**
  - Accéder à `/publications/create`
  - Sélectionner un client
  - Choisir une date
  - Sélectionner une idée de contenu
  - Lier optionnellement à un tournage (seulement les tournages disponibles)
  - Vérifier les alertes de conflits de dates
  - Vérifier les alertes de jours non recommandés (règles de publication)
  - Soumettre et vérifier la création

- [ ] **Modification d'une publication**
  - Modifier la date, le client, l'idée de contenu, le tournage lié
  - Vérifier les alertes

- [ ] **Actions sur une publication**
  - Marquer comme "Complétée" → vérifier le changement de statut
  - Marquer comme "Annulée" → vérifier le changement de statut
  - Reprogrammer → vérifier la mise à jour de la date

- [ ] **Affichage du calendrier des publications**
  - Naviguer entre les mois (← et →)
  - Sélectionner un mois/année dans les dropdowns
  - Vérifier que le calendrier se met à jour sans rechargement de page (AJAX)
  - Cliquer sur une date pour voir les détails

- [ ] **Export Excel du calendrier des publications**
  - Exporter le calendrier
  - Vérifier le format du fichier CSV

### 1.6 Règles de Publication (Admin uniquement)

- [ ] **Création d'une règle**
  - Accéder à `/clients/{id}/publication-rules/create`
  - Sélectionner un jour de la semaine (lundi, mardi, etc.)
  - Soumettre et vérifier la création

- [ ] **Suppression d'une règle**
  - Supprimer une règle
  - Vérifier qu'elle n'apparaît plus dans la liste

- [ ] **Vérification des alertes**
  - Créer une publication sur un jour non recommandé
  - Vérifier que l'alerte s'affiche correctement

### 1.7 Dashboard Admin

- [ ] **Affichage du calendrier combiné**
  - Vérifier l'affichage des tournages et publications
  - Vérifier les couleurs différentes pour chaque type d'événement
  - Vérifier les alertes visuelles (retards, événements à venir)

- [ ] **Navigation du calendrier**
  - Utiliser les boutons ← et → pour naviguer
  - Vérifier que les selects mois/année se mettent à jour
  - Vérifier que le calendrier se met à jour sans rechargement (AJAX)
  - Changer le mois/année dans les selects → vérifier la mise à jour automatique

- [ ] **Export Excel du calendrier global**
  - Exporter le calendrier combiné
  - Vérifier le format et les données

- [ ] **Génération de rapport Word**
  - Sélectionner un ou plusieurs clients
  - Choisir un mois/année
  - Générer le rapport
  - Vérifier le format du fichier Word
  - Vérifier que toutes les données sont présentes

- [ ] **Statistiques**
  - Vérifier l'affichage des statistiques (nombre de tournages, publications, etc.)

### 1.8 Dashboard Client

- [ ] **Affichage du calendrier client**
  - Vérifier que seuls les événements du client connecté sont affichés
  - Vérifier les couleurs et les alertes

- [ ] **Navigation du calendrier**
  - Utiliser les boutons ← et → pour naviguer
  - Vérifier que les selects mois/année se mettent à jour
  - Vérifier que le calendrier se met à jour sans rechargement (AJAX)
  - Changer le mois/année dans les selects → vérifier la mise à jour automatique

- [ ] **Affichage responsive**
  - Vérifier le défilement horizontal sur mobile
  - Vérifier que tous les jours de la semaine sont visibles

- [ ] **Clic sur une date**
  - Cliquer sur une date avec événements
  - Vérifier l'affichage de la modal avec les détails
  - Vérifier les informations affichées (tournages, publications)

### 1.9 Comparaison de Plannings (Admin uniquement)

- [ ] **Sélection de clients**
  - Accéder à `/planning-comparison`
  - Sélectionner plusieurs clients
  - Vérifier l'affichage du calendrier comparatif

- [ ] **Navigation du calendrier comparatif**
  - Naviguer entre les mois
  - Vérifier que tous les événements des clients sélectionnés sont affichés

### 1.10 Fonctionnalités AJAX

- [ ] **Navigation calendrier admin**
  - Cliquer sur ← ou → → vérifier la mise à jour AJAX
  - Changer le mois/année dans les selects → vérifier la mise à jour AJAX
  - Vérifier qu'il n'y a pas de rechargement de page

- [ ] **Navigation calendrier client**
  - Cliquer sur ← ou → → vérifier la mise à jour AJAX
  - Changer le mois/année dans les selects → vérifier la mise à jour AJAX
  - Vérifier qu'il n'y a pas de rechargement de page

- [ ] **Modales de détails**
  - Cliquer sur une date → vérifier le chargement AJAX des événements
  - Vérifier l'affichage des détails

- [ ] **Vérification de dates en temps réel**
  - Lors de la création d'un tournage/publication
  - Vérifier que les alertes de conflits s'affichent en temps réel
  - Vérifier que les alertes de jours non recommandés s'affichent

---

## 2. Tests de Sécurité

### 2.1 Authentification

- [ ] **Protection CSRF**
  - Vérifier que tous les formulaires ont un token CSRF
  - Tenter de soumettre un formulaire sans token → doit être rejeté

- [ ] **Protection XSS**
  - Entrer du code JavaScript dans les champs texte (nom client, description, etc.)
  - Vérifier que le code est échappé et ne s'exécute pas

- [ ] **Protection SQL Injection**
  - Tenter des injections SQL dans les champs de recherche
  - Vérifier que les requêtes sont sécurisées (utilisation d'Eloquent)

- [ ] **Sessions**
  - Vérifier que les sessions expirent correctement
  - Vérifier que les sessions sont régénérées après connexion

### 2.2 Autorisation

- [ ] **Routes protégées**
  - Tester toutes les routes admin sans être connecté → redirection login
  - Tester toutes les routes admin en tant que client → erreur 403

- [ ] **Accès client**
  - Client essayant d'accéder à son propre dashboard → autorisé
  - Client essayant d'accéder au dashboard d'un autre client → erreur 403
  - Client essayant d'accéder aux routes CRUD (clients, shootings, etc.) → erreur 403

- [ ] **Paramètres d'URL**
  - Tenter de modifier l'ID client dans l'URL → doit être bloqué
  - Tenter d'accéder à des ressources d'autres clients via l'API → doit être bloqué

### 2.3 Validation des Données

- [ ] **Validation des formulaires**
  - Soumettre des formulaires avec des champs vides (requis) → erreur de validation
  - Soumettre des formulaires avec des données invalides → erreur de validation
  - Vérifier que les messages d'erreur sont en français

- [ ] **Validation des dates**
  - Tenter de créer un événement avec une date invalide
  - Tenter de créer un événement avec une date dans le passé (si non autorisé)

- [ ] **Limites de taille**
  - Tester les limites de caractères dans les champs texte
  - Vérifier que les limites sont respectées

### 2.4 Fichiers et Exports

- [ ] **Export Excel**
  - Vérifier que les exports ne contiennent pas de données sensibles
  - Vérifier que les exports sont bien formatés

- [ ] **Export Word**
  - Vérifier que les rapports Word sont bien générés
  - Vérifier que les données sont correctes

---

## 3. Tests d'Interface Utilisateur (UI/UX)

### 3.1 Page de Connexion

- [ ] **Affichage**
  - Vérifier que la carte de connexion est centrée (verticalement et horizontalement)
  - Vérifier que le fond occupe toute la page
  - Vérifier que les champs sont vides par défaut
  - Vérifier que l'autocomplétion est désactivée

- [ ] **Bouton d'affichage du mot de passe**
  - Cliquer sur le bouton → le mot de passe doit s'afficher
  - Cliquer à nouveau → le mot de passe doit se masquer
  - Vérifier qu'il n'y a qu'un seul bouton/icône

- [ ] **Responsive**
  - Tester sur mobile (320px, 375px, 414px)
  - Tester sur tablette (768px, 1024px)
  - Tester sur desktop (1920px)
  - Vérifier que la carte reste centrée et lisible sur tous les écrans

### 3.2 Dashboard Admin

- [ ] **Responsive**
  - Tester sur mobile → vérifier que le calendrier est scrollable horizontalement
  - Tester sur tablette → vérifier la mise en page
  - Tester sur desktop → vérifier la mise en page complète

- [ ] **Navigation**
  - Vérifier que les boutons ← et → sont visibles et cliquables
  - Vérifier que les selects mois/année sont bien stylisés
  - Vérifier que le bouton "Exporter" est visible

- [ ] **Calendrier**
  - Vérifier que le calendrier est centré
  - Vérifier que les événements sont bien colorés
  - Vérifier que les alertes (retards, à venir) sont visibles

### 3.3 Dashboard Client

- [ ] **Responsive**
  - Tester sur mobile → vérifier le défilement horizontal du calendrier
  - Vérifier que tous les jours (lundi à dimanche) sont accessibles par défilement
  - Tester sur tablette et desktop

- [ ] **Navigation**
  - Vérifier que les boutons ← et → fonctionnent
  - Vérifier que les selects mois/année sont bien stylisés
  - Vérifier que le calendrier se met à jour sans rechargement

- [ ] **Header**
  - Vérifier l'affichage du logo
  - Vérifier l'affichage du nom d'utilisateur
  - Vérifier que le bouton "Déconnexion" est visible et fonctionnel

### 3.4 Formulaires

- [ ] **Création/Modification**
  - Vérifier que tous les champs sont bien stylisés
  - Vérifier que les labels sont clairs
  - Vérifier que les messages d'erreur sont visibles
  - Vérifier que les alertes de conflits sont bien affichées

- [ ] **Sélecteurs**
  - Vérifier que les selects sont bien stylisés avec les flèches personnalisées
  - Vérifier que les selects sont fonctionnels sur mobile

### 3.5 Modales

- [ ] **Modales de détails**
  - Vérifier que les modales s'ouvrent correctement
  - Vérifier que les modales se ferment avec le bouton X
  - Vérifier que les modales se ferment avec la touche Escape
  - Vérifier que les modales se ferment en cliquant en dehors (si implémenté)

- [ ] **Contenu des modales**
  - Vérifier que toutes les informations sont affichées
  - Vérifier que les liens fonctionnent
  - Vérifier que les actions (modifier, supprimer) sont accessibles

### 3.6 Animations et Transitions

- [ ] **Chargement AJAX**
  - Vérifier l'affichage d'un indicateur de chargement lors des requêtes AJAX
  - Vérifier que les transitions sont fluides

- [ ] **Animations GSAP**
  - Vérifier que les animations fonctionnent correctement
  - Vérifier qu'il n'y a pas de ralentissements

---

## 4. Tests de Performance

### 4.1 Temps de Chargement

- [ ] **Page de connexion**
  - Mesurer le temps de chargement initial
  - Vérifier que c'est < 2 secondes

- [ ] **Dashboard admin**
  - Mesurer le temps de chargement initial
  - Vérifier que c'est < 3 secondes

- [ ] **Dashboard client**
  - Mesurer le temps de chargement initial
  - Vérifier que c'est < 3 secondes

- [ ] **Requêtes AJAX**
  - Mesurer le temps de réponse des requêtes AJAX
  - Vérifier que c'est < 1 seconde

### 4.2 Base de Données

- [ ] **Requêtes optimisées**
  - Vérifier l'utilisation de `with()` pour éviter les requêtes N+1
  - Vérifier que les index sont présents sur les colonnes fréquemment utilisées (date, client_id)

- [ ] **Volume de données**
  - Tester avec un grand nombre de clients (50+)
  - Tester avec un grand nombre de tournages/publications (1000+)
  - Vérifier que les performances restent acceptables

### 4.3 Optimisation Frontend

- [ ] **Images**
  - Vérifier que les images sont optimisées
  - Vérifier que le logo n'est pas trop lourd

- [ ] **CSS/JS**
  - Vérifier que le CSS est minifié en production
  - Vérifier que le JavaScript est minifié en production

---

## 5. Tests de Compatibilité

### 5.1 Navigateurs

- [ ] **Chrome** (dernière version)
  - Tester toutes les fonctionnalités
  - Vérifier l'affichage responsive

- [ ] **Firefox** (dernière version)
  - Tester toutes les fonctionnalités
  - Vérifier l'affichage responsive

- [ ] **Safari** (dernière version)
  - Tester toutes les fonctionnalités
  - Vérifier l'affichage responsive

- [ ] **Edge** (dernière version)
  - Tester toutes les fonctionnalités
  - Vérifier l'affichage responsive

### 5.2 Appareils

- [ ] **Mobile**
  - iPhone (Safari)
  - Android (Chrome)
  - Vérifier le responsive et le défilement horizontal

- [ ] **Tablette**
  - iPad (Safari)
  - Android (Chrome)
  - Vérifier la mise en page

- [ ] **Desktop**
  - Résolutions : 1920x1080, 1366x768, 2560x1440
  - Vérifier que tout est bien affiché

---

## 6. Tests de Données

### 6.1 Intégrité des Données

- [ ] **Relations**
  - Supprimer un client → vérifier le comportement des tournages/publications
  - Supprimer une idée de contenu → vérifier le comportement des tournages/publications
  - Vérifier que les foreign keys sont bien configurées

- [ ] **Cohérence**
  - Vérifier que les dates sont cohérentes
  - Vérifier que les statuts sont valides
  - Vérifier que les relations sont correctes

### 6.2 Migration et Seeders

- [ ] **Migration**
  - Exécuter `php artisan migrate:fresh` → vérifier qu'il n'y a pas d'erreurs
  - Vérifier que toutes les tables sont créées

- [ ] **Seeders**
  - Exécuter `php artisan db:seed` → vérifier que les données sont créées
  - Vérifier que les utilisateurs sont créés avec les bons rôles
  - Vérifier que les clients sont créés
  - Vérifier que les relations client_id sont bien assignées

### 6.3 Exports

- [ ] **Export Excel**
  - Exporter avec des données réelles
  - Ouvrir dans Excel/LibreOffice
  - Vérifier que toutes les données sont présentes et correctes
  - Vérifier le formatage (dates, nombres)

- [ ] **Export Word**
  - Générer un rapport avec des données réelles
  - Ouvrir dans Word/LibreOffice
  - Vérifier que toutes les données sont présentes
  - Vérifier le formatage et la mise en page

---

## 7. Tests d'Intégration

### 7.1 Flux Complets

- [ ] **Création d'un planning complet**
  1. Créer un client
  2. Créer des idées de contenu
  3. Créer des règles de publication
  4. Créer des tournages
  5. Créer des publications liées aux tournages
  6. Vérifier l'affichage dans le dashboard admin
  7. Vérifier l'affichage dans le dashboard client
  8. Exporter le calendrier
  9. Générer un rapport Word

- [ ] **Cycle de vie d'un événement**
  1. Créer un tournage en statut "pending"
  2. Le marquer comme "completed"
  3. Vérifier que le statut est mis à jour partout
  4. Créer une publication liée
  5. Reprogrammer le tournage
  6. Vérifier que la publication est toujours liée

### 7.2 Interactions entre Modules

- [ ] **Idées de contenu partagées**
  - Créer une idée de contenu
  - L'utiliser dans un tournage pour le client A
  - L'utiliser dans une publication pour le client B
  - Vérifier que les deux utilisations fonctionnent

- [ ] **Règles de publication**
  - Créer une règle pour un client (ex: pas de publication le lundi)
  - Créer une publication le lundi → vérifier l'alerte
  - Créer une publication le mardi → vérifier qu'il n'y a pas d'alerte

---

## 8. Tests de Déploiement

### 8.1 Configuration Production

- [ ] **Variables d'environnement**
  - Vérifier que `APP_ENV=production`
  - Vérifier que `APP_DEBUG=false`
  - Vérifier que `APP_URL` est correct
  - Vérifier les paramètres de base de données

- [ ] **Optimisation Laravel**
  - Exécuter `php artisan config:cache`
  - Exécuter `php artisan route:cache`
  - Exécuter `php artisan view:cache`
  - Vérifier que les caches sont créés

### 8.2 Base de Données Production

- [ ] **Migration**
  - Exécuter les migrations sur la base de données de production
  - Vérifier qu'il n'y a pas d'erreurs
  - Vérifier que toutes les tables sont créées

- [ ] **Seeders**
  - Exécuter les seeders pour créer les utilisateurs initiaux
  - Vérifier que les données sont créées

- [ ] **Backup**
  - Configurer un système de backup automatique
  - Tester la restauration d'un backup

### 8.3 Serveur Web

- [ ] **Permissions**
  - Vérifier les permissions sur `storage/` et `bootstrap/cache/`
  - Vérifier que l'application peut écrire dans ces dossiers

- [ ] **HTTPS**
  - Vérifier que HTTPS est activé
  - Vérifier que les certificats SSL sont valides

- [ ] **Performance**
  - Configurer OPcache (PHP)
  - Vérifier que les performances sont optimales

### 8.4 Tests Post-Déploiement

- [ ] **Vérification fonctionnelle**
  - Tester la connexion
  - Tester les fonctionnalités principales
  - Vérifier que les exports fonctionnent

- [ ] **Monitoring**
  - Configurer un système de monitoring (logs, erreurs)
  - Vérifier que les logs sont bien enregistrés

---

## 📝 Checklist Finale avant Production

### Configuration
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `APP_URL` correct
- [ ] Base de données configurée
- [ ] Caches Laravel générés

### Sécurité
- [ ] Tous les tests de sécurité passés
- [ ] HTTPS activé
- [ ] Permissions fichiers correctes
- [ ] Tokens CSRF fonctionnels

### Fonctionnalités
- [ ] Tous les tests fonctionnels passés
- [ ] Tous les tests UI/UX passés
- [ ] Tous les tests d'intégration passés

### Performance
- [ ] Temps de chargement acceptables
- [ ] Requêtes AJAX rapides
- [ ] Base de données optimisée

### Compatibilité
- [ ] Testé sur les principaux navigateurs
- [ ] Testé sur mobile et tablette
- [ ] Responsive fonctionnel

### Documentation
- [ ] Documentation à jour
- [ ] Guide d'utilisation pour les utilisateurs
- [ ] Guide d'administration

---

## 🐛 Bugs Connus à Vérifier

- [ ] Vérifier qu'il n'y a pas de doublons d'icônes (ex: password toggle)
- [ ] Vérifier que les champs de formulaire sont bien vides par défaut
- [ ] Vérifier que l'autocomplétion est bien désactivée
- [ ] Vérifier que les calendriers se mettent à jour correctement en AJAX
- [ ] Vérifier que les modales fonctionnent correctement après les mises à jour AJAX

---

## 📞 Support et Maintenance

- [ ] Documenter les procédures de maintenance
- [ ] Documenter les procédures de backup/restauration
- [ ] Préparer un plan de rollback en cas de problème
- [ ] Configurer un système de monitoring des erreurs

---

**Date de création :** 2026-01-09  
**Dernière mise à jour :** 2026-01-09
