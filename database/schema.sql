-- =============================================================
-- H-teach : création de la base de données (MySQL / MariaDB)
-- Import : phpMyAdmin > onglet "Importer" > choisir ce fichier
-- Attention : relancer ce script supprime et recrée les tables.
-- =============================================================

-- Les données sont envoyées en UTF-8 (accents corrects, même depuis la ligne de commande)
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS hteach
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE hteach;

-- Suppression dans l'ordre inverse des dépendances
DROP TABLE IF EXISTS attributions;
DROP TABLE IF EXISTS candidatures;
DROP TABLE IF EXISTS offre_matieres;
DROP TABLE IF EXISTS offres;
DROP TABLE IF EXISTS niveaux_enseignant;
DROP TABLE IF EXISTS matieres_enseignant;
DROP TABLE IF EXISTS niveaux;
DROP TABLE IF EXISTS matieres;
DROP TABLE IF EXISTS utilisateurs;
DROP TABLE IF EXISTS sessions;

-- -------------------------------------------------------------
-- Utilisateurs : parents, enseignants et administrateurs
-- (la structure = rôle "admin", voir ADR 002)
-- -------------------------------------------------------------
CREATE TABLE utilisateurs (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nom            VARCHAR(100) NOT NULL,
  prenom         VARCHAR(100) NULL,
  email          VARCHAR(190) NOT NULL UNIQUE,
  mot_de_passe   VARCHAR(255) NOT NULL,          -- empreinte password_hash(), jamais en clair
  role           ENUM('parent', 'enseignant', 'admin') NOT NULL,
  -- Champs propres aux enseignants (écran 04)
  experience     ENUM('moins_1_an', '1_3_ans', '3_5_ans', 'plus_5_ans') NULL,
  niveau_etudes  ENUM('bac', 'licence', 'master', 'doctorat') NULL,
  cree_le        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- Référentiels : listes de matières et de niveaux
-- -------------------------------------------------------------
CREATE TABLE matieres (
  id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  libelle  VARCHAR(60) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE niveaux (
  id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  libelle  VARCHAR(40) NOT NULL UNIQUE,
  ordre    TINYINT UNSIGNED NOT NULL              -- pour trier du plus petit au plus grand
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- Profil de l'enseignant : matières et niveaux enseignés (écran 14)
-- -------------------------------------------------------------
CREATE TABLE matieres_enseignant (
  enseignant_id  INT UNSIGNED NOT NULL,
  matiere_id     INT UNSIGNED NOT NULL,
  PRIMARY KEY (enseignant_id, matiere_id),
  FOREIGN KEY (enseignant_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
  FOREIGN KEY (matiere_id)    REFERENCES matieres(id)
) ENGINE=InnoDB;

CREATE TABLE niveaux_enseignant (
  enseignant_id  INT UNSIGNED NOT NULL,
  niveau_id      INT UNSIGNED NOT NULL,
  PRIMARY KEY (enseignant_id, niveau_id),
  FOREIGN KEY (enseignant_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
  FOREIGN KEY (niveau_id)     REFERENCES niveaux(id)
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- Offres créées par les parents (écrans 09 à 12)
-- -------------------------------------------------------------
CREATE TABLE offres (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  parent_id   INT UNSIGNED NOT NULL,
  niveau_id   INT UNSIGNED NOT NULL,
  serie       ENUM('A', 'B', 'C', 'D', 'E', 'F', 'G') NULL,  -- uniquement Seconde, Première, Terminale (contrôle en PHP)
  ville       VARCHAR(80)  NOT NULL,
  commune     VARCHAR(120) NOT NULL,
  budget_min  INT UNSIGNED NOT NULL,              -- en FCFA par mois
  budget_max  INT UNSIGNED NOT NULL,
  statut      ENUM('ouverte', 'attribuee', 'annulee') NOT NULL DEFAULT 'ouverte',
  cree_le     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (parent_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
  FOREIGN KEY (niveau_id) REFERENCES niveaux(id),
  CHECK (budget_min <= budget_max)
) ENGINE=InnoDB;

-- Une offre peut porter sur plusieurs matières
CREATE TABLE offre_matieres (
  offre_id    INT UNSIGNED NOT NULL,
  matiere_id  INT UNSIGNED NOT NULL,
  PRIMARY KEY (offre_id, matiere_id),
  FOREIGN KEY (offre_id)   REFERENCES offres(id) ON DELETE CASCADE,
  FOREIGN KEY (matiere_id) REFERENCES matieres(id)
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- Candidatures des enseignants (écran 16)
-- -------------------------------------------------------------
CREATE TABLE candidatures (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  offre_id       INT UNSIGNED NOT NULL,
  enseignant_id  INT UNSIGNED NOT NULL,
  postule_le     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,  -- sert à la règle "premier arrivé, premier servi"
  UNIQUE (offre_id, enseignant_id),                -- un enseignant ne postule qu'une fois par offre
  FOREIGN KEY (offre_id)      REFERENCES offres(id) ON DELETE CASCADE,
  FOREIGN KEY (enseignant_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- Attributions décidées par la structure (écrans 18 et 19)
-- -------------------------------------------------------------
CREATE TABLE attributions (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  offre_id        INT UNSIGNED NOT NULL UNIQUE,    -- une offre n'est attribuée qu'une seule fois
  candidature_id  INT UNSIGNED NOT NULL UNIQUE,
  regle           ENUM('premier_arrive', 'experience', 'politique') NOT NULL,
  attribue_le     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (offre_id)       REFERENCES offres(id) ON DELETE CASCADE,
  FOREIGN KEY (candidature_id) REFERENCES candidatures(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- Sessions de connexion (nécessaire pour l'hébergement sur Vercel)
-- -------------------------------------------------------------
CREATE TABLE sessions (
  id       VARCHAR(128) PRIMARY KEY,
  donnees  TEXT NOT NULL,
  maj_le   INT UNSIGNED NOT NULL                 -- horodatage Unix de la dernière activité
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- Données de référence (nécessaires au fonctionnement)
-- -------------------------------------------------------------
INSERT INTO matieres (libelle) VALUES
  ('Mathématiques'), ('Français'), ('Anglais'), ('Physique-Chimie'),
  ('SVT'), ('Histoire-Géo'), ('Philosophie'), ('Espagnol'), ('Allemand');

-- Niveaux pris en charge par les agences : du CP1 à la Terminale
INSERT INTO niveaux (libelle, ordre) VALUES
  ('CP1', 1), ('CP2', 2), ('CE1', 3), ('CE2', 4), ('CM1', 5), ('CM2', 6),
  ('6e', 7), ('5e', 8), ('4e', 9), ('3e', 10),
  ('Seconde', 11), ('Première', 12), ('Terminale', 13);
