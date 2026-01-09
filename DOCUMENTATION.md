# Documentation Gplanning

## 📋 Table des matières

1. [Vue d'ensemble](#vue-densemble)
2. [Architecture du projet](#architecture-du-projet)
3. [Modèles (Models)](#modèles-models)
4. [Migrations de base de données](#migrations-de-base-de-données)
5. [Contrôleurs (Controllers)](#contrôleurs-controllers)
6. [Vues (Views)](#vues-views)
7. [Routes](#routes)
8. [Fonctionnalités principales](#fonctionnalités-principales)
9. [UX et JavaScript](#ux-et-javascript)
10. [Configuration](#configuration)

---

## Vue d'ensemble

**Gplanning** est une application web de gestion de planning développée avec Laravel 10 pour **Gda Com**. Elle permet de gérer efficacement les tournages et publications de contenu pour plusieurs clients.

### Objectifs principaux

- Gestion centralisée des clients et de leurs plannings
- Planification des tournages et publications avec calendrier visuel
- Gestion des idées de contenu partagées entre tous les clients
- Système de règles de publication par client (jours non recommandés)
- Alertes automatiques pour les événements en retard ou à venir
- Export des plannings en Excel et génération de rapports Word
- Comparaison de plannings entre plusieurs clients

### Technologies utilisées

- **Backend** : Laravel 10 (PHP 8.1+)
- **Base de données** : MySQL/MariaDB
- **Frontend** : Blade Templates, CSS3, JavaScript (ES6+)
- **Animations** : GSAP (GreenSock Animation Platform)
- **Export** : CSV pour Excel, HTML pour Word (via PHPOffice/PhpWord)

---

## Architecture du projet

### Structure des dossiers

```
gplanning/
├── app/
│   ├── Http/
│   │   └── Controllers/     # Contrôleurs de l'application
│   └── Models/              # Modèles Eloquent
├── database/
│   └── migrations/          # Migrations de base de données
├── resources/
│   └── views/              # Vues Blade
│       ├── clients/
│       ├── content-ideas/
│       ├── publications/
│       ├── shootings/
│       ├── planning-comparison/
│       └── layouts/
├── routes/
│   └── web.php             # Routes web
├── public/
│   ├── js/
│   │   └── gplanning-ux.js # Scripts UX personnalisés
│   └── logo.png            # Logo de l'application
└── config/                  # Fichiers de configuration
```

---

## Modèles (Models)

### 1. Client (`app/Models/Client.php`)

Représente un client de l'entreprise.

**Attributs :**
- `id` : Identifiant unique
- `nom_entreprise` : Nom de l'entreprise (requis, max 255 caractères)

**Relations :**
- `publicationRules()` : HasMany → PublicationRule
- `shootings()` : HasMany → Shooting
- `publications()` : HasMany → Publication

**Méthodes :**
- `isDayNotRecommended(string $dayOfWeek): bool` : Vérifie si un jour de la semaine est non recommandé pour les publications

### 2. ContentIdea (`app/Models/ContentIdea.php`)

Représente une idée de contenu partagée entre tous les clients.

**Attributs :**
- `id` : Identifiant unique
- `titre` : Titre de l'idée (requis, max 255 caractères)
- `type` : Type de contenu (enum: 'vidéo', 'image', 'texte')

**Relations :**
- `shootings()` : BelongsToMany → Shooting (table pivot: `content_idea_shooting`)
- `publications()` : HasMany → Publication

**Note importante :** Les idées de contenu sont globales et peuvent être utilisées par tous les clients.

### 3. Shooting (`app/Models/Shooting.php`)

Représente un tournage planifié.

**Attributs :**
- `id` : Identifiant unique
- `client_id` : Référence au client (foreign key)
- `date` : Date du tournage (requis)
- `status` : Statut (enum: 'pending', 'completed', 'cancelled', default: 'pending')
- `description` : Description optionnelle du tournage (text, nullable)

**Relations :**
- `client()` : BelongsTo → Client
- `contentIdeas()` : BelongsToMany → ContentIdea (table pivot: `content_idea_shooting`)
- `publications()` : HasMany → Publication

**Méthodes :**
- `isOverdue(): bool` : Vérifie si le tournage est en retard
- `isUpcoming(): bool` : Vérifie si le tournage approche (dans les 3 prochains jours)
- `isCompleted(): bool` : Vérifie si le tournage est complété

### 4. Publication (`app/Models/Publication.php`)

Représente une publication planifiée.

**Attributs :**
- `id` : Identifiant unique
- `client_id` : Référence au client (foreign key)
- `date` : Date de publication (requis)
- `content_idea_id` : Référence à l'idée de contenu (foreign key)
- `shooting_id` : Référence optionnelle au tournage lié (foreign key, nullable)
- `status` : Statut (enum: 'pending', 'completed', 'cancelled', default: 'pending')
- `description` : Description optionnelle de la publication (text, nullable)

**Relations :**
- `client()` : BelongsTo → Client
- `contentIdea()` : BelongsTo → ContentIdea
- `shooting()` : BelongsTo → Shooting (nullable)

**Méthodes :**
- `isOverdue(): bool` : Vérifie si la publication est en retard
- `isUpcoming(): bool` : Vérifie si la publication approche (dans les 3 prochains jours)
- `isCompleted(): bool` : Vérifie si la publication est complétée

### 5. PublicationRule (`app/Models/PublicationRule.php`)

Représente une règle de publication pour un client (jour non recommandé).

**Attributs :**
- `id` : Identifiant unique
- `client_id` : Référence au client (foreign key)
- `day_of_week` : Jour de la semaine non recommandé (enum: 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche')

**Relations :**
- `client()` : BelongsTo → Client

---

## Migrations de base de données

### Ordre chronologique des migrations

1. **`create_clients_table`** (2026_01_08_092903)
   - Crée la table `clients` avec `nom_entreprise`

2. **`create_content_ideas_table`** (2026_01_08_092910)
   - Crée la table `content_ideas` avec `titre` et `type`
   - Initialement liée à `client_id` (supprimé plus tard)

3. **`create_publication_rules_table`** (2026_01_08_092928)
   - Crée la table `publication_rules` avec `client_id` et `day_of_week`

4. **`create_shootings_table`** (2026_01_08_092935)
   - Crée la table `shootings` avec `client_id` et `date`

5. **`create_publications_table`** (2026_01_08_092942)
   - Crée la table `publications` avec `client_id`, `date`, `content_idea_id`, `shooting_id` (nullable)

6. **`create_content_idea_shooting_table`** (2026_01_08_092948)
   - Crée la table pivot `content_idea_shooting` pour la relation many-to-many

7. **`add_status_to_shootings_table`** (2026_01_08_101417)
   - Ajoute le champ `status` (enum: 'pending', 'completed', 'cancelled') aux tournages

8. **`add_status_to_publications_table`** (2026_01_08_101432)
   - Ajoute le champ `status` (enum: 'pending', 'completed', 'cancelled') aux publications

9. **`remove_client_id_from_content_ideas_table`** (2026_01_08_105205)
   - Supprime la colonne `client_id` de `content_ideas` pour rendre les idées globales

10. **`add_description_to_shootings_table`** (2026_01_08_140820)
    - Ajoute le champ `description` (text, nullable) aux tournages

11. **`add_description_to_publications_table`** (2026_01_08_140830)
    - Ajoute le champ `description` (text, nullable) aux publications

### Schéma de base de données

```
clients
├── id (PK)
└── nom_entreprise

content_ideas
├── id (PK)
├── titre
└── type

publication_rules
├── id (PK)
├── client_id (FK → clients.id)
└── day_of_week

shootings
├── id (PK)
├── client_id (FK → clients.id)
├── date
├── status
├── description (nullable)
└── timestamps

publications
├── id (PK)
├── client_id (FK → clients.id)
├── date
├── content_idea_id (FK → content_ideas.id)
├── shooting_id (FK → shootings.id, nullable)
├── status
├── description (nullable)
└── timestamps

content_idea_shooting (table pivot)
├── content_idea_id (FK → content_ideas.id)
├── shooting_id (FK → shootings.id)
└── timestamps
```

---

## Contrôleurs (Controllers)

### 1. DashboardController (`app/Http/Controllers/DashboardController.php`)

Gère le tableau de bord principal de l'application.

**Méthodes principales :**

- **`index(Request $request)`**
  - Affiche le calendrier combiné des tournages et publications
  - Calcule les alertes (retards, événements à venir)
  - Paramètres : `month`, `year` (optionnels, par défaut mois/année actuel)

- **`generateReport(Request $request)`**
  - Génère un rapport Word détaillé pour un ou plusieurs clients
  - Format : Document Word avec HTML/CSS intégré
  - Paramètres : `client_ids[]`, `month`, `year`

- **`exportCalendar(Request $request)`**
  - Exporte le calendrier combiné en CSV (Excel)
  - Format : Tableau avec jours de la semaine en colonnes
  - Paramètres : `month`, `year`

**Vue associée :** `resources/views/dashboard.blade.php`

### 2. ClientController (`app/Http/Controllers/ClientController.php`)

Gère le CRUD des clients.

**Méthodes :**
- `index()` : Liste tous les clients
- `create()` : Formulaire de création
- `store(Request $request)` : Enregistre un nouveau client
- `show(Client $client)` : Affiche les détails d'un client
- `edit(Client $client)` : Formulaire d'édition
- `update(Request $request, Client $client)` : Met à jour un client
- `destroy(Client $client)` : Supprime un client

**Fonctionnalités spéciales :**
- Redirection intelligente avec paramètre `return_to`
- Validation en français

### 3. ContentIdeaController (`app/Http/Controllers/ContentIdeaController.php`)

Gère le CRUD des idées de contenu (globales).

**Méthodes :**
- `index()` : Liste toutes les idées de contenu
- `create()` : Formulaire de création
- `store(Request $request)` : Enregistre une nouvelle idée
- `show(ContentIdea $contentIdea)` : Affiche les détails
- `edit(ContentIdea $contentIdea)` : Formulaire d'édition
- `update(Request $request, ContentIdea $contentIdea)` : Met à jour
- `destroy(ContentIdea $contentIdea)` : Supprime

**Fonctionnalités spéciales :**
- Redirection intelligente avec paramètre `return_to`
- Validation : `type` doit être 'vidéo', 'image' ou 'texte'

### 4. ShootingController (`app/Http/Controllers/ShootingController.php`)

Gère les tournages avec calendrier.

**Méthodes principales :**

- **`index(Request $request)`**
  - Affiche le calendrier mensuel des tournages
  - Paramètres : `month`, `year`

- **`create(Request $request)`**
  - Formulaire de création avec sélection de client et date
  - Paramètres : `client_id`, `date` (optionnels)

- **`store(Request $request)`**
  - Crée un nouveau tournage
  - Validation : `client_id`, `date`, `content_idea_ids[]` (min 1), `description` (optionnel)
  - Actions possibles :
    - `action=create` : Crée et redirige vers la page du tournage
    - `action=create_and_publish` : Crée et redirige vers le formulaire de publication

- **`show(Shooting $shooting)`**
  - Affiche les détails d'un tournage avec alertes

- **`edit(Shooting $shooting)`**
  - Formulaire d'édition

- **`update(Request $request, Shooting $shooting)`**
  - Met à jour un tournage

- **`destroy(Request $request, Shooting $shooting)`**
  - Supprime un tournage
  - Redirection intelligente avec `return_to_client`

- **`toggleStatus(Request $request, Shooting $shooting)`**
  - Change le statut (pending ↔ completed, ou cancelled)

- **`reschedule(Request $request, Shooting $shooting)`**
  - Reprogramme un tournage annulé avec une nouvelle date

- **`exportCalendar(Request $request)`**
  - Exporte le calendrier des tournages en CSV

**Vues associées :**
- `resources/views/shootings/index.blade.php`
- `resources/views/shootings/create.blade.php`
- `resources/views/shootings/edit.blade.php`
- `resources/views/shootings/show.blade.php`

### 5. PublicationController (`app/Http/Controllers/PublicationController.php`)

Gère les publications avec calendrier.

**Méthodes principales :**

- **`index(Request $request)`**
  - Affiche le calendrier mensuel des publications
  - Paramètres : `month`, `year`

- **`create(Request $request)`**
  - Formulaire de création
  - Filtre les tournages disponibles (non liés à une publication)
  - Paramètres : `client_id`, `date`, `shooting_id` (optionnels)

- **`store(Request $request)`**
  - Crée une nouvelle publication
  - Validation : `client_id`, `date`, `content_idea_id`, `shooting_id` (optionnel), `description` (optionnel)
  - Vérifie les avertissements (jour non recommandé, conflits)

- **`show(Publication $publication)`**
  - Affiche les détails avec alertes

- **`edit(Publication $publication)`**
  - Formulaire d'édition
  - Filtre les tournages disponibles (inclut le tournage actuellement lié)

- **`update(Request $request, Publication $publication)`**
  - Met à jour une publication

- **`destroy(Request $request, Publication $publication)`**
  - Supprime une publication
  - Redirection intelligente avec `return_to_client`

- **`toggleStatus(Request $request, Publication $publication)`**
  - Change le statut (pending ↔ completed, ou cancelled)

- **`reschedule(Request $request, Publication $publication)`**
  - Reprogramme une publication annulée

- **`exportCalendar(Request $request)`**
  - Exporte le calendrier des publications en CSV

**Vues associées :**
- `resources/views/publications/index.blade.php`
- `resources/views/publications/create.blade.php`
- `resources/views/publications/edit.blade.php`
- `resources/views/publications/show.blade.php`

### 6. PublicationRuleController (`app/Http/Controllers/PublicationRuleController.php`)

Gère les règles de publication par client.

**Méthodes :**
- `index(Client $client)` : Liste les règles d'un client
- `create(Client $client)` : Formulaire de création
- `store(Request $request, Client $client)` : Crée une règle
- `destroy(PublicationRule $publicationRule)` : Supprime une règle

**Vues associées :**
- `resources/views/publication-rules/index.blade.php`
- `resources/views/publication-rules/create.blade.php`

### 7. PlanningComparisonController (`app/Http/Controllers/PlanningComparisonController.php`)

Gère la comparaison de plannings entre plusieurs clients.

**Méthodes :**
- **`index(Request $request)`**
  - Affiche le formulaire de sélection de clients et mois
  - Paramètres : `client_ids[]`, `month`, `year`
  - Construit un calendrier comparatif avec tous les événements des clients sélectionnés

**Vue associée :** `resources/views/planning-comparison/index.blade.php`

---

## Vues (Views)

### Layout principal

**`resources/views/layouts/app.blade.php`**

Layout principal de l'application avec :
- Header avec logo (`public/logo.png`)
- Navigation principale
- Container d'alertes fixe en haut de page
- Styles CSS personnalisés (couleurs : orange `#FF6A3A`, gris foncé `#303030`)
- Intégration GSAP pour animations
- Script UX (`public/js/gplanning-ux.js`)

### Vues par module

#### Clients (`resources/views/clients/`)
- **`index.blade.php`** : Liste des clients avec statistiques
- **`create.blade.php`** : Formulaire de création
- **`edit.blade.php`** : Formulaire d'édition
- **`show.blade.php`** : Détails du client avec tournages et publications récents, possibilité de suppression

#### Idées de contenu (`resources/views/content-ideas/`)
- **`index.blade.php`** : Liste des idées de contenu
- **`create.blade.php`** : Formulaire de création
- **`edit.blade.php`** : Formulaire d'édition
- **`show.blade.php`** : Détails d'une idée

#### Tournages (`resources/views/shootings/`)
- **`index.blade.php`** : Calendrier mensuel avec navigation
- **`create.blade.php`** : Formulaire avec vérification en temps réel des conflits
- **`edit.blade.php`** : Formulaire d'édition
- **`show.blade.php`** : Détails avec alertes, actions (compléter, échec, reprogrammer)

#### Publications (`resources/views/publications/`)
- **`index.blade.php`** : Calendrier mensuel avec navigation
- **`create.blade.php`** : Formulaire avec tournages disponibles filtrés
- **`edit.blade.php`** : Formulaire d'édition
- **`show.blade.php`** : Détails avec alertes, actions (compléter, échec, reprogrammer)

#### Dashboard (`resources/views/dashboard.blade.php`)
- Calendrier combiné (tournages + publications)
- Alertes visuelles (retards, événements à venir)
- Boutons d'export (Excel, Word)
- Navigation mensuelle

#### Comparaison de plannings (`resources/views/planning-comparison/index.blade.php`)
- Formulaire de sélection de clients (multi-sélection)
- Calendrier comparatif avec événements de tous les clients sélectionnés

---

## Routes

### Routes principales (`routes/web.php`)

#### Dashboard
```php
GET  /dashboard                    → DashboardController@index
GET  /dashboard/generate-report    → DashboardController@generateReport
GET  /dashboard/export-calendar   → DashboardController@exportCalendar
GET  /                             → redirect('/dashboard')
```

#### Clients (Resource)
```php
GET    /clients                    → ClientController@index
GET    /clients/create             → ClientController@create
POST   /clients                    → ClientController@store
GET    /clients/{client}           → ClientController@show
GET    /clients/{client}/edit      → ClientController@edit
PUT    /clients/{client}           → ClientController@update
DELETE /clients/{client}           → ClientController@destroy
```

#### Idées de contenu (Resource)
```php
GET    /content-ideas              → ContentIdeaController@index
GET    /content-ideas/create       → ContentIdeaController@create
POST   /content-ideas              → ContentIdeaController@store
GET    /content-ideas/{idea}       → ContentIdeaController@show
GET    /content-ideas/{idea}/edit  → ContentIdeaController@edit
PUT    /content-ideas/{idea}       → ContentIdeaController@update
DELETE /content-ideas/{idea}      → ContentIdeaController@destroy
```

#### Tournages (Resource + Actions)
```php
GET    /shootings                  → ShootingController@index
GET    /shootings/create           → ShootingController@create
POST   /shootings                  → ShootingController@store
GET    /shootings/{shooting}       → ShootingController@show
GET    /shootings/{shooting}/edit  → ShootingController@edit
PUT    /shootings/{shooting}       → ShootingController@update
DELETE /shootings/{shooting}       → ShootingController@destroy
POST   /shootings/{shooting}/toggle-status → ShootingController@toggleStatus
POST   /shootings/{shooting}/reschedule    → ShootingController@reschedule
GET    /shootings/export-calendar  → ShootingController@exportCalendar
```

#### Publications (Resource + Actions)
```php
GET    /publications               → PublicationController@index
GET    /publications/create        → PublicationController@create
POST   /publications               → PublicationController@store
GET    /publications/{publication} → PublicationController@show
GET    /publications/{publication}/edit → PublicationController@edit
PUT    /publications/{publication} → PublicationController@update
DELETE /publications/{publication} → PublicationController@destroy
POST   /publications/{publication}/toggle-status → PublicationController@toggleStatus
POST   /publications/{publication}/reschedule    → PublicationController@reschedule
GET    /publications/export-calendar → PublicationController@exportCalendar
```

#### Règles de publication (Nested)
```php
GET    /clients/{client}/publication-rules        → PublicationRuleController@index
GET    /clients/{client}/publication-rules/create → PublicationRuleController@create
POST   /clients/{client}/publication-rules        → PublicationRuleController@store
DELETE /clients/{client}/publication-rules/{rule}  → PublicationRuleController@destroy
```

#### Comparaison de plannings
```php
GET  /planning-comparison          → PlanningComparisonController@index
```

### Routes API (`routes/web.php` - Section API)

#### Autocomplétion
```php
GET  /api/autocomplete/{type}      → Autocomplétion (clients, content-ideas)
```

#### Vérification de date
```php
GET  /api/check-date               → Vérifie les conflits et avertissements pour une date
```

**Paramètres :**
- `date` : Date à vérifier (requis)
- `type` : Type d'événement ('shooting' ou 'publication')
- `client_id` : ID du client (optionnel)

**Réponse JSON :**
```json
{
  "available": true/false,
  "warnings": ["..."],
  "conflicts": [
    {
      "type": "publication|shooting",
      "eventType": "publication|tournage",
      "client": "Nom du client",
      "isSameClient": true/false,
      "message": "...",
      "id": 123,
      "url": "/publications/123"
    }
  ]
}
```

#### Détails d'un tournage
```php
GET  /api/shootings/{shooting}     → Retourne les détails JSON d'un tournage
```

---

## Fonctionnalités principales

### 1. Gestion des clients

- CRUD complet des clients
- Affichage des statistiques (nombre de tournages, publications, règles)
- Liste des derniers tournages et publications
- Suppression des tournages et publications depuis la page client

### 2. Gestion des idées de contenu

- Idées de contenu **globales** (partagées entre tous les clients)
- Types : vidéo, image, texte
- Utilisables dans les tournages (plusieurs idées par tournage) et publications (une idée par publication)

### 3. Gestion des tournages

- Calendrier mensuel avec navigation
- Création avec sélection de client, date, et idées de contenu
- Vérification en temps réel des conflits de dates
- Statuts : pending, completed, cancelled
- Actions : marquer comme complété, échec, reprogrammer
- Description optionnelle
- Export Excel du calendrier
- Possibilité de créer une publication directement après création

### 4. Gestion des publications

- Calendrier mensuel avec navigation
- Création avec sélection de client, date, idée de contenu
- Liaison optionnelle avec un tournage (seulement les tournages disponibles)
- Vérification en temps réel des conflits et avertissements
- Vérification des jours non recommandés (règles de publication)
- Statuts : pending, completed, cancelled
- Actions : marquer comme complétée, échec, reprogrammer
- Description optionnelle
- Export Excel du calendrier

### 5. Règles de publication

- Définition de jours non recommandés par client
- Avertissements automatiques lors de la création de publications
- Gestion depuis la page du client

### 6. Dashboard

- Calendrier combiné (tournages + publications)
- Alertes visuelles :
  - Retards (événements en statut "pending" avec date passée)
  - Événements à venir (dans les 3 prochains jours)
- Export Excel du calendrier
- Génération de rapports Word détaillés par client(s)

### 7. Comparaison de plannings

- Sélection de plusieurs clients
- Calendrier comparatif avec tous les événements
- Navigation mensuelle

### 8. Exports

#### Export Excel (CSV)
- Format tableau avec jours de la semaine en colonnes
- Contenu : date, événements avec statuts, clients, idées de contenu, avertissements
- Disponible pour : dashboard, tournages, publications

#### Export Word (Rapport)
- Document Word avec HTML/CSS intégré
- Détails complets par client : tournages, publications, statistiques
- Sélection d'un ou plusieurs clients

---

## UX et JavaScript

### GSAP (GreenSock Animation Platform)

Intégré via CDN pour les animations fluides :
- Animations d'entrée des cartes (`fadeInUp`)
- Animations des alertes (slideInDown, slideOutRight)
- Transitions fluides

### Script UX personnalisé (`public/js/gplanning-ux.js`)

**Fonctionnalités :**

1. **Vérification en temps réel des dates**
   - Détection automatique des conflits lors de la saisie
   - Affichage visuel des avertissements

2. **Sauvegarde automatique des brouillons**
   - Sauvegarde locale (localStorage) des formulaires
   - Restauration automatique au rechargement
   - Désactivable avec `data-no-draft="true"`

3. **Validation dynamique**
   - Feedback visuel immédiat
   - Messages d'erreur en français

4. **Gestion des alertes**
   - Position fixe en haut de page
   - Auto-masquage après 5 secondes
   - Pause au survol
   - Fermeture manuelle

5. **Autocomplétion**
   - Pour les champs clients et idées de contenu
   - Requêtes AJAX vers `/api/autocomplete/{type}`

6. **Navigation au clavier**
   - Support des raccourcis clavier
   - Navigation dans les formulaires

### Styles CSS personnalisés

- **Couleurs principales :**
  - Orange : `#FF6A3A` (primaire)
  - Gris foncé : `#303030` (secondaire)
  - Dégradés pour les boutons et cartes

- **Weekends grisés :**
  - Samedi et dimanche avec fond gris clair (`#e9e9e9` avec opacité 0.7)
  - Indication visuelle des jours non travaillés

- **Responsive design :**
  - Media queries pour mobile et tablette
  - Navigation adaptative

---

## Configuration

### Fichier `.env`

Configuration de la base de données :
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gplanning
DB_USERNAME=root
DB_PASSWORD=
```

### Dépendances principales (`composer.json`)

- `laravel/framework: ^10.0`
- `phpoffice/phpword: ^1.1` (pour les exports Word)

### Installation

1. Cloner le projet
2. Installer les dépendances : `composer install`
3. Copier `.env.example` vers `.env`
4. Générer la clé : `php artisan key:generate`
5. Configurer la base de données dans `.env`
6. Exécuter les migrations : `php artisan migrate`
7. Démarrer le serveur : `php artisan serve`

### Commandes utiles

```bash
# Migrations
php artisan migrate              # Exécuter les migrations
php artisan migrate:status      # Voir le statut des migrations
php artisan migrate:rollback     # Annuler la dernière migration

# Cache
php artisan cache:clear         # Vider le cache
php artisan config:clear        # Vider la config
php artisan view:clear          # Vider les vues compilées

# Serveur de développement
php artisan serve               # Démarrer sur http://127.0.0.1:8000
```

---

## Notes importantes

### Sécurité

- Validation des données côté serveur
- Protection CSRF sur tous les formulaires
- Utilisation de l'injection de dépendances Laravel
- Échappement automatique dans les vues Blade

### Performance

- Eager loading des relations (avec `with()`)
- Regroupement des requêtes
- Cache des vues compilées

### Internationalisation

- Tous les messages sont en français
- Format de dates : `d/m/Y` (français)
- Validation en français

### Évolutions futures possibles

- Système d'authentification utilisateurs
- Permissions et rôles
- Notifications par email
- API REST complète
- Export PDF
- Intégration calendrier externe (Google Calendar, etc.)

---

## Support et maintenance

Pour toute question ou problème, consulter :
- La documentation Laravel : https://laravel.com/docs
- Les logs de l'application : `storage/logs/laravel.log`

---

**Dernière mise à jour :** Janvier 2026  
**Version :** 1.0  
**Développé pour :** Gda Com
