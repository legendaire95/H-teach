<?php

declare(strict_types=1);

/**
 * GET /api/parent/offres : offres du parent connecté, avec l'enseignant attribué s'il y en a un.
 */
function listerOffresParent(): void
{
    $parent = exigerConnexion('parent');

    $requete = connexionBdd()->prepare(
        "SELECT o.id, n.libelle AS niveau, o.serie, o.ville, o.commune,
                o.budget_min, o.budget_max, o.statut, o.cree_le,
                (SELECT GROUP_CONCAT(m.libelle ORDER BY m.libelle SEPARATOR ', ')
                   FROM offre_matieres om
                   JOIN matieres m ON m.id = om.matiere_id
                  WHERE om.offre_id = o.id) AS matieres,
                e.nom AS enseignant_nom, e.prenom AS enseignant_prenom, e.email AS enseignant_email
           FROM offres o
           JOIN niveaux n ON n.id = o.niveau_id
           LEFT JOIN attributions a ON a.offre_id = o.id
           LEFT JOIN candidatures c ON c.id = a.candidature_id
           LEFT JOIN utilisateurs e ON e.id = c.enseignant_id
          WHERE o.parent_id = ? AND o.statut <> 'annulee'
          ORDER BY o.cree_le DESC"
    );
    $requete->execute([$parent['id']]);

    repondreJson($requete->fetchAll());
}

/**
 * POST /api/offres : le parent connecté crée une offre (écrans 09 à 12).
 */
function creerOffre(): void
{
    $parent = exigerConnexion('parent');
    $donnees = lireCorpsJson();

    $niveauId = filter_var($donnees['niveau_id'] ?? null, FILTER_VALIDATE_INT);
    $serie = $donnees['serie'] ?? null;
    $matieres = $donnees['matieres'] ?? [];
    $ville = trim((string) ($donnees['ville'] ?? ''));
    $commune = trim((string) ($donnees['commune'] ?? ''));
    $optionsBudget = ['options' => ['min_range' => 1]];
    $budgetMin = filter_var($donnees['budget_min'] ?? null, FILTER_VALIDATE_INT, $optionsBudget);
    $budgetMax = filter_var($donnees['budget_max'] ?? null, FILTER_VALIDATE_INT, $optionsBudget);

    $pdo = connexionBdd();
    $erreurs = [];

    // Niveau, et série obligatoire seulement pour Seconde, Première et Terminale
    $niveau = false;
    if ($niveauId !== false) {
        $requete = $pdo->prepare('SELECT libelle FROM niveaux WHERE id = ?');
        $requete->execute([$niveauId]);
        $niveau = $requete->fetchColumn();
    }

    if ($niveau === false) {
        $erreurs['niveau_id'] = 'Choisissez un niveau valide.';
    } elseif (in_array($niveau, ['Seconde', 'Première', 'Terminale'], true)) {
        if (!in_array($serie, ['A', 'B', 'C', 'D', 'E', 'F', 'G'], true)) {
            $erreurs['serie'] = 'Choisissez la série.';
        }
    } else {
        $serie = null;
    }

    // Matières : au moins une, et toutes doivent exister
    if (!is_array($matieres) || $matieres === []) {
        $erreurs['matieres'] = 'Choisissez au moins une matière.';
    } else {
        $matieres = array_values(array_unique(array_map('intval', $matieres)));
        $marqueurs = implode(',', array_fill(0, count($matieres), '?'));
        $requete = $pdo->prepare("SELECT COUNT(*) FROM matieres WHERE id IN ($marqueurs)");
        $requete->execute($matieres);

        if ((int) $requete->fetchColumn() !== count($matieres)) {
            $erreurs['matieres'] = 'Une des matières choisies est inconnue.';
        }
    }

    if ($ville === '' || $commune === '') {
        $erreurs['localisation'] = 'Indiquez la ville et la commune.';
    }

    if ($budgetMin === false || $budgetMax === false) {
        $erreurs['budget'] = 'Indiquez un budget minimum et un budget maximum valides.';
    } elseif ($budgetMin > $budgetMax) {
        $erreurs['budget'] = 'Le budget minimum doit être inférieur ou égal au budget maximum.';
    }

    if ($erreurs !== []) {
        repondreJson(['erreurs' => $erreurs], 422);
        return;
    }

    // Enregistrement de l'offre et de ses matières, en une seule transaction
    $pdo->beginTransaction();

    try {
        $requete = $pdo->prepare(
            'INSERT INTO offres (parent_id, niveau_id, serie, ville, commune, budget_min, budget_max)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $requete->execute([$parent['id'], $niveauId, $serie, $ville, $commune, $budgetMin, $budgetMax]);
        $offreId = (int) $pdo->lastInsertId();

        $requete = $pdo->prepare('INSERT INTO offre_matieres (offre_id, matiere_id) VALUES (?, ?)');
        foreach ($matieres as $matiereId) {
            $requete->execute([$offreId, $matiereId]);
        }

        $pdo->commit();
    } catch (Throwable $erreur) {
        $pdo->rollBack();
        throw $erreur;
    }

    repondreJson(['id' => $offreId], 201);
}
