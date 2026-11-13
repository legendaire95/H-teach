# ADR 001 — Stack front-end et back-end

- **Statut :** Accepté
- **Date :** 13/11/2026
- **Auteur :** GADEDJISSO Mickaël & SOUANGA David
- **Projet :** H-teach — TD Projet Web, MIAGE 2e année

---

## 1. Contexte

H-teach est une application web qui met en relation trois acteurs d'une agence de placement d'enseignants à domicile : les **parents d'élèves**, les **enseignants** et la **structure** (l'agence, représentée par un administrateur).

Le besoin, tel que défini dans la note de cadrage et les maquettes (19 écrans), se compose surtout de :

- **formulaires** : inscription double Client / Professeur (écrans 03 et 04), création d'une offre en plusieurs étapes (écrans 09 à 12), profil de l'enseignant (écran 14) ;
- **listes et tableaux** : offres disponibles (écran 16), offres à attribuer et candidats (écrans 17 et 18), offres attribuées (écran 19) ;
- **quelques interactions dynamiques** : onglets, tutoriel « Suivant / Commencer maintenant » (écrans 06 et 07), passage d'une étape à l'autre du formulaire, changement de formulaire selon l'onglet choisi.

Côté serveur, l'application doit :

- gérer des **comptes sécurisés** avec trois rôles (parent, enseignant, administrateur) ;
- appliquer des **règles métier** : seul un parent crée une offre, seul un enseignant postule, seul l'administrateur attribue une offre, et une offre n'est attribuée qu'une seule fois ;
- enregistrer et lire les données dans une base de données (voir [ADR 002](002-base-de-donnees.md)).

Contraintes du projet :

- une **équipe de 3 à 4 étudiants** qui maîtrise HTML, CSS, JavaScript et PHP, vus en cours et pratiqués en TP (application « bibliothèque » avec `fetch` et un serveur PHP) ;
- un **délai court** pour livrer une application complète et fonctionnelle ;
- un **budget nul** : uniquement des outils gratuits.

## 2. Décision

- **Front-end :** HTML, CSS et JavaScript **sans framework** (« vanilla »). Les échanges avec le serveur se font avec l'API `fetch`, au format JSON.
- **Back-end :** **PHP**, sans framework, organisé en API : un point d'entrée unique (`backend/public/index.php`), des contrôleurs (logique métier) et des modèles (accès à la base).

## 3. Justification

### 3.1 HTML et CSS sans framework

Toute application web repose sur HTML (structure) et CSS (mise en forme). La vraie décision porte sur **l'absence de framework CSS ou de composants** :

- les écrans de H-teach sont des formulaires, des listes et des tableaux simples : HTML les décrit directement (`<form>`, `<input>`, `<select>`, `<table>`) ;
- CSS (Flexbox et Grid) suffit pour reproduire la mise en page des maquettes : en-tête avec onglets, cartes, grilles de cases à cocher ;
- les maquettes Figma ont leur propre charte (police Inter, bleu #2557D6, orange #F07B1D) : une feuille de style maison la reproduit plus fidèlement qu'un framework au style imposé ;
- l'équipe maîtrise ces technologies : pas de temps d'apprentissage.

### 3.2 JavaScript

JavaScript est nécessaire parce que **plusieurs écrans demandent de l'interaction** :

- le formulaire d'inscription change selon l'onglet « Client » ou « Professeur » (écrans 03 et 04) ;
- la création d'une offre se fait en 4 étapes avec un récapitulatif (écrans 09 à 12) ;
- le tutoriel rapide avance avec « Suivant » (écrans 06 et 07) ;
- les données sont envoyées au serveur et affichées **sans recharger la page**, grâce à `fetch` ;
- les champs sont vérifiés avant l'envoi : champs obligatoires, budget minimum inférieur au maximum…

C'est aussi un langage que l'équipe vient d'étudier : le projet permet d'approfondir ces acquis (`fetch`, `async/await`, manipulation du DOM).

### 3.3 PHP

Un langage serveur est indispensable pour ce que le navigateur ne doit pas faire seul :

- **sécuriser les comptes** : les mots de passe sont chiffrés avec `password_hash` et vérifiés avec `password_verify` ;
- **contrôler les rôles** : par exemple, refuser qu'un parent appelle l'action « attribuer une offre » ;
- **appliquer les règles métier** côté serveur : une offre attribuée ne peut plus l'être une deuxième fois ;
- **dialoguer avec MySQL** de façon sécurisée, avec des requêtes préparées (PDO).

PHP est choisi parce que l'équipe l'a déjà pratiqué en TP (serveur PHP, routes `/api/...`, réponses JSON), qu'il est fourni gratuitement avec XAMPP ou WAMP, et que l'hébergement PHP est très répandu et peu coûteux.

## 4. Alternatives envisagées

| Alternative | Raison de l'écarter |
|---|---|
| **React ou Vue.js** (framework front-end) | Courbe d'apprentissage et outillage (Node.js, bundler) trop lourds pour le délai. Les composants réutilisables n'apportent pas assez à une application de 19 écrans simples. |
| **Bootstrap** (framework CSS) | Utilisable, mais son style par défaut s'éloigne de la charte des maquettes, qu'il faudrait de toute façon surcharger. |
| **Node.js + Express** (back-end) | On resterait en JavaScript partout, mais l'équipe maîtrise moins JavaScript côté serveur que PHP. |
| **Laravel** (framework PHP) | Il fournirait le routage, la sécurité et l'ORM, mais demande un temps d'apprentissage incompatible avec le délai. |

## 5. Conséquences

### Avantages

- L'équipe est productive tout de suite : aucune nouvelle technologie à apprendre.
- Le projet est léger : il suffit d'un serveur PHP et d'un navigateur, sans étape de compilation.
- Chaque membre comprend et peut expliquer l'ensemble du code.
- Les écrans peuvent reproduire fidèlement les maquettes Figma.

### Inconvénients et risques

- **Code répété d'une page à l'autre** : l'en-tête (avatar, nom, onglets) est présent sur chaque page, faute de composants réutilisables. *Mesure : un script JavaScript commun génère l'en-tête.*
- **Gestion manuelle de l'affichage** : mettre à jour les listes après un `fetch` se fait à la main, ce qui deviendrait lourd si l'application grossissait.
- **Sécurité à écrire soi-même** : sans framework, l'équipe doit penser au contrôle des rôles, aux requêtes préparées contre les injections SQL et à l'échappement des données affichées. *Mesure : centraliser ces contrôles dans le back-end.*
- **Routage à écrire soi-même** côté PHP (correspondance entre une URL et une action).
- Si le projet devait évoluer fortement, une migration vers un framework (React côté front, Laravel côté back) serait à étudier dans un nouvel ADR.
