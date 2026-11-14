<?php

declare(strict_types=1);

// Règles de sélection de la note de cadrage, et l'ordre de tri SQL correspondant.
// "politique" : pas de classement automatique, l'agence choisit selon sa propre politique.
const REGLES_SELECTION = [
    'premier_arrive' => 'c.postule_le ASC',
    'experience'     => "FIELD(e.experience, 'plus_5_ans', '3_5_ans', '1_3_ans', 'moins_1_an'), c.postule_le ASC",
    'politique'      => 'c.postule_le ASC',
];

/**
 * GET /api/admin/offres : offres ouvertes, avec le nombre de candidatures (écran 17).
 */
function listerOffresAAttribuer(): void
{
    exigerConnexion('admin');

    $offres = connexionBdd()->query(
        "SELECT o.id, n.libelle AS niveau, o.serie, o.ville, o.commune,
                o.budget_min, o.budget_max, o.cree_le,
                (SELECT GROUP_CONCAT(m.libelle ORDER BY m.libelle SEPARATOR ', ')
                   FROM offre_matieres om
                   JOIN matieres m ON m.id = om.matiere_id
                  WHERE om.offre_id = o.id) AS matieres,
                p.nom AS parent_nom, p.prenom AS parent_prenom,
                (SELECT COUNT(*) FROM candidatures c WHERE c.offre_id = o.id) AS nb_candidatures
           FROM offres o
           JOIN niveaux n ON n.id = o.niveau_id
           JOIN utilisateurs p ON p.id = o.parent_id
          WHERE o.statut = 'ouverte'
          ORDER BY o.cree_le ASC"
    )->fetchAll();

    repondreJson($offres);
}

/**
 * GET /api/admin/candidats?offre_id=..&regle=.. : l'offre et ses candidats, triés selon la règle (écran 18).
 */
function listerCandidats(): void
{
    exigerConnexion('admin');

    $offreId = filter_var($_GET['offre_id'] ?? null, FILTER_VALIDATE_INT);
    $regle = $_GET['regle'] ?? 'premier_arrive';
    if (!array_key_exists($regle, REGLES_SELECTION)) {
        $regle = 'premier_arrive';
    }

    $pdo = connexionBdd();

    $requete = $pdo->prepare(
        "SELECT o.id, n.libelle AS niveau, o.serie, o.ville, o.commune,
                o.budget_min, o.budget_max, o.statut,
                (SELECT GROUP_CONCAT(m.libelle ORDER BY m.libelle SEPARATOR ', ')
                   FROM offre_matieres om
                   JOIN matieres m ON m.id = om.matiere_id
                  WHERE om.offre_id = o.id) AS matieres,
                p.nom AS parent_nom, p.prenom AS parent_prenom
           FROM offres o
           JOIN niveaux n ON n.id = o.niveau_id
           JOIN utilisateurs p ON p.id = o.parent_id
          WHERE o.id = ?"
    );
    $requete->execute([$offreId === false ? 0 : $offreId]);
    $offre = $requete->fetch();

    if (!$offre) {
        repondreJson(['erreur' => 'Cette offre est introuvable.'], 404);
        return;
    }

    $ordre = REGLES_SELECTION[$regle];
    $requete = $pdo->prepare(
        "SELECT c.id AS candidature_id, c.postule_le,
                e.nom, e.prenom, e.email, e.experience, e.niveau_etudes
           FROM candidatures c
           JOIN utilisateurs e ON e.id = c.enseignant_id
          WHERE c.offre_id = ?
          ORDER BY $ordre"
    );
    $requete->execute([$offre['id']]);

    repondreJson(['offre' => $offre, 'regle' => $regle, 'candidats' => $requete->fetchAll()]);
}

/**
 * POST /api/admin/attributions : la structure retient une candidature (écran 18).
 */
function attribuerOffre(): void
{
    exigerConnexion('admin');

    $donnees = lireCorpsJson();
    $candidatureId = filter_var($donnees['candidature_id'] ?? null, FILTER_VALIDATE_INT);
    $regle = $donnees['regle'] ?? '';

    if ($candidatureId === false || !array_key_exists($regle, REGLES_SELECTION)) {
        repondreJson(['erreur' => 'Candidature ou règle de sélection invalide.'], 422);
        return;
    }

    $pdo = connexionBdd();
    $pdo->beginTransaction();

    try {
        // FOR UPDATE verrouille l'offre le temps de la transaction
        $requete = $pdo->prepare(
            'SELECT c.offre_id, o.statut
               FROM candidatures c
               JOIN offres o ON o.id = c.offre_id
              WHERE c.id = ?
                FOR UPDATE'
        );
        $requete->execute([$candidatureId]);
        $ligne = $requete->fetch();

        if (!$ligne) {
            $pdo->rollBack();
            repondreJson(['erreur' => 'Cette candidature est introuvable.'], 404);
            return;
        }
        if ($ligne['statut'] !== 'ouverte') {
            $pdo->rollBack();
            repondreJson(['erreur' => 'Cette offre a déjà été attribuée.'], 409);
            return;
        }

        $pdo->prepare('INSERT INTO attributions (offre_id, candidature_id, regle) VALUES (?, ?, ?)')
            ->execute([$ligne['offre_id'], $candidatureId, $regle]);
        $pdo->prepare("UPDATE offres SET statut = 'attribuee' WHERE id = ?")
            ->execute([$ligne['offre_id']]);

        $pdo->commit();
    } catch (Throwable $erreur) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $erreur;
    }

    repondreJson(['offre_id' => (int) $ligne['offre_id']], 201);
}

/**
 * GET /api/admin/attributions : offres attribuées (écran 19).
 */
function listerAttributions(): void
{
    exigerConnexion('admin');

    $attributions = connexionBdd()->query(
        "SELECT a.id, a.regle, a.attribue_le, n.libelle AS niveau, o.serie,
                (SELECT GROUP_CONCAT(m.libelle ORDER BY m.libelle SEPARATOR ', ')
                   FROM offre_matieres om
                   JOIN matieres m ON m.id = om.matiere_id
                  WHERE om.offre_id = o.id) AS matieres,
                p.nom AS parent_nom, p.prenom AS parent_prenom,
                e.nom AS enseignant_nom, e.prenom AS enseignant_prenom
           FROM attributions a
           JOIN offres o ON o.id = a.offre_id
           JOIN niveaux n ON n.id = o.niveau_id
           JOIN utilisateurs p ON p.id = o.parent_id
           JOIN candidatures c ON c.id = a.candidature_id
           JOIN utilisateurs e ON e.id = c.enseignant_id
          ORDER BY a.attribue_le DESC"
    )->fetchAll();

    repondreJson($attributions);
}
