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
- [Qualité du code](#qualité-du-code)
- [Conventions de travail](#conventions-de-travail)
- [Documentation](#documentation)
- [Équipe](#équipe)

---

## Le besoin

Les familles ont souvent besoin d'un enseignant pour encadrer leurs enfants, mais elles ont du mal à en trouver. Des agences de placement existent pour répondre à ce besoin. H-teach centralise et numérise l'activité d'une agence : les parents publient leurs besoins sous forme d'offres, les enseignants y postulent, et la structure attribue chaque offre au bon enseignant.

## Fonctionnalités (MVP)

**Parent (profil « Client »)**
- Inscription et connexion.
- Création d'une offre en plusieurs étapes : niveau de l'élève et matières, localisation, budget (fourchette), puis récapitulatif.
- Onglet « Mes répétiteurs » pour suivre les enseignants attribués.

**Enseignant (profil « Professeur »)**
- Inscription et connexion.
- Profil : matières et niveaux enseignés.
- Onglet « Offres disponibles » : consulter les offres et y postuler.
- Onglet « Mes étudiants » pour suivre les élèves attribués.

**Structure (profil « Administrateur »)**
- Liste des offres à attribuer, avec le nombre de candidatures.
- Comparaison des candidats d'une offre selon une règle : premier arrivé, premier servi ; expérience ; politique de l'entreprise.
- Attribution de l'offre à un enseignant, puis liste des offres attribuées.

## Stack technique

| Couche | Technologie |
|---|---|
| Front-end | HTML, CSS, JavaScript (sans framework, appels à l'API avec `fetch`) |
| Back-end | PHP 8 (sans framework, API JSON) |
| Base de données | MySQL (accès via PDO, requêtes préparées) |
| Qualité du code | ESLint et Prettier (JavaScript), PHP_CodeSniffer avec la norme PSR-12 (PHP) |

Les choix techniques sont justifiés dans les fiches ADR : [docs/adr](docs/adr/README.md).

## Structure du projet

```
H-teach/
├── frontend/             # Interface utilisateur
│   ├── pages/            # Pages HTML (accueil, inscription, espaces parent / enseignant / admin)
│   ├── css/              # Feuilles de style
│   ├── js/               # Scripts (appels à l'API, interactions)
│   └── assets/           # Images, logo
├── backend/              # API PHP
│   ├── public/           # Point d'entrée unique (index.php)
│   ├── src/
│   │   ├── controllers/  # Logique métier (inscription, offres, candidatures, attributions)
│   │   └── models/       # Accès aux tables de la base
│   └── config/           # Configuration (connexion à la base)
├── database/             # Scripts SQL : création des tables et données de test
├── docs/
│   └── adr/              # Fiches de décision d'architecture
├── .env.example          # Modèle des variables d'environnement
├── .editorconfig         # Réglages communs des éditeurs
├── eslint.config.mjs     # Règles ESLint
├── .prettierrc           # Règles Prettier
├── phpcs.xml             # Règles PHP_CodeSniffer (PSR-12)
├── package.json          # Outils et commandes JavaScript (npm)
└── composer.json         # Outils et commandes PHP (Composer)
```

## Prérequis

| Outil | Version minimale | Rôle |
|---|---|---|
| [Git](https://git-scm.com/) | 2.x | Récupérer le projet |
| [XAMPP](https://www.apachefriends.org/) (ou WAMP) | PHP 8.1, MySQL / MariaDB | Exécuter le PHP et la base de données |
| [Composer](https://getcomposer.org/) | 2.x | Installer les outils PHP |
| [Node.js](https://nodejs.org/) (avec npm) | 18 | Installer les outils JavaScript |

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
2. Ouvrir phpMyAdmin (http://localhost/phpmyadmin).
3. Importer le fichier `database/schema.sql`, qui crée la base `hteach` et ses tables, puis `database/seed.sql` pour les données de test.

> Les scripts SQL sont ajoutés pendant la phase de développement.

## Lancement

Depuis la racine du projet :

```bash
php -S localhost:8000 -t frontend backend/public/index.php
```

Puis ouvrir **http://localhost:8000** dans le navigateur.

Cette commande lance le serveur intégré de PHP : il sert les pages du dossier `frontend/` et transmet les appels `/api/...` au back-end. Le front-end et l'API partagent ainsi la même adresse. Ne pas ouvrir les pages avec l'extension Live Server : les appels à l'API échoueraient.

## Qualité du code

| Commande | Rôle |
|---|---|
| `npm run lint` | Analyse le JavaScript avec ESLint |
| `npm run format` | Met en forme le front-end avec Prettier |
| `npm run format:check` | Vérifie la mise en forme sans rien modifier |
| `composer lint` | Analyse le PHP selon la norme PSR-12 |
| `composer lint:fix` | Corrige automatiquement les erreurs PHP corrigeables |

À lancer avant chaque commit.

**Extensions VS Code recommandées :** EditorConfig for VS Code, ESLint, Prettier - Code formatter.

## Conventions de travail

**Branches**
- `main` : version stable, qui fonctionne toujours.
- `feature/nom-de-la-fonctionnalite` : une branche par fonctionnalité (exemple : `feature/inscription`), fusionnée dans `main` une fois terminée et testée.

**Messages de commit** (convention *Conventional Commits*)

| Préfixe | Usage | Exemple |
|---|---|---|
| `feat:` | Nouvelle fonctionnalité | `feat: création d'une offre par le parent` |
| `fix:` | Correction de bug | `fix: vérification du budget minimum` |
| `docs:` | Documentation | `docs: mise à jour du README` |
| `style:` | Mise en forme, sans changement de logique | `style: indentation du CSS` |
| `chore:` | Configuration, outils | `chore: configuration d'ESLint` |

## Documentation

- **Fiches ADR (choix techniques) :** [docs/adr](docs/adr/README.md)
- **Wireframes et maquettes (Figma) :** [TD Projet Web MIAGE 2 — Wireframes & Maquettes](https://www.figma.com/design/pQJCYlPN5M75bDJOEK8H0P)

## Équipe

| Membre | Rôle |
|---|---|


| Achi Ephraim | Note de cadrage et compte-rendu de kick-off, Wireframes et maquettes UI (Figma), Développement back-end et base de données, Tests et intégration |

| Gadedjisso Mickaël | Parcours d'onboarding ,ADR — 2 fiches de choix techniques ,Développement back-end et base de données, Tests et intégration |

| Souanga David | ADR — 2 fiches de choix techniques ,Note de cadrage et compte-rendu de kick-off, Développement back-end et base de données, Tests et intégration |

| Djabonou yann | Développement front-end, Développement back-end et base de données, Tests et intégration |

| Dan Gnaly | Parcours d'onboarding, Starter kit et dépôt Git (README, linter, .env.example), Développement front-end, Développement back-end et base de données, Tests et intégration|
---

*Statut : Starter Kit en place, développement du MVP terminé.*
