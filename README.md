# H-teach

**H-teach** (Home-teach) est une application web qui met en relation les **parents d'élèves**, les **enseignants à domicile** et la **structure de placement** (l'agence) qui fait le lien entre eux.

> Projet réalisé dans le cadre du TD « Projet Web : de l'idée à l'application fonctionnelle » — Fondamentaux du Web, MIAGE Licence 2.

---

## Sommaire

- [Le besoin](#le-besoin)
- [Fonctionnalités (MVP)](#fonctionnalités-mvp)
- [Stack technique](#stack-technique)
- [Structure du projet](#structure-du-projet)
- [Prérequis](#prérequis)
- [Installation](#installation)
- [Lancement](#lancement)
- [Déploiement sur Vercel](#déploiement-sur-vercel)
- [Comptes de démonstration](#comptes-de-démonstration)
- [API](#api)
- [Sécurité](#sécurité)
- [Qualité du code](#qualité-du-code)
- [Conventions de travail](#conventions-de-travail)
- [Documentation](#documentation)
- [Améliorations et perspectives pour H-teach](#améliorations-et-perspectives-pour-h-teach)
- [Équipe](#équipe)

---

## Le besoin

Les familles ont souvent besoin d'un enseignant pour encadrer leurs enfants, mais elles ont du mal à en trouver. Des agences de placement existent pour répondre à ce besoin. H-teach centralise et numérise l'activité d'une agence : les parents publient leurs besoins sous forme d'offres, les enseignants y postulent, et la structure attribue chaque offre au bon enseignant.

## Fonctionnalités (MVP)

**Parent (profil « Client »)**

- Inscription et connexion.
- Après l'inscription : écran d'accueil en 3 étapes et tutoriel rapide.
- Création d'une offre en 4 étapes : niveau de l'élève (du CP1 à la Terminale, avec la série de A à G pour la Seconde, la Première et la Terminale) et matières, localisation, budget (fourchette), puis récapitulatif.
- Onglet « Mes répétiteurs » : enseignants attribués et offres encore en attente.

**Enseignant (profil « Professeur »)**

- Inscription et connexion (avec années d'expérience et niveau d'études).
- Première connexion : saisie des matières et des niveaux enseignés, modifiable ensuite.
- Onglet « Offres disponibles » : uniquement les offres qui correspondent à son profil, avec le détail de chaque offre et la candidature en un clic.
- Onglet « Mes étudiants » : élèves attribués par la structure.

**Structure (profil « Administrateur »)**

- Liste des offres à attribuer, avec le nombre de candidatures.
- Comparaison des candidats d'une offre selon une règle : premier arrivé, premier servi ; expérience ; politique de l'entreprise.
- Attribution de l'offre à un enseignant, puis liste des offres attribuées.

## Stack technique

| Couche          | Technologie                                                                     |
| --------------- | ------------------------------------------------------------------------------- |
| Front-end       | HTML, CSS, JavaScript (sans framework, modules ES, appels à l'API avec `fetch`) |
| Back-end        | PHP 8 (sans framework, API JSON, sessions)                                      |
| Base de données | MySQL / MariaDB (accès via PDO, requêtes préparées)                             |
| Qualité du code | ESLint et Prettier (JavaScript), PHP_CodeSniffer avec la norme PSR-12 (PHP)     |

Les choix techniques sont justifiés dans les fiches ADR : [docs/adr](docs/adr/README.md).

## Structure du projet

```
H-teach/
├── frontend/                 # Interface utilisateur
│   ├── pages/                # Pages HTML (accueil, inscription, connexion, espaces parent / enseignant / admin)
│   ├── css/style.css         # Feuille de style commune (charte des maquettes Figma)
│   ├── js/                   # Scripts : api.js (appels à l'API), entete.js (en-tête commun),
│   │                         #   format.js (affichage commun), un script par page
│   └── assets/               # Images, logo
├── backend/                  # API PHP
│   ├── public/index.php      # Point d'entrée unique et routeur de l'API
│   ├── config/database.php   # Lecture du .env et connexion à la base (PDO)
│   ├── src/
│   │   ├── http.php          # Outils de réponse et de lecture du JSON
│   │   └── controllers/      # Logique métier par profil : auth, parent, enseignant, admin, référentiels
│   └── scripts/              # Scripts en ligne de commande (création d'un compte administrateur)
├── database/
│   ├── schema.sql            # Création de la base et des 9 tables
│   └── seed.sql              # Données de démonstration
├── docs/
│   └── adr/                  # Fiches de décision d'architecture
├── .env.example              # Modèle des variables d'environnement
├── .editorconfig             # Réglages communs des éditeurs
├── eslint.config.mjs         # Règles ESLint
├── .prettierrc               # Règles Prettier
├── phpcs.xml                 # Règles PHP_CodeSniffer (PSR-12)
├── package.json              # Outils et commandes JavaScript (npm)
└── composer.json             # Outils et commandes PHP (Composer)
```

## Prérequis

| Outil                                             | Version minimale         | Rôle                                  |
| ------------------------------------------------- | ------------------------ | ------------------------------------- |
| [Git](https://git-scm.com/)                       | 2.x                      | Récupérer le projet                   |
| [XAMPP](https://www.apachefriends.org/) (ou WAMP) | PHP 8.1, MySQL / MariaDB | Exécuter le PHP et la base de données |
| [Composer](https://getcomposer.org/)              | 2.x                      | Installer les outils PHP              |
| [Node.js](https://nodejs.org/) (avec npm)         | 18                       | Installer les outils JavaScript       |

Vérifier les installations :

```bash
git --version
php -v
composer -V
node -v
npm -v
```

> Sous Windows avec XAMPP : si `php -v` ne répond pas, ajouter `C:\xampp\php` à la variable d'environnement `PATH`.

## Installation

**1. Cloner le dépôt**

```bash
git clone https://github.com/legendaire95/H-teach.git
cd H-teach
```

**2. Installer les outils de développement**

```bash
npm install
composer install
```

**3. Configurer les variables d'environnement**

```bash
cp .env.example .env
```

Ouvrir ensuite le fichier `.env` et renseigner les informations de connexion à votre base MySQL (avec XAMPP par défaut : utilisateur `root`, mot de passe vide).

> Le fichier `.env` est propre à chaque machine et n'est jamais envoyé sur GitHub.

**4. Créer la base de données**

1. Démarrer **MySQL** depuis le panneau de contrôle XAMPP.
2. Ouvrir phpMyAdmin (http://localhost/phpmyadmin), onglet **Importer**.
3. Importer `database/schema.sql` : il crée la base `hteach`, ses 9 tables et les listes de matières et de niveaux.
4. Importer ensuite `database/seed.sql` pour charger les données de démonstration (comptes, offres, candidatures).

> Attention : réimporter `schema.sql` supprime et recrée toutes les tables.

**5. (Facultatif) Créer un compte administrateur**

L'inscription publique ne permet pas de créer un compte administrateur. Les données de démonstration en contiennent un. Pour en créer un autre :

```bash
php backend/scripts/creer-admin.php "Nom de l'agence" email@exemple.ci motdepasse
```

## Lancement

Depuis la racine du projet, avec MySQL démarré :

```bash
npm start
```

Puis ouvrir **http://localhost:8000** dans le navigateur.

`npm start` lance le serveur intégré de PHP (`php -S localhost:8000 -t frontend backend/public/index.php`) : il sert les pages du dossier `frontend/` et transmet les appels `/api/...` au back-end. Le front-end et l'API partagent ainsi la même adresse, ce qui permet d'utiliser une session sécurisée. Ne pas ouvrir les pages avec l'extension Live Server : les appels à l'API échoueraient.

## Déploiement sur Vercel

L'application complète (pages, API PHP et base de données) est en ligne sur Vercel.

- **Pages** : le dossier `frontend/` est servi tel quel (`outputDirectory` dans `vercel.json`).
- **API PHP** : Vercel n'exécute pas PHP nativement ; on utilise le runtime communautaire [`vercel-php`](https://github.com/vercel-community/php). Toutes les URL `/api/...` sont redirigées vers `api/index.php`, qui charge le routeur habituel `backend/public/index.php`.
- **Base de données** : Vercel n'héberge pas MySQL ; la base est sur **TiDB Cloud Starter** (gratuit, compatible MySQL, connexion chiffrée SSL).
- **Sessions** : sur Vercel, chaque requête peut tomber sur un serveur différent qui ne garde aucun fichier. Les sessions sont donc enregistrées dans la table `sessions` (`backend/src/session.php`).

Variables d'environnement à définir dans Vercel (_Settings → Environment Variables_) :

| Variable      | Valeur                                                      |
| ------------- | ----------------------------------------------------------- |
| `DB_HOST`     | hôte fourni par TiDB Cloud                                  |
| `DB_PORT`     | `4000`                                                      |
| `DB_NAME`     | `hteach`                                                    |
| `DB_USER`     | utilisateur fourni par TiDB Cloud (de la forme `xxxx.root`) |
| `DB_PASSWORD` | mot de passe TiDB Cloud                                     |
| `DB_SSL`      | `true`                                                      |

## Comptes de démonstration

Disponibles après l'import de `database/seed.sql` :

| Profil                     | Email                                    | Mot de passe |
| -------------------------- | ---------------------------------------- | ------------ |
| Structure (administrateur) | admin@demo.ci                            | admin1234    |
| Parent                     | awa@demo.ci, marc@demo.ci, fatou@demo.ci | demo1234     |
| Enseignant                 | yao@demo.ci, aya@demo.ci, koffi@demo.ci  | demo1234     |

Scénario conseillé : se connecter avec `admin@demo.ci`, ouvrir l'offre de Terminale D d'Awa Kone, comparer ses deux candidats selon chaque règle, puis attribuer l'offre. Le répétiteur apparaît alors dans l'espace d'Awa, et l'élève dans celui de l'enseignant retenu.

> Ces comptes servent uniquement à la démonstration.

## API

Toutes les routes renvoient du JSON. Les routes protégées vérifient la session et le rôle de l'utilisateur.

| Méthode et route                              | Rôle           | Description                                  |
| --------------------------------------------- | -------------- | -------------------------------------------- |
| `GET /api/niveaux`                            | Public         | Liste des niveaux (CP1 à Terminale)          |
| `GET /api/matieres`                           | Public         | Liste des matières                           |
| `POST /api/inscription`                       | Public         | Création d'un compte parent ou enseignant    |
| `POST /api/connexion`                         | Public         | Connexion (ouverture de la session)          |
| `GET /api/moi`                                | Connecté       | Utilisateur connecté                         |
| `POST /api/deconnexion`                       | Connecté       | Déconnexion                                  |
| `GET /api/parent/offres`                      | Parent         | Offres du parent, avec l'enseignant attribué |
| `POST /api/offres`                            | Parent         | Création d'une offre                         |
| `GET /api/enseignant/profil`                  | Enseignant     | Matières et niveaux de l'enseignant          |
| `PUT /api/enseignant/profil`                  | Enseignant     | Enregistrement du profil                     |
| `GET /api/enseignant/offres`                  | Enseignant     | Offres ouvertes correspondant au profil      |
| `POST /api/candidatures`                      | Enseignant     | Candidature à une offre                      |
| `GET /api/enseignant/etudiants`               | Enseignant     | Offres attribuées à l'enseignant             |
| `GET /api/admin/offres`                       | Administrateur | Offres à attribuer                           |
| `GET /api/admin/candidats?offre_id=…&regle=…` | Administrateur | Candidats d'une offre, triés selon la règle  |
| `POST /api/admin/attributions`                | Administrateur | Attribution d'une offre                      |
| `GET /api/admin/attributions`                 | Administrateur | Offres attribuées                            |

## Sécurité

- Mots de passe chiffrés avec `password_hash` ; message d'erreur identique pour un email inconnu ou un mot de passe faux.
- Session PHP avec cookie `HttpOnly` et `SameSite=Lax` ; identifiant de session régénéré à la connexion.
- Contrôle du rôle sur chaque route protégée (`exigerConnexion`) ; le rôle administrateur ne peut pas être obtenu par l'inscription.
- Requêtes préparées (PDO) contre les injections SQL ; tri des candidats par liste blanche.
- Échappement des données affichées (`echapperHtml`) contre les failles XSS.
- Règles métier garanties aussi par la base : clés étrangères, contraintes d'unicité (une candidature par enseignant et par offre, une attribution par offre), contrôle du budget, transactions.

## Qualité du code

| Commande               | Rôle                                                 |
| ---------------------- | ---------------------------------------------------- |
| `npm run lint`         | Analyse le JavaScript avec ESLint                    |
| `npm run format`       | Met en forme le front-end avec Prettier              |
| `npm run format:check` | Vérifie la mise en forme sans rien modifier          |
| `composer lint`        | Analyse le PHP selon la norme PSR-12                 |
| `composer lint:fix`    | Corrige automatiquement les erreurs PHP corrigeables |

À lancer avant chaque commit.

**Extensions VS Code recommandées :** EditorConfig for VS Code, ESLint, Prettier - Code formatter.

## Conventions de travail

**Branches**

- `main` : version stable, qui fonctionne toujours.
- `feature/nom-de-la-fonctionnalite` : une branche par fonctionnalité (exemple : `feature/inscription`), fusionnée dans `main` une fois terminée et testée.

**Messages de commit** (convention _Conventional Commits_)

| Préfixe  | Usage                                     | Exemple                                    |
| -------- | ----------------------------------------- | ------------------------------------------ |
| `feat:`  | Nouvelle fonctionnalité                   | `feat: création d'une offre par le parent` |
| `fix:`   | Correction de bug                         | `fix: vérification du budget minimum`      |
| `docs:`  | Documentation                             | `docs: mise à jour du README`              |
| `style:` | Mise en forme, sans changement de logique | `style: indentation du CSS`                |
| `chore:` | Configuration, outils                     | `chore: configuration d'ESLint`            |

## Documentation

- **Fiches ADR (choix techniques) :** [docs/adr](docs/adr/README.md)
- **Wireframes et maquettes (Figma) :** [maquettes Figma de H-teach](https://www.figma.com/design/pQJCYlPN5M75bDJOEK8H0P) (page 1 : wireframes, page 2 : maquettes)

## Améliorations et perspectives pour H-teach

<!-- Section à compléter par l'équipe avant le rendu final. -->

### Améliorations apportées pendant le projet

- …

### Perspectives

- …

## Équipe

| Membre             | Rôle                                                                                                                                                                     |
| ------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| Achi Ephraim       | Note de cadrage et compte-rendu de kick-off, Wireframes et maquettes UI (Figma), Développement back-end et base de données, Tests et intégration                         |
| Gadedjisso Mickaël | Parcours d'onboarding, ADR — 2 fiches de choix techniques, Développement back-end et base de données, Tests et intégration                                               |
| Souanga David      | ADR — 2 fiches de choix techniques, Note de cadrage et compte-rendu de kick-off, Développement back-end et base de données, Tests et intégration                         |
| Djabonou yann      | Développement front-end, Développement back-end et base de données, Tests et intégration                                                                                 |
| Dan Gnaly          | Parcours d'onboarding, Starter kit et dépôt Git (README, linter, .env.example), Développement front-end, Développement back-end et base de données, Tests et intégration |

---

_Statut : MVP terminé : les 19 écrans des maquettes sont fonctionnels._
