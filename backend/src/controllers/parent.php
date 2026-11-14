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
