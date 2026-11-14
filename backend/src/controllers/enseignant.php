<?php

declare(strict_types=1);

/**
 * Vérifie une liste d'identifiants (matières ou niveaux) : non vide, et tous présents dans la table.
 * Renvoie la liste nettoyée, ou null si elle est invalide.
 * $table vient toujours de notre code ('matieres' ou 'niveaux'), jamais de l'utilisateur.
 */
function idsValides(PDO $pdo, string $table, mixed $ids): ?array
{
    if (!is_array($ids) || $ids === []) {
        return null;
    }

    $ids = array_values(array_unique(array_map('intval', $ids)));
    $marqueurs = implode(',', array_fill(0, count($ids), '?'));
    $requete = $pdo->prepare("SELECT COUNT(*) FROM $table WHERE id IN ($marqueurs)");
    $requete->execute($ids);

    return (int) $requete->fetchColumn() === count($ids) ? $ids : null;
}

/**
 * GET /api/enseignant/profil : matières et niveaux de l'enseignant connecté.
 */
function lireProfilEnseignant(): void
{
    $enseignant = exigerConnexion('enseignant');
    $pdo = connexionBdd();

    $requete = $pdo->prepare('SELECT matiere_id FROM matieres_enseignant WHERE enseignant_id = ?');
    $requete->execute([$enseignant['id']]);
    $matieres = array_map('intval', $requete->fetchAll(PDO::FETCH_COLUMN));

    $requete = $pdo->prepare('SELECT niveau_id FROM niveaux_enseignant WHERE enseignant_id = ?');
    $requete->execute([$enseignant['id']]);
    $niveaux = array_map('intval', $requete->fetchAll(PDO::FETCH_COLUMN));

    repondreJson(['matieres' => $matieres, 'niveaux' => $niveaux]);
}

/**
 * PUT /api/enseignant/profil : remplace les matières et niveaux de l'enseignant (écran 14).
 */
function enregistrerProfilEnseignant(): void
{
    $enseignant = exigerConnexion('enseignant');
    $donnees = lireCorpsJson();
    $pdo = connexionBdd();

    $matieres = idsValides($pdo, 'matieres', $donnees['matieres'] ?? null);
    $niveaux = idsValides($pdo, 'niveaux', $donnees['niveaux'] ?? null);

    $erreurs = [];
    if ($matieres === null) {
        $erreurs['matieres'] = 'Choisissez au moins une matière.';
    }
    if ($niveaux === null) {
        $erreurs['niveaux'] = 'Choisissez au moins un niveau.';
    }
    if ($erreurs !== []) {
        repondreJson(['erreurs' => $erreurs], 422);
        return;
    }

    $pdo->beginTransaction();

    try {
        $pdo->prepare('DELETE FROM matieres_enseignant WHERE enseignant_id = ?')->execute([$enseignant['id']]);
        $pdo->prepare('DELETE FROM niveaux_enseignant WHERE enseignant_id = ?')->execute([$enseignant['id']]);

        $requete = $pdo->prepare('INSERT INTO matieres_enseignant (enseignant_id, matiere_id) VALUES (?, ?)');
        foreach ($matieres as $matiereId) {
            $requete->execute([$enseignant['id'], $matiereId]);
        }

        $requete = $pdo->prepare('INSERT INTO niveaux_enseignant (enseignant_id, niveau_id) VALUES (?, ?)');
        foreach ($niveaux as $niveauId) {
            $requete->execute([$enseignant['id'], $niveauId]);
        }

        $pdo->commit();
    } catch (Throwable $erreur) {
        $pdo->rollBack();
        throw $erreur;
    }

    repondreJson(['matieres' => $matieres, 'niveaux' => $niveaux]);
}

/**
 * GET /api/enseignant/etudiants : offres attribuées à l'enseignant connecté (écran 15).
 */
function listerEtudiants(): void
{
    $enseignant = exigerConnexion('enseignant');

    $requete = connexionBdd()->prepare(
        "SELECT o.id, n.libelle AS niveau, o.serie, o.ville, o.commune,
                o.budget_min, o.budget_max, a.attribue_le,
                (SELECT GROUP_CONCAT(m.libelle ORDER BY m.libelle SEPARATOR ', ')
                   FROM offre_matieres om
                   JOIN matieres m ON m.id = om.matiere_id
                  WHERE om.offre_id = o.id) AS matieres,
                p.nom AS parent_nom, p.prenom AS parent_prenom, p.email AS parent_email
           FROM attributions a
           JOIN candidatures c ON c.id = a.candidature_id
           JOIN offres o ON o.id = a.offre_id
           JOIN niveaux n ON n.id = o.niveau_id
           JOIN utilisateurs p ON p.id = o.parent_id
          WHERE c.enseignant_id = ?
          ORDER BY a.attribue_le DESC"
    );
    $requete->execute([$enseignant['id']]);

    repondreJson($requete->fetchAll());
}
