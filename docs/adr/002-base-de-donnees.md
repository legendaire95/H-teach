# ADR 002 — Base de données

- **Statut :** Accepté
- **Date :** 13/11/2026
- **Auteur :** GADEDJISSO Mickaël & SOUANGA David
- **Projet :** H-teach — TD Projet Web, MIAGE 2e année

---

## 1. Contexte

H-teach repose sur des données **fortement liées entre elles**. Le cycle de vie d'une offre le montre :

1. un **parent** crée une offre (niveau de l'élève, matières, localisation, budget) et la met sur le marché ;
2. des **enseignants** postulent à cette offre ;
3. l'**administrateur** de la structure compare les candidatures selon une règle (premier arrivé, premier servi ; expérience ; politique de l'entreprise) et en retient une ;
4. l'offre est **attribuée** : l'enseignant apparaît dans « Mes répétiteurs » du parent, et l'élève dans « Mes étudiants » de l'enseignant.

Les données doivent donc respecter des règles d'intégrité :

- une candidature doit toujours pointer vers une offre et un enseignant **qui existent** ;
- une offre ne peut être attribuée **qu'une seule fois** ;
- un enseignant ne peut pas postuler **deux fois** à la même offre ;
- la **date de candidature** doit être enregistrée, car la règle « premier arrivé, premier servi » en dépend.

Contraintes : une équipe qui connaît le langage SQL (cours de MIAGE), un délai court, des outils gratuits, et un back-end en PHP (voir [ADR 001](001-stack-front-end-et-back-end.md)).

Une seule agence utilise la plateforme.

## 2. Décision

1. Utiliser une base de données **relationnelle MySQL**, accessible depuis PHP par **PDO** avec des requêtes préparées.
2. Représenter la structure par un **rôle « admin »** dans la table des utilisateurs, et **non par une table « Structure »** séparée. Une seule agence utilise la plateforme : une telle table ne contiendrait qu'une seule ligne.

### Modèle de données prévu (MVP)

| Table | Contenu principal | Liens (clés étrangères) |
|---|---|---|
| `utilisateurs` | nom, prénom, email (unique), mot de passe chiffré, rôle (`parent`, `enseignant`, `admin`) ; pour les enseignants : années d'expérience, niveau d'études | — |
| `matieres` / `niveaux` | référentiels : listes des matières et des niveaux (du CP1 à la Terminale, colonne `ordre` pour le tri scolaire) | — |
| `matieres_enseignant` / `niveaux_enseignant` | matières et niveaux renseignés par l'enseignant (écran 14) | → `utilisateurs`, → `matieres` / `niveaux` |
| `offres` | niveau de l'élève, série (A à G, uniquement pour Seconde, Première et Terminale), ville, commune, budget minimum et maximum, statut (`ouverte`, `attribuee`, `annulee`), date de création | → `utilisateurs` (le parent), → `niveaux` |
| `offre_matieres` | matières demandées dans l'offre | → `offres`, → `matieres` |
| `candidatures` | date de candidature | → `offres`, → `utilisateurs` (l'enseignant) ; couple (offre, enseignant) **unique** |
| `attributions` | règle appliquée, date d'attribution | → `offres` (**unique** : une seule attribution par offre), → `candidatures` |

Les niveaux vont du CP1 à la Terminale, car les agences ne placent pas d’enseignants à l’université, et le primaire distingue CP1 et CP2. La série est stockée dans l’offre, car elle décrit le besoin de l’élève.

Les règles d'intégrité du contexte sont garanties **par la base elle-même**, grâce aux clés étrangères et aux contraintes d'unicité, et pas seulement par le code PHP.

## 3. Alternatives envisagées

| Alternative | Raison de l'écarter |
|---|---|
| **MongoDB** (NoSQL, orienté documents) | Les relations entre utilisateurs, offres, candidatures et attributions sont au cœur du métier. Une base orientée documents les gère moins naturellement et ne garantit pas l'intégrité référentielle avec des clés étrangères. |
| **PostgreSQL** (relationnelle) | Techniquement aussi adaptée, mais moins pratiquée par l'équipe en cours et moins répandue chez les hébergeurs PHP bon marché. MySQL est fourni directement avec XAMPP et WAMP. |
| **SQLite** (relationnelle, fichier unique) | Très simple, mais moins adaptée à une base partagée et à un déploiement en ligne avec plusieurs utilisateurs simultanés. |
| **Table « Structure » séparée** | Inutile avec une seule agence : un rôle « admin » suffit. À reconsidérer dans un nouvel ADR si la plateforme accueille plusieurs agences. |

## 4. Conséquences

### Avantages

- **Intégrité des données garantie** par les clés étrangères et les contraintes d'unicité : pas de candidature « orpheline », pas de double attribution.
- **Requêtes SQL déjà maîtrisées** par l'équipe, et jointures naturelles pour afficher, par exemple, les candidats d'une offre avec leur expérience (écran 18).
- **Gratuit et facile à installer** : MySQL est inclus dans XAMPP et WAMP, et l'outil phpMyAdmin permet de consulter les données.
- **Bonne intégration avec PHP** via PDO.

### Inconvénients et risques

- **Schéma rigide** : ajouter un champ plus tard demande de modifier la table et de mettre à jour le script SQL. *Mesure : le schéma est versionné dans `database/schema.sql`.*
- **Installation locale** : chaque membre doit installer un serveur MySQL et créer la base. *Mesure : un script de création et un jeu de données de test sont fournis dans `database/`, et la procédure figure dans le README.*
- **Requêtes écrites à la main** : sans ORM, l'équipe écrit ses requêtes SQL et doit les protéger contre les injections. *Mesure : utilisation systématique des requêtes préparées PDO.*
- **Identifiants de connexion sensibles** : hôte, nom de la base, utilisateur et mot de passe sont stockés dans le fichier `.env` (non versionné) ; le fichier `.env.example` indique seulement les variables attendues.
