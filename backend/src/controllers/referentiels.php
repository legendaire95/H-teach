<?php

declare(strict_types=1);

/**
 * GET /api/niveaux : liste des niveaux dans l'ordre scolaire (CP1 à Terminale).
 */
function listerNiveaux(): void
{
    $niveaux = connexionBdd()
        ->query('SELECT id, libelle FROM niveaux ORDER BY ordre')
        ->fetchAll();

    repondreJson($niveaux);
}

/**
 * GET /api/matieres : liste des matières par ordre alphabétique.
 */
function listerMatieres(): void
{
    $matieres = connexionBdd()
        ->query('SELECT id, libelle FROM matieres ORDER BY libelle')
        ->fetchAll();

    repondreJson($matieres);
}
