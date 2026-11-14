-- =============================================================
-- H-teach : données de démonstration
-- À importer JUSTE APRÈS schema.sql (base vide), via phpMyAdmin > Importer.
-- Les identifiants sont fixés pour que les liens entre tables soient lisibles.
--
-- Comptes de démonstration (à ne jamais utiliser en production) :
--   Structure (admin) : admin@demo.ci  / admin1234
--   Parents           : awa@demo.ci, marc@demo.ci, fatou@demo.ci   / demo1234
--   Enseignants       : yao@demo.ci, aya@demo.ci, koffi@demo.ci    / demo1234
-- =============================================================

-- Les données sont envoyées en UTF-8 (accents corrects, même depuis la ligne de commande)
SET NAMES utf8mb4;

USE hteach;

-- -------------------------------------------------------------
-- Utilisateurs (mots de passe chiffrés avec password_hash)
-- -------------------------------------------------------------
INSERT INTO utilisateurs (id, nom, prenom, email, mot_de_passe, role, experience, niveau_etudes) VALUES
  (1, 'Agence de placement', NULL, 'admin@demo.ci',
   '$2y$10$HfNHp.Jbq4VQaZ/8GOY/weizG5kEb/qhBMn/ysaK.0fSdV62elb.y', 'admin', NULL, NULL),
  (2, 'Kone', 'Awa', 'awa@demo.ci',
   '$2y$10$0XfjpQ6Ix.P8V4gJ/XGGJecxVAun4AZkqUCmMz0PjrTuv/LhBf2pS', 'parent', NULL, NULL),
  (3, 'Yapi', 'Marc', 'marc@demo.ci',
   '$2y$10$0XfjpQ6Ix.P8V4gJ/XGGJecxVAun4AZkqUCmMz0PjrTuv/LhBf2pS', 'parent', NULL, NULL),
  (4, 'Diallo', 'Fatou', 'fatou@demo.ci',
   '$2y$10$0XfjpQ6Ix.P8V4gJ/XGGJecxVAun4AZkqUCmMz0PjrTuv/LhBf2pS', 'parent', NULL, NULL),
  (5, 'Kouassi Yao', NULL, 'yao@demo.ci',
   '$2y$10$0XfjpQ6Ix.P8V4gJ/XGGJecxVAun4AZkqUCmMz0PjrTuv/LhBf2pS', 'enseignant', '3_5_ans', 'master'),
  (6, 'Traoré Aya', NULL, 'aya@demo.ci',
   '$2y$10$0XfjpQ6Ix.P8V4gJ/XGGJecxVAun4AZkqUCmMz0PjrTuv/LhBf2pS', 'enseignant', 'plus_5_ans', 'licence'),
  (7, 'N''Guessan Koffi', NULL, 'koffi@demo.ci',
   '$2y$10$0XfjpQ6Ix.P8V4gJ/XGGJecxVAun4AZkqUCmMz0PjrTuv/LhBf2pS', 'enseignant', '1_3_ans', 'master');

-- -------------------------------------------------------------
-- Profils des enseignants (écran 14)
-- Matières : 1 Mathématiques, 2 Français, 4 Physique-Chimie, 5 SVT
-- Niveaux  : 7 6e, 8 5e, 9 4e, 10 3e, 11 Seconde, 12 Première, 13 Terminale
-- -------------------------------------------------------------
INSERT INTO matieres_enseignant (enseignant_id, matiere_id) VALUES
  (5, 1), (5, 4),
  (6, 1), (6, 4), (6, 5),
  (7, 1), (7, 2);

INSERT INTO niveaux_enseignant (enseignant_id, niveau_id) VALUES
  (5, 10), (5, 11), (5, 12), (5, 13),
  (6, 9), (6, 10), (6, 11), (6, 12), (6, 13),
  (7, 7), (7, 8), (7, 9), (7, 10);

-- -------------------------------------------------------------
-- Offres des parents (écrans 09 à 12)
-- -------------------------------------------------------------
INSERT INTO offres (id, parent_id, niveau_id, serie, ville, commune, budget_min, budget_max, statut, cree_le) VALUES
  (1, 2, 13, 'D',  'Abidjan', 'Cocody, Angré', 30000, 50000, 'ouverte',   '2026-11-12 18:20:00'),
  (2, 3, 11, 'C',  'Abidjan', 'Yopougon',      25000, 40000, 'ouverte',   '2026-11-13 08:45:00'),
  (3, 4, 9,  NULL, 'Abidjan', 'Marcory',       20000, 35000, 'attribuee', '2026-11-11 10:00:00');

INSERT INTO offre_matieres (offre_id, matiere_id) VALUES
  (1, 1), (1, 4),
  (2, 4),
  (3, 1);

-- -------------------------------------------------------------
-- Candidatures des enseignants (écran 16)
-- -------------------------------------------------------------
INSERT INTO candidatures (id, offre_id, enseignant_id, postule_le) VALUES
  (1, 1, 5, '2026-11-13 09:12:00'),
  (2, 1, 6, '2026-11-13 10:05:00'),
  (3, 2, 6, '2026-11-13 11:30:00'),
  (4, 3, 7, '2026-11-12 08:30:00'),
  (5, 3, 6, '2026-11-12 14:00:00');

-- -------------------------------------------------------------
-- Attribution décidée par la structure (écrans 18 et 19)
-- L'offre 3 (Fatou Diallo, 4e) est attribuée à Koffi N'Guessan, premier à avoir postulé.
-- -------------------------------------------------------------
INSERT INTO attributions (id, offre_id, candidature_id, regle, attribue_le) VALUES
  (1, 3, 4, 'premier_arrive', '2026-11-13 16:00:00');
